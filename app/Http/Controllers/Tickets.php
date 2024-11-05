<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketType;
use App\Models\TicketModel as Ticket;
use App\Models\TicketStatus;
use App\Models\TicketFollowup;
use App\Models\TicketAssignee;
use App\Models\RelationshipManager;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class Tickets extends Controller
{
    public function index(){
        $ticket_types = TicketType::all();
        $tickets = DB::table('tickets as t')
    ->select(
        't.id as ticket_id',
        't.subject_name',
        't.discription',
        't.created_at',
        't.email_id',
        'ts.ticket_status',
        'ts.ticket_label',
        'tt.ticket_type',
        'u.fullname',
        DB::raw("IF(t.created_by IS NULL, c.fullname, e.username) AS created_user"),
        'tf.added_at as last_followup',
        'tf.user_type as followup_type',
        'fu.fullname as followup_user',
        'fa.username as followup_admin'
    )
    ->leftJoin('ticket_status as ts', 't.ticket_status_id', '=', 'ts.id')
    ->leftJoin('ticket_types as tt', 't.ticket_type_id', '=', 'tt.id')
    ->leftJoin('aspnetusers as u', 't.email_id', '=', 'u.email')
    ->leftJoin('emplist as e', 't.created_by', '=', 'e.client_index')
    ->leftJoin('aspnetusers as c', 't.created_user', '=', 'c.id')
    ->leftJoin(DB::raw('(SELECT tf1.* FROM ticket_followup as tf1 INNER JOIN (SELECT ticket_id, MAX(added_at) as latest_followup FROM ticket_followup GROUP BY ticket_id) as tf2 ON tf1.ticket_id = tf2.ticket_id AND tf1.added_at = tf2.latest_followup) as tf'), 't.id', '=', 'tf.ticket_id')
    ->leftJoin('aspnetusers as fu', 'tf.user_id', '=', 'fu.id')
    ->leftJoin('emplist as fa', 'tf.admin_id', '=', 'fa.client_index')
    ->where('t.email_id', session('clogin'))
    ->orderBy('t.created_at', 'DESC')
    ->get();
        // \DB::enableQueryLog();
        // $tickets = Ticket::with(['status','type','user','creator'])->where('email_id', session('clogin'))->get();
        // dd(\DB::getQueryLog());
        // echo "<pre>";
        // print_r($tickets->toArray());
        // exit();
        return view('tickets',compact('ticket_types','tickets'));
    }
    public function createTicket(Request $request)
    {
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'email' => 'required|email',
            'discription' => 'required|string',
            'ticket_type_id' => 'required|integer',
        ]);
        $created_by=session('user')->toArray();

        //Initially set ticket as open
        $ticket_status = TicketStatus::where('ticket_status', 'open')->first();
        $status = $ticket_status->id;

        // Fetch Relationship Manager details
        $rm_details = RelationshipManager::with('employee')
            ->where('user_id', $created_by['id'])
            ->first();

        $assignee = $rm_details ? $rm_details->employee->client_index : $this->getSuperAdminIndex();

        // Create the ticket
        $ticket = Ticket::create([
            'subject_name' => $request->subject_name,
            'email_id' => $request->email,
            'discription' => $request->discription,
            'ticket_type_id' => $request->ticket_type_id,
            'ticket_status_id' => $status,
            'created_user' => $created_by['id'],
        ]);

        if ($ticket) {
            // Create ticket assignee
            TicketAssignee::create([
                'ticket_id' => $ticket->id,
                'assignee' => $assignee,
                'assigned_user' => $created_by['id'],
            ]);

            // Create ticket follow-up
            TicketFollowup::create([
                'ticket_id' => $ticket->id,
                'remarks' => "<p><b>{$request->subject_name}</b><br>{$request->discription}</b></p>",
                'status' => $status,
                'assignee' => $assignee,
                'user_type' => 'user',
                'user_id' => $created_by['id'],
            ]);
            return redirect()->back()->with('success', 'New Ticket Created');
        }
        return redirect()->back()->with('error', 'Ticket creation failed');
    }
    private function getSuperAdminIndex()
    {
        // Fetch the superadmin index
        return \DB::table('emplist')->where('role_id', 1)->value('client_index');
    }
    public function showDetails(Request $request)
    {
        $id = $request->query('id');
        // Fetch the ticket along with necessary relationships
        $ticket = Ticket::select(
            'tickets.id AS ticket_id',
            'tickets.subject_name',
            'tickets.discription',
            'tickets.created_at',
            'tickets.email_id',
            'ticket_status.ticket_status',
            'ticket_status.ticket_label',
            'ticket_types.ticket_type',
            'users.fullname',
            DB::raw("IF(tickets.created_by IS NULL, created_user.fullname, employee.username) AS created_user"),
            'last_followup.added_at AS last_followup',
            'last_followup.user_type AS followup_type',
            'followup_user.fullname AS followup_user',
            'followup_admin.username AS followup_admin'
        )
        ->leftJoin('ticket_status', 'tickets.ticket_status_id', '=', 'ticket_status.id')
        ->leftJoin('ticket_types', 'tickets.ticket_type_id', '=', 'ticket_types.id')
        ->leftJoin('aspnetusers as users', 'tickets.email_id', '=', 'users.email')
        ->leftJoin('emplist AS employee', 'tickets.created_by', '=', 'employee.client_index')
        ->leftJoin('aspnetusers AS created_user', 'tickets.created_user', '=', 'created_user.id')
        ->leftJoin(DB::raw('(
            SELECT tf1.*
            FROM ticket_followup tf1
            INNER JOIN (
                SELECT ticket_id, MAX(added_at) AS latest_followup
                FROM ticket_followup
                GROUP BY ticket_id
            ) tf2 ON tf1.ticket_id = tf2.ticket_id AND tf1.added_at = tf2.latest_followup
        ) AS last_followup'), 'tickets.id', '=', 'last_followup.ticket_id')
        ->leftJoin('aspnetusers AS followup_user', 'last_followup.user_id', '=', 'followup_user.id')
        ->leftJoin('emplist AS followup_admin', 'last_followup.admin_id', '=', 'followup_admin.client_index')
        ->where(DB::raw('MD5(tickets.id)'), $id)
        ->orderBy('tickets.created_at', 'DESC')
        ->first();

        if (!$ticket) {
            abort(404); // or redirect to a route with an error message
        }
        $followups = TicketFollowup::select('ticket_followup.remarks', 'ticket_followup.attachment', 'ticket_followup.user_type', 'ticket_followup.added_at', 'users.fullname AS client_name', 'employee.username AS admin_name')
            ->leftJoin('aspnetusers AS users', 'ticket_followup.user_id', '=', 'users.id')
            ->leftJoin('emplist AS employee', 'ticket_followup.admin_id', '=', 'employee.client_index')
            ->where('ticket_followup.ticket_id', $ticket->ticket_id)
            ->get();

        $assign_details = TicketAssignee::select('ticket_assignee.assignee', 'employee.username')
            ->leftJoin('emplist AS employee', 'ticket_assignee.assignee', '=', 'employee.client_index')
            ->where('ticket_assignee.ticket_id', $ticket->ticket_id)
            ->first();
        return view('ticket_details', compact('ticket', 'followups', 'assign_details'));
    }
    public function fetchFollowups(Request $request)
    {
        if ($request->has('id') && !empty($request->query('id'))) {
            $ticket_id = $request->query('id');
            $followups = TicketFollowup::select(
                'ticket_followup.remarks',
                'ticket_followup.attachment',
                'ticket_followup.user_type',
                'ticket_followup.added_at',
                'users.fullname AS client_name',
                'employees.username AS admin_name'
            )
            ->leftJoin('aspnetusers AS users', 'ticket_followup.user_id', '=', 'users.id')
            ->leftJoin('emplist AS employees', 'ticket_followup.admin_id', '=', 'employees.client_index')
            ->where(DB::raw('MD5(ticket_followup.ticket_id)'), $ticket_id)
            ->get();
        }
        return view('ticket_followups', compact('followups'));
    }
    public function addRemark(Request $request)
    {
        $user=session('user')->toArray();
        $request->validate([
            'remark' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);
        $ticketId = $request->query('id');
        $fileName='';
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            // $attachmentPath = $file->store('ticket_attachments');
            // $file = $request->file('attachment');
            $folderName = 'public/_ticket_attachments/';
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs($folderName, $fileName);

        }
        $originalTicketId = DB::table('tickets')->where(DB::raw('md5(id)'), $ticketId)->value('id');

        if ($originalTicketId) {
            // add followup remark
            TicketFollowup::create([
                'ticket_id' => $originalTicketId,
                'remarks' => $request->input('remark'),
                'attachment' => $fileName,
                'user_type' => 'user',
                'user_id' => $user['id'],
            ]);
            return redirect()->back();
        }
        return redirect()->back()->with('error', 'Ticket not found');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketFollowup extends Model
{
    use HasFactory;
    protected $table = 'ticket_followup';
    public $timestamps=false;
    protected $fillable = ['ticket_id', 'remarks', 'status', 'assignee','attachment','user_type','user_id'];
    public function ticket()
    {
        return $this->belongsTo(TicketModel::class, 'ticket_id');
    }
}

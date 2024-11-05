<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketModel extends Model
{
    use HasFactory;
    protected $table = 'tickets';

    public $timestamps=false;
    protected $fillable = [
        'subject_name',
        'discription',
        'created_at',
        'email_id',
        'ticket_status_id',
        'ticket_type_id',
        'created_by',
        'created_user'
    ];
    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id');
    }

    public function type()
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email_id', 'email');
    }

    public function followups()
    {
        return $this->hasMany(TicketFollowup::class, 'ticket_id');
    }
    public function creator()
    {
        return $this->belongsTo(EmployeeList::class, 'created_by','client_index');
    }
    public function lastFollowup()
    {
        return $this->hasOne(TicketFollowup::class, 'ticket_id')->latest();
    }
}

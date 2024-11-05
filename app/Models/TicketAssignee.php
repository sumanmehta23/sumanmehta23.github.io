<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAssignee extends Model
{
    use HasFactory;
    protected $table = 'ticket_assignee';
    public $timestamps=false;
    protected $fillable=['ticket_id','assignee','assigned_user'];
}

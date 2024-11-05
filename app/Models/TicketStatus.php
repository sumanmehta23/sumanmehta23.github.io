<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;
    protected $table = 'ticket_status';
    // public function tickets()
    // {
    //     return $this->hasMany(TicketModel::class, 'ticket_status_id');
    // }
}

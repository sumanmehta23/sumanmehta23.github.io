<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientNote extends Model
{
    protected $fillable = [
        'client_id',
        'admin_id',
        'note'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'Id');
    }

    public function admin()
    {
        return $this->belongsTo(EmployeeList::class, 'admin_id', 'id');
    }
}

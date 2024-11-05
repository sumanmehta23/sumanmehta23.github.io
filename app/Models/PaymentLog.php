<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;
    protected $primaryKey = 'payment_id';
    protected $fillable = [
        'payment_amount',
        'payment_type',
        'payment_reference_id',
        'payment_status',
        'initiated_by'
    ];
    public function user(){
        return $this->belongsTo(User::class, 'email_id', 'email');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory,HasUuids;
    protected $primaryKey = 'id';
    protected $fillable = [
        'payment_amount',
        'payment_type',
        'payment_reference_id',
        'payment_status',
        'initiated_by',
        'user_id',
        'payment_res',
        'account_id'
    ];
    protected $guarded = [];
    public function user(){
        return $this->belongsTo(User::class);
    }
}

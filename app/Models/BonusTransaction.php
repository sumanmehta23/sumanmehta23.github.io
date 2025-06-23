<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $email
 * @property string $code
 * @property string $bonus_amount
 * @property string $bonus_currency
 * @property string $bonus_type
 * @property string $bonus_code_id
 * @property string $bonus_code_desc
 * @property string $bonus_date
 * @property integer $status
 * @property string $admin_remark
 * @property string $Js_Admin_Remark_Date
 * @property string $created_by
 */
class BonusTransaction extends Model
{
    /**
     * @var array
     */

     use HasFactory,HasUuids;

    public $timestamps=false;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function liveaccount()
    {
        return $this->belongsTo(Account::class)->where('demo',false);
    }

    public function demoaccount()
    {
        return $this->belongsTo(Account::class)->where('demo',true);
    }

    public function promocode()
    {
        return $this->belongsTo(Promocode::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $email
 * @property string $trade_id
 * @property string $bonus_amount
 * @property string $bonus_currency
 * @property string $bonus_type
 * @property string $bonus_code_id
 * @property string $bonus_code_desc
 * @property string $bonus_date
 * @property integer $status
 * @property string $adminRemark
 * @property string $Js_Admin_Remark_Date
 * @property string $created_by
 */
class BonusTrans extends Model
{
    /**
     * @var array
     */
    public $timestamps=false;
    protected $fillable = ['email', 'trade_id', 'bonus_amount', 'bonus_currency', 'bonus_type', 'bonus_code_id', 'bonus_code_desc', 'bonus_date', 'status', 'adminRemark', 'Js_Admin_Remark_Date', 'created_by'];
}

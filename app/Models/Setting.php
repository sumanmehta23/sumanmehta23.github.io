<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
class Setting extends Model
{
    use HasFactory,HasUuids;
    protected $table = 'settings';

    protected $fillable = ['name', 'value', 'updated_at'];
    public $timestamps = false;
    public static function tableExists()
    {
        return Schema::hasTable('settings');
    }
}

<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;
    protected $table = 'settings';

    protected $fillable = ['key', 'value'];
    public static function tableExists()
    {
        return Schema::hasTable(self::getTable());
    }
}

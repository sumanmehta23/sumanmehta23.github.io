<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmployeeList;

class RelationshipManager extends Model
{
    use HasFactory;
    protected $table = 'relationship_manager';
    public $timestamps=false;
    protected $fillable=['user_id','rm_id'];
    public function employee()
    {
        return $this->hasOne(EmployeeList::class, 'client_index', 'rm_id');
    }
}

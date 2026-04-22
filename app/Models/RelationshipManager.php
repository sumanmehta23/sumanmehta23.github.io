<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\EmployeeList;

class RelationshipManager extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'relationship_manager';
    public $timestamps=false;
    protected $fillable=['user_id','rm_id'];

    // Has many live accounts
    public function liveAccounts()
    {
        return $this->hasMany(Account::class)->where('demo', false);
    }
    /**
     * Get the employee (EmployeeList) for this relationship manager
     */
    public function employee()
    {
        return $this->belongsTo(EmployeeList::class, 'rm_id', 'id');
    }
}

<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * PermissionAudit Model
 *
 * Tracks all permission-related events:
 * - Permission changes (create, update, delete)
 * - Role changes and permission assignments
 * - Authorization failures
 *
 * Used for compliance auditing and investigating suspicious access patterns.
 */
class PermissionAudit extends Model
{

    protected $table = 'permission_audits';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    /**
     * Get the admin who performed the action.
     */
    public function employee()
    {
        return $this->belongsTo(EmployeeList::class, 'employee_id');
    }

    /**
     * Scope: Get recent audits (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    /**
     * Scope: Get authorization failures
     */
    public function scopeAuthorizationFailures($query)
    {
        return $query->where('action', 'authorization_failed');
    }

    /**
     * Scope: Get changes to specific resource
     */
    public function scopeForResource($query, $resource)
    {
        return $query->where('resource', $resource);
    }

    /**
     * Scope: Get changes by specific admin
     */
    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope: Get changes on specific resource instance
     */
    public function scopeForResourceId($query, $resourceId)
    {
        return $query->where('resource_id', $resourceId);
    }
}

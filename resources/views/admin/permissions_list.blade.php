
<form method="POST" action="{{route('admin.update_role_permissions')}}">
    @csrf
    <div>
        <div class="mb-3 d-flex justify-content-between">
            <h2 class="text-secondary">Permissions - {{ $roles->name }}</h2>
            <input type="hidden" name="role_id" value="{{ $roles->id }}">
            <input type="submit" class="btn btn-primary" name="update_permissions" value="Update">
        </div>
        <div class="px-3 row justify-content-between">
            {{-- {{dd($permissionGroups)}} --}}
            @foreach ($permissionGroups as $group)
                <div class="py-2 mt-2 row border-bottom">
                    <div class="d-flex align-items-center">
                        <input
                            data-group="{{ $group->id }}"
                            name="groups[]"
                            class="form-check-input me-2 permission-menu-main"
                            type="checkbox"
                            value="{{ $group->id }}"
                            id="checkbox-lg-group-{{ $group->id }}"
                            {{ $group->permissions->every(function ($permission) use ($rolePermissions) {
                                return $rolePermissions->permissions->contains('id', $permission->id);
                            }) ? 'checked' : '' }}
                        >
                        <h4 class="mb-0 text-secondary"  id="group-title-{{ $group->id }}">
                            {{ $group->name }}
                        </h4>
                    </div>
                </div>
                @if (!empty($group->permissions))
                    <div class="mt-2 mb-4 row">
                        @foreach ($group->permissions as $permission)
                            <div class="col-4">
                                <input
                                    data-group="{{ $permission->permission_group_id }}"
                                    name="permissions[]"
                                    class="form-check-input me-2 permission-menu-sub"
                                    type="checkbox"
                                    value="{{ $permission->id }}"
                                    id="checkbox-lg-{{ $permission->id }}"
                                    onclick="togglePermission({{ $permission->id }})"

                                    @if($rolePermissions->permissions->contains('id', $permission->id)) checked @endif
                                >
                                <span class="permission-description" >
                                    {{ $permission->description }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</form>
<script>
    // Function to toggle a single permission checkbox when its description is clicked
// function toggleCheckbox(permissionId) {
//     const checkbox = document.getElementById('checkbox-lg-' + permissionId);
//     checkbox.checked = !checkbox.checked;
// }

// // Function to toggle all permissions within a group when the group name is clicked
// function toggleGroupPermissions(groupId) {
//     const checkboxes = document.querySelectorAll(`[data-group="${groupId}"]`);
//     const groupCheckbox = document.getElementById('checkbox-lg-group-' + groupId);
// console.log(checkboxes);
//     // If the group checkbox is checked, check all permissions, else uncheck them
//     checkboxes.forEach(checkbox => {
//         checkbox.checked = groupCheckbox.checked;
//     });
// }

// // Optionally, you can initialize the checkboxes' checked state based on the role's permissions
// document.addEventListener('DOMContentLoaded', function () {
//     // Here you could loop through existing rolePermissions (if needed)
//     // and set the checkboxes to "checked" based on your existing logic
// });
</script>

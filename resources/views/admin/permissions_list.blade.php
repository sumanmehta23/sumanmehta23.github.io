<form method="POST" action="{{route('admin.update_role_permissions')}}">
    @csrf
    <div>
        <div class="d-flex justify-content-between mb-3">
            <h2 class="text-secondary">PAGES LIST - {{ $roles->role_name }}</h2>
            <input type="hidden" name="role_id" value="{{ $roles->role_id }}">
            <input type="submit" class="btn btn-primary" name="update_permissions" value="Update">
        </div>
        <div class="row justify-content-between px-3">
            @foreach ($menu as $page)
                <div class="row border-bottom py-2 mt-2">
                    <div class="d-flex align-items-center">
                        <input
                            data-page="{{ $page['page_id'] }}"
                            name="pages[]"
                            class="form-check-input me-2 permission-menu-main"
                            type="checkbox"
                            value="{{ $page['page_id'] }}"
                            id="checkebox-lg-{{ $page['page_id'] }}"
                            {{ in_array($page['page_id'], $rolePermissions) ? 'checked' : '' }}
                        >
                        <h4 class="text-secondary mb-0">{{ $page['page_name'] }}</h4>
                    </div>
                </div>
                @if (!empty($page['submenu']))
                    <div class="row mb-5">
                        @foreach ($page['submenu'] as $subpage)
                            <div class="col-4">
                                <input
                                    data-page="{{ $page['page_id'] }}"
                                    name="pages[]"
                                    class="form-check-input me-2 permission-menu-sub"
                                    type="checkbox"
                                    value="{{ $subpage['page_id'] }}"
                                    id="checkebox-lg-{{ $subpage['page_id'] }}"
                                    {{ in_array($subpage['page_id'], $rolePermissions) ? 'checked' : '' }}
                                >
                                {{ $subpage['page_name'] }}
                            </div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</form>

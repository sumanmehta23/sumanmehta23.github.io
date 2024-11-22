<form method="POST" action="{{route('admin.update_role_permissions')}}">
    @csrf
    <div>
        <div class="mb-3 d-flex justify-content-between">
            <h2 class="text-secondary">PAGES LIST - {{ $roles->name }}</h2>
            <input type="hidden" name="role_id" value="{{ $roles->role_id }}">
            <input type="submit" class="btn btn-primary" name="update_permissions" value="Update">
        </div>
        <div class="px-3 row justify-content-between">
            @foreach ($menu as $page)
                <div class="py-2 mt-2 row border-bottom">
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
                        <h4 class="mb-0 text-secondary">{{ $page['page_name'] }}</h4>
                    </div>
                </div>
                @if (!empty($page['submenu']))
                    <div class="mb-5 row">
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

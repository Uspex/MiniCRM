@foreach($permissionList as $groupName => $permissionGroup)
    <div class="nk-block">
        <div class="nk-block-head">
            <h5 class="title">{{ $groupName }}</h5>
        </div><!-- .nk-block-head -->

        <div class="profile-ud-list">
            <div class="profile-ud-item">
                @foreach($permissionGroup as $permission)
                    <div class="custom-control custom-control-sm custom-checkbox pr-2">
                        <input id="permission_{{ $permission->id }}" class="custom-control-input" type="checkbox" name="role_permission[]" value="{{ $permission->name }}" @if($role->hasPermissionTo($permission->name)) checked @endif>
                        <label for="permission_{{ $permission->id }}" class="custom-control-label">{{ $permission->name }}</label>
                    </div>
                @endforeach
            </div>

        </div><!-- .profile-ud-list -->

    </div>
@endforeach



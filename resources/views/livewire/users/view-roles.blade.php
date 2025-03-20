<div>
    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Role</th>
            <th>Permissions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($roles as $role)
            <tr wire:key="{{ $role->id }}">
                <td>
                    {{ $role->display_name }}
                </td>
                <td class="text-nowrap">
                    <ul>
                        @foreach($role->permissions as $permission)
                            <li>{{ $permission->display_name }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endforeach
    </table>
</div>

<div>
    <table class="table striped bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th style="width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr wire:key="{{ $user->id }}">
                <td>
                    {{ $user->name }}
                </td>
                <td>
                    {{ $user->email }}
                </td>
                <td class="text-nowrap">
                    @can('update', $user)
                        <x-forms.button.edit
                            :href="route('users.manage', $user)" title="Edit User {{ $user->id }}" size="xs"
                        />
                    @endcan
                    @can('delete', $user)
                        <x-forms.button.delete
                            title="Delete User {{ $user->id }}"
                            size="xs"
                            wire:click.prevent="delete('{{ $user->id }}')"
                            wire:confirm="Are you sure you want to delete the user &quot;{{ $user->email }}&quot;?"
                        />
                    @endcan
                </td>
            </tr>
        @endforeach
    </table>
</div>

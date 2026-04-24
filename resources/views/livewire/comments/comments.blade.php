<div data-cds-comments>
    @if($this->comments->isEmpty())
        <p class="text-cds-gray-500 text-sm italic">No comments yet.</p>
    @else
        <div class="space-y-3 mb-4">
            @foreach($this->comments as $comment)
                <flux:card class="rounded-sm p-3" wire:key="{{ $comment->id }}">
                    @if($editingId === $comment->id)
                        <flux:textarea label="Edit comment" wire:model="editBody" rows="3" class="mb-2" />
                        <div class="flex gap-2">
                            <x-forms.button size="xs" wire:click="saveEdit">Save</x-forms.button>
                            <x-forms.button size="xs" class="secondary" wire:click="cancelEdit">Cancel</x-forms.button>
                        </div>
                    @else
                        <p class="mb-1">{!! nl2br($comment->body) !!}</p>
                        <div class="flex justify-between items-center text-xs text-cds-gray-500">
                            <span>{{ $comment->user->name }} &middot; {{ $comment->created_at->diffForHumans() }}</span>
                            <div class="flex gap-1">
                                @can('update', $comment)
                                    <x-forms.button.edit size="xs" class="secondary" title="Edit comment"
                                        wire:click="showEdit({{ $comment->id }})"
                                    />
                                @endcan
                                @can('delete', $comment)
                                    <x-forms.button.delete size="xs" title="Delete comment"
                                        wire:click="deleteComment({{ $comment->id }})"
                                        wire:confirm="Delete this comment?"
                                    />
                                @endcan
                            </div>
                        </div>
                    @endif
                </flux:card>
            @endforeach
        </div>
    @endif

    @can('create', [App\Models\Comment::class, $commentable])
        <div>
            <flux:textarea label="Add a comment" wire:model="newComment" rows="3" class="mb-2" />
            <x-forms.button wire:click="addComment" icon="chat-bubble-oval-left">
                Add Comment
            </x-forms.button>
        </div>
    @endcan
</div>

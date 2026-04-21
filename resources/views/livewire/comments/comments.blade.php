<div>
    @if($this->comments->isEmpty())
        <p class="text-cds-gray-500 text-sm italic">No comments yet.</p>
    @else
        <div class="space-y-3 mb-4">
            @foreach($this->comments as $comment)
                <div wire:key="{{ $comment->id }}" class="border rounded-sm border-cds-gray-200 p-3 text-sm">
                    @if($editingId === $comment->id)
                        <flux:textarea wire:model="editBody" rows="3" class="mb-2" />
                        @error('editBody') <p class="text-red-600 text-xs mb-1">{{ $message }}</p> @enderror
                        <div class="flex gap-2">
                            <x-forms.button size="xs" wire:click="saveEdit">Save</x-forms.button>
                            <x-forms.button size="xs" class="secondary" wire:click="cancelEdit">Cancel</x-forms.button>
                        </div>
                    @else
                        <p class="mb-1">{{ $comment->body }}</p>
                        <div class="flex justify-between items-center text-xs text-cds-gray-500">
                            <span>{{ $comment->user->name }} &middot; {{ $comment->created_at->diffForHumans() }}</span>
                            <div class="flex gap-1">
                                @if(auth()->user() && $comment->isEditableBy(auth()->user()))
                                    <x-forms.button size="xs" class="secondary" wire:click="startEdit({{ $comment->id }})" title="Edit comment">
                                        <flux:icon.pencil-square variant="micro" />
                                    </x-forms.button>
                                @endif
                                @if(auth()->user() && $comment->isDeletableBy(auth()->user()))
                                    <x-forms.button.delete size="xs"
                                        wire:click="deleteComment({{ $comment->id }})"
                                        wire:confirm="Delete this comment?"
                                        title="Delete comment"
                                    />
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @can('create', [App\Models\Comment::class, $commentable])
        <div>
            <flux:textarea
                wire:model="newComment"
                placeholder="Add a comment…"
                rows="3"
                class="mb-2"
            />
            @error('newComment') <p class="text-red-600 text-xs mb-1">{{ $message }}</p> @enderror
            <x-forms.button wire:click="addComment" icon="chat-bubble-oval-left">
                Add Comment
            </x-forms.button>
        </div>
    @endcan
</div>

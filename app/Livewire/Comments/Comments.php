<?php

namespace App\Livewire\Comments;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\Scope;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Comments extends Component
{
    public Issue|Scope $commentable;
    public string $newComment = '';
    public ?int $editingId = null;
    public string $editBody = '';

    #[Computed]
    public function comments(): Collection
    {
        return $this->commentable->comments()->with('user:id,name')->get();
    }

    public function addComment(): void
    {
        $this->authorize('create', [Comment::class, $this->commentable]);
        $this->validate(
            rules: ['newComment' => 'required|string|max:2000'],
            messages: ['newComment.required' => 'A comment is required.'],
        );

        $this->commentable->comments()->create([
            'user_id' => auth()->id(),
            'body' => $this->newComment,
        ]);

        $this->newComment = '';
        unset($this->comments);
        $this->dispatch('comments-updated');
    }

    public function showEdit(Comment $comment): void
    {
        if ($this->comments->doesntContain($comment)) {
            abort(404);
        }
        $this->authorize('update', $comment);

        $this->editingId = $comment->id;
        $this->editBody = $comment->body;
    }

    public function saveEdit(): void
    {
        if ($this->editingId === null) {
            return;
        }

        $comment = $this->commentable->comments()->findOrFail($this->editingId);
        $this->authorize('update', $comment);
        $this->validate(
            rules: ['editBody' => 'required|string|max:2000'],
            messages: ['editBody.required' => 'A comment is required.'],
        );

        $comment->update(['body' => $this->editBody]);

        $this->editingId = null;
        $this->editBody = '';
        unset($this->comments);
        $this->dispatch('comments-updated');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editBody = '';
    }

    public function deleteComment(Comment $comment): void
    {
        if ($this->comments->doesntContain($comment)) {
            abort(404);
        }
        $this->authorize('delete', $comment);

        $comment->delete();
        unset($this->comments);
        $this->dispatch('comments-updated');
    }
}

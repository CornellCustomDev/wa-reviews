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

    public function addComment(): void
    {
        $this->authorize('create', [Comment::class, $this->commentable]);
        $this->validate(['newComment' => 'required|string|max:2000']);

        $this->commentable->comments()->create([
            'user_id' => auth()->id(),
            'body' => $this->newComment,
        ]);

        $this->newComment = '';
        unset($this->comments);
    }

    public function startEdit(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        $this->editingId = $id;
        $this->editBody = $comment->body;
    }

    public function saveEdit(): void
    {
        $comment = Comment::findOrFail($this->editingId);
        $this->authorize('update', $comment);
        $this->validate(['editBody' => 'required|string|max:2000']);

        $comment->update(['body' => $this->editBody]);

        $this->editingId = null;
        $this->editBody = '';
        unset($this->comments);
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editBody = '';
    }

    public function deleteComment(int $id): void
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);

        $comment->delete();
        unset($this->comments);
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->commentable->comments()->with('user:id,name')->get();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.comments.comments');
    }
}

<?php

namespace Workbench\App\Livewire;

use Livewire\Component;
use Workbench\App\Models\Post;

class Posts extends Component
{
    public $editingPost;

    public PostForm $form;

    public function edit($postId)
    {
        $this->editingPost = Post::findOrFail($postId);

        $this->form->fill([
            'title' => $this->editingPost->title,
            'body' => $this->editingPost->body->toTrixHtml(),
        ]);
    }

    public function update()
    {
        $this->editingPost->update($this->form->all());
        $this->editingPost = null;
    }

    public function cancel()
    {
        $this->form->reset();
        $this->editingPost = null;
    }

    public function render()
    {
        return view('livewire.posts');
    }
}

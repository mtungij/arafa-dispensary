<?php

namespace App\Livewire\Pages\Post;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app-sidebar')]
class Create extends Component
{
    public function render(): \Illuminate\View\View
    {
        return view('pages.post.⚡create');
    }
}

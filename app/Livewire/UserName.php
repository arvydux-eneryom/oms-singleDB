<?php

namespace App\Livewire;
use Livewire\Component;

class UserName extends Component
{
    public function render()
    {
        return view('livewire.user-name', [
            'name' => auth()->user()->name,
        ]);
    }
}

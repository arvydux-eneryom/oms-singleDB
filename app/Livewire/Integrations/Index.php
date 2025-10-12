<?php

namespace App\Livewire\Integrations;

use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.integrations.index');
    }
}

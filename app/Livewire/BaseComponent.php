<?php

namespace App\Livewire;

use Livewire\Component;

class BaseComponent extends Component
{
    protected function toast($type, $message)
    {
        $this->js(<<<JS
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: '{$type}',
                    message: '{$message}',
                }
            }))
        JS);
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class ServerClock extends Component
{
    public function render()
    {
        return view('livewire.server-clock', [
            'now' => Carbon::now(),
        ]);
    }
}

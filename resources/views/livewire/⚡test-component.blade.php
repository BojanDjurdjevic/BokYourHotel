<?php

use Livewire\Component;

new class extends Component
{
    public string $name = '';
    public function sayHello()
    {
        $this->name = 'Bojan';
    }
};
?>

<div class="text-white">
    <h2>Hello from Livewire</h2>

    @if ( strlen($name) > 0)
       <p class="text-red-500"> Hello {{ $name }} </p> 
    @endif
    <p class="text-red-500"> {{ $name }} </p> 

    <button class="bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-400 p-2 m-2"
        wire:click="sayHello"
    >Click me</button>
</div>
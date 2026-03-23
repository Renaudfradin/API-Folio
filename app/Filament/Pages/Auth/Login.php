<?php

namespace App\Filament\Pages\Auth;

class Login extends \Filament\Auth\Pages\Login
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'demo@gmail.com',
            'password' => 'b$K!*g+h4:v6cjaO4SPL/',
            'remember' => true,
        ]);
    }
}

<?php

namespace App\Livewire\Auth;

use Auth;
use App\Models\Store;
use Livewire\Component;

class Login extends Component
{
    public $store;
    public $email = '';
    public $password = '';
    public $showPassword = false;

    public function mount()
    {
        $this->store = Store::first();
    }

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:8',
    ];

    protected $messages = [
        'email.required' => 'Email wajib diisi',
        'email.email' => 'Format email tidak valid',
        'password.required' => 'Password wajib diisi',
        'password.min' => 'Password minimal 8 karakter'
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();

            $user = Auth::user();

            // Memeriksa apakah pengguna memiliki role 'admin' atau 'super_admin'
        if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
            return redirect()->intended('/admin');
        }

            return redirect()->intended(route('home'));
        }

        $this->addError('email', 'Email atau password salah');
        $this->password = '';
    }

    
    public function render()
    {
        return view('livewire.auth.login')
             ->layout('components.layouts.app', ['hideBottomNav' => true]);
    }
}

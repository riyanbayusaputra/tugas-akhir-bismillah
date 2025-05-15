<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\Store;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class Register extends Component
{
    public $store;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $showPassword = false;
    public $passwordConfirmationTouched = false;

    public function mount()
    {
        $this->store = Store::first();
    }

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed', // Pastikan password dan konfirmasi harus sama
    ];

    protected $messages = [
        'name.required' => 'Nama wajib diisi',
        'name.min' => 'Nama minimal 3 karakter',
        'email.required' => 'Email wajib diisi',
        'email.email' => 'Format email tidak valid',
        'email.unique' => 'Email sudah terdaftar',
        'password.required' => 'Password wajib diisi',
        'password.min' => 'Password minimal 8 karakter',
        'password.confirmed' => 'Konfirmasi password harus sama dengan password', // Pesan error untuk password_confirmation
    ];

    public function updated($propertyName)
    {
        if ($propertyName === 'password_confirmation') {
            $this->passwordConfirmationTouched = true;
        }

        if ($this->passwordConfirmationTouched &&
            $this->password_confirmation !== '' &&
            $this->password !== $this->password_confirmation) {
                $this->addError('password_confirmation', 'Konfirmasi password harus sama dengan password');
        } else {
            $this->resetErrorBag('password_confirmation');
        }

        $this->validateOnly($propertyName);
    }

    public function register()
    {
        $validateData = $this->validate();

        $user = User::create([
            'name' => $validateData['name'],
            'email' => $validateData['email'],
            'password' => Hash::make($validateData['password']),
         
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('home');
    }

    public function togglePassword()
    {
        $this->showPassword = !$this->showPassword;
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('components.layouts.app', ['hideBottomNav' => true]);
    }
}

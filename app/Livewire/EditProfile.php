<?php

namespace App\Livewire;

use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class EditProfile extends Component
{
    public $name, $email, $password, $password_confirmation;

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
            'password' => 'nullable|min:6|confirmed',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama wajib diisi',
        'name.string' => 'Nama harus berupa teks',
        'name.max' => 'Nama maksimal 255 karakter',
        'email.required' => 'Email wajib diisi',
        'email.email' => 'Format email tidak valid',

        'password.min' => 'Password minimal 6 karakter',
        'password.confirmed' => 'Konfirmasi password harus sama dengan password',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updateProfile()
    {
        $this->validate();

        $user = auth()->user();
        $user->name = $this->name;
        $user->email = $this->email;

        if ($this->password) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        session()->flash('message', 'Profil berhasil diperbarui!');
    }

    public function render()
    {
        return view('livewire.edit-profile');
    }
}

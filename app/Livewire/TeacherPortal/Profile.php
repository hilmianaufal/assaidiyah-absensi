<?php

namespace App\Livewire\TeacherPortal;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $phone;
    public $address;
    public $bio;
    public $photo;

    public $newPassword;
    public $newPasswordConfirmation;

    public function mount()
    {
        $teacher = auth()->user()->teacher;

        $this->name = $teacher->name;
        $this->email = auth()->user()->email;
        $this->phone = $teacher->phone;
        $this->address = $teacher->address;
        $this->bio = $teacher->bio;
    }

    public function save()
    {
        $user = auth()->user();
        $teacher = $user->teacher;

        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        if ($this->photo) {

            $fileName = time() . '.'.$this->photo->extension();

            $this->photo->storeAs(
                'uploads/teachers',
                $fileName,
                'public'
            );

            $teacher->photo = 'storage/uploads/teachers/'.$fileName;
        }

        $teacher->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'bio' => $this->bio,
            'photo' => $teacher->photo,
        ]);

        $user->update([
            'email' => $this->email,
        ]);

        if ($this->newPassword) {

            $this->validate([
                'newPassword' => 'min:6|same:newPasswordConfirmation'
            ]);

            $user->update([
                'password' => Hash::make($this->newPassword),
            ]);
        }

        session()->flash(
            'success',
            'Profil berhasil diperbarui.'
        );
    }

    public function render()
    {
        return view('livewire.teacher-portal.profile')
            ->layout('layouts.app');
    }
}

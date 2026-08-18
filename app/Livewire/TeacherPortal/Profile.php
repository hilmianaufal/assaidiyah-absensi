<?php

namespace App\Livewire\TeacherPortal;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $bio = '';

    public $photo = null;

    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    public function mount(): void
    {
        $user = auth()->user();
        $teacher = $user?->teacher;

        if (! $user || ! $teacher) {
            abort(
                403,
                'Akun ini belum terhubung dengan data guru.'
            );
        }

        $this->name = (string) $teacher->name;
        $this->email = (string) $user->email;
        $this->phone = (string) ($teacher->phone ?? '');
        $this->address = (string) ($teacher->address ?? '');
        $this->bio = (string) ($teacher->bio ?? '');
    }

    public function save(): void
    {
        $user = auth()->user();
        $teacher = $user?->teacher;

        if (! $user || ! $teacher) {
            abort(
                403,
                'Akun ini belum terhubung dengan data guru.'
            );
        }

        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'photo' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'newPassword' => [
                'nullable',
                'string',
                'min:6',
            ],

            'newPasswordConfirmation' => [
                'nullable',
                'same:newPassword',
            ],
        ]);

        $photoPath = $teacher->photo;

        if ($this->photo) {
            $storedPath = $this->photo->store(
                'uploads/teachers',
                'public'
            );

            /*
             * Hapus foto lama hanya jika foto tersebut
             * memang berasal dari storage lokal.
             */
            if (
                $teacher->photo
                && Str::startsWith(
                    $teacher->photo,
                    'storage/'
                )
            ) {
                Storage::disk('public')->delete(
                    Str::after(
                        $teacher->photo,
                        'storage/'
                    )
                );
            }

            $photoPath = 'storage/' . $storedPath;
        }

        $teacher->update([
            'name' => trim($this->name),
            'phone' => trim($this->phone) ?: null,
            'address' => trim($this->address) ?: null,
            'bio' => trim($this->bio) ?: null,
            'photo' => $photoPath,
        ]);

        $userData = [
            'name' => trim($this->name),
            'email' => strtolower(
                trim($this->email)
            ),
        ];

        if ($this->newPassword !== '') {
            $userData['password'] = Hash::make(
                $this->newPassword
            );
        }

        $user->update($userData);

        $this->photo = null;
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';

        session()->flash(
            'success',
            'Profil berhasil diperbarui.'
        );
    }

    public function render()
    {
        return view(
            'livewire.teacher-portal.profile',
            [
                'teacher' => auth()->user()->teacher,
            ]
        )->layout('layouts.app');
    }
}

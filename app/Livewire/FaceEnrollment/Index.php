<?php

namespace App\Livewire\FaceEnrollment;

use App\Models\Teacher;
use Livewire\Component;

class Index extends Component
{
    public string $teacher_id = '';
    public string $descriptor = '';

    public function saveFace(): void
    {
        $this->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'descriptor' => ['required', 'string'],
        ]);

        Teacher::findOrFail($this->teacher_id)->update([
            'face_descriptor' => json_decode($this->descriptor, true),
        ]);

        session()->flash('success', 'Data wajah guru berhasil diregistrasi.');

        $this->teacher_id = '';
        $this->descriptor = '';
    }

    public function render()
    {
        return view('livewire.face-enrollment.index', [
            'teachers' => Teacher::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}
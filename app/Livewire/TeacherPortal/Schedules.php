<?php

namespace App\Livewire\TeacherPortal;

use App\Models\TeachingSchedule;
use Livewire\Component;

class Schedules extends Component
{
    public string $day = '';

    public array $days = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Ahad',
    ];

    public function render()
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            abort(403, 'Akun ini belum terhubung dengan data guru.');
        }

        $schedules = TeachingSchedule::with('subject')
            ->where('teacher_id', $teacher->id)
            ->when($this->day, fn ($query) =>
                $query->where('day', $this->day)
            )
            ->orderBy('start_time')
            ->get();

        return view('livewire.teacher-portal.schedules', [
            'teacher' => $teacher,
            'schedules' => $schedules,
        ])->layout('layouts.app');
    }
}
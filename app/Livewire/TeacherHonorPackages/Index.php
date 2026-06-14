<?php

namespace App\Livewire\TeacherHonorPackages;

use App\Models\Teacher;
use App\Models\TeacherHonorPackage;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $teacherId = '';

    public int $weeklyHours = 0;
    public int $packageRate = 0;
    public int $monthlyHonor = 0;
    public int $deductionPerHour = 0;

    public bool $isActive = true;
    public bool $showModal = false;
    public ?int $editingId = null;

    public function updated($property): void
    {
        if (in_array($property, ['weeklyHours', 'packageRate'])) {
            $this->calculateHonor();
        }
    }

    private function calculateHonor(): void
    {
        $weeklyHours = (int) ($this->weeklyHours ?? 0);
        $packageRate = (int) ($this->packageRate ?? 0);

        $this->monthlyHonor = $weeklyHours * $packageRate;

        $monthlyHours = $weeklyHours * 4;

        $this->deductionPerHour = $monthlyHours > 0
            ? (int) round($this->monthlyHonor / $monthlyHours)
            : 0;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $package = TeacherHonorPackage::findOrFail($id);

        $this->editingId = $package->id;
        $this->teacherId = (string) $package->teacher_id;
        $this->weeklyHours = (int) $package->weekly_hours;
        $this->packageRate = (int) $package->package_rate;
        $this->monthlyHonor = (int) $package->monthly_honor;
        $this->deductionPerHour = (int) $package->deduction_per_hour;
        $this->isActive = (bool) $package->is_active;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->calculateHonor();

        $this->validate([
            'teacherId' => ['required', 'exists:teachers,id'],
            'weeklyHours' => ['required', 'integer', 'min:1'],
            'packageRate' => ['required', 'integer', 'min:0'],
        ]);

        TeacherHonorPackage::updateOrCreate(
            ['id' => $this->editingId],
            [
                'teacher_id' => $this->teacherId,
                'weekly_hours' => $this->weeklyHours,
                'package_rate' => $this->packageRate,
                'monthly_honor' => $this->monthlyHonor,
                'deduction_per_hour' => $this->deductionPerHour,
                'is_active' => $this->isActive,
            ]
        );

        $this->showModal = false;
        $this->resetForm();

        session()->flash('success', 'Paket honor guru berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        TeacherHonorPackage::findOrFail($id)->delete();

        session()->flash('success', 'Paket honor guru berhasil dihapus.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->teacherId = '';
        $this->weeklyHours = 0;
        $this->packageRate = 0;
        $this->monthlyHonor = 0;
        $this->deductionPerHour = 0;
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        $packages = TeacherHonorPackage::with('teacher')
            ->when($this->search, function ($query) {
                $query->whereHas('teacher', function ($teacherQuery) {
                    $teacherQuery->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.teacher-honor-packages.index', [
            'packages' => $packages,
            'teachers' => Teacher::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}

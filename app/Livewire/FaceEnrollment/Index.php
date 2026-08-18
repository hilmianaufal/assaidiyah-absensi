<?php

namespace App\Livewire\FaceEnrollment;

use App\Models\Teacher;
use Livewire\Component;

class Index extends Component
{
    public string $teacher_id = '';

    public function saveFaceDescriptor($teacherId, $descriptor): array
    {
        $teacherId = (int) $teacherId;

        if ($teacherId <= 0) {
            return [
                'status' => 'error',
                'message' => 'Guru belum dipilih.',
            ];
        }

        if (
            ! is_array($descriptor)
            || count($descriptor) !== 128
        ) {
            return [
                'status' => 'error',
                'message' => 'Descriptor wajah tidak valid.',
            ];
        }

        $cleanDescriptor = [];

        foreach ($descriptor as $value) {
            if (! is_numeric($value)) {
                return [
                    'status' => 'error',
                    'message' => 'Data wajah rusak.',
                ];
            }

            $cleanDescriptor[] = (float) $value;
        }

        $teacher = Teacher::query()
            ->where('is_active', true)
            ->find($teacherId);

        if (! $teacher) {
            return [
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan.',
            ];
        }

        /*
         * forceFill supaya penyimpanan descriptor tidak
         * tergantung pada konfigurasi fillable.
         */
        $teacher->forceFill([
            'face_descriptor' => $cleanDescriptor,
        ]);

        $teacher->save();
        $teacher->refresh();

        $savedDescriptor = $teacher->face_descriptor;

        if (
            ! is_array($savedDescriptor)
            || count($savedDescriptor) !== 128
        ) {
            return [
                'status' => 'error',
                'message' => 'Wajah gagal tersimpan ke database.',
            ];
        }

        /*
         * Pertahankan guru yang dipilih setelah render Livewire.
         */
        $this->teacher_id = (string) $teacher->id;

        return [
            'status' => 'success',
            'message' => 'Data wajah berhasil disimpan.',
            'teacher_id' => $teacher->id,
            'name' => $teacher->name,
        ];
    }

    public function render()
    {
        return view(
            'livewire.face-enrollment.index',
            [
                'teachers' => Teacher::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),
            ]
        )->layout('layouts.app');
    }
}

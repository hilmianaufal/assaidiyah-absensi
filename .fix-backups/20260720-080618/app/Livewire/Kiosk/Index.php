<?php

namespace App\Livewire\Kiosk;

use App\Models\Teacher;
use App\Services\TeacherAttendanceService;
use App\Services\WhatsappService;
use Livewire\Component;
use Throwable;

class Index extends Component
{
    public string $mode = 'check_in';

    public function mount(): void
    {
        $requestedMode = (string) request()->query(
            'mode',
            'check_in'
        );

        if (in_array(
            $requestedMode,
            ['check_in', 'check_out'],
            true
        )) {
            $this->mode = $requestedMode;
        }
    }

    public function setMode(string $mode): void
    {
        if (in_array(
            $mode,
            ['check_in', 'check_out'],
            true
        )) {
            $this->mode = $mode;
        }
    }

    public function saveAttendanceByTeacherId(
        $teacherId,
        ?string $photoBase64 = null
    ): array {
        $teacher = Teacher::query()
            ->where('is_active', true)
            ->findOrFail($teacherId);

        $result = app(TeacherAttendanceService::class)
            ->record(
                $teacher,
                $this->mode,
                $photoBase64
            );

        $result['wa_sent'] = false;

        if (
            ($result['status'] ?? null) === 'success'
            && filled($teacher->phone)
        ) {
            try {
                $response = WhatsappService::send(
                    $teacher->phone,
                    $this->buildWhatsappMessage(
                        $teacher,
                        $result
                    )
                );

                $result['wa_sent'] = method_exists(
                    $response,
                    'successful'
                )
                    ? $response->successful()
                    : true;
            } catch (Throwable $exception) {
                report($exception);
                $result['wa_sent'] = false;
            }
        }

        return $result;
    }

    private function buildWhatsappMessage(
        Teacher $teacher,
        array $result
    ): string {
        $type = ($result['type'] ?? '') === 'check_out'
            ? 'Pulang'
            : 'Masuk';

        $status = match (
            $result['check_in_status']
                ?? $result['check_out_status']
                ?? null
        ) {
            'ontime' => 'Tepat Waktu',
            'late' => 'Terlambat',
            'normal' => 'Sesuai Jadwal',
            'early' => 'Di Luar Jadwal',
            default => '-',
        };

        return "Assalamu'alaikum Wr. Wb.\n\n"
            . "Absensi guru berhasil dicatat.\n\n"
            . "Nama: {$teacher->name}\n"
            . "Tanggal: " . now()->format('d-m-Y') . "\n"
            . "Jenis: Absen {$type}\n"
            . "Waktu: " . now()->format('H:i') . "\n"
            . "Status: {$status}\n"
            . "Transport: Rp "
            . number_format(
                (int) ($result['transport'] ?? 0),
                0,
                ',',
                '.'
            )
            . "\n\n"
            . "Sistem Absensi Assaidiyyah";
    }

    public function render()
    {
        return view('livewire.kiosk.index', [
            'teachers' => Teacher::query()
                ->where('is_active', true)
                ->whereNotNull('face_descriptor')
                ->orderBy('name')
                ->get(),
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use Illuminate\Database\Seeder;

class TeachingSchedulePdfSeeder extends Seeder
{
    public function run(): void
    {
        $mts = Institution::firstOrCreate(
            ['code' => 'mts'],
            ['name' => 'MTs Assaidiyyah', 'is_active' => true]
        );

        $ma = Institution::firstOrCreate(
            ['code' => 'ma'],
            ['name' => 'MA Assaidiyyah', 'is_active' => true]
        );

        $teachers = [
            1 => 'H. Umar Hamdan, M.Pd.I',
            2 => 'Hj. Lihayati, M.Pd.I',
            3 => 'A. Faozi, S.Pd.I',
            4 => 'Yahya, S.Pd.I',
            5 => 'Ahmad Rifqi, S.Ag, M.Pd',
            6 => 'Linda Septiyani, S.Pd',
            7 => 'H. Muhammad Faiz, Lc',
            8 => 'Abdul Salim, S.Pd.I',
            9 => 'Mas’ud, S.Pd.I',
            10 => 'Abdur Rozak, S.Pd.I, M.Pd',
            11 => 'Anik Nailah, S.Pd.I',
            12 => 'Puji Ambarwati, S.Pd',
            13 => 'Toto, S.Pd.I',
            14 => 'Ismaul Maula, S.Pd.I',
            15 => 'Rotiah, S.Pd',
            16 => 'Muh Rofik Al Mubarok',
            17 => 'Doddy Yudhistira, S.E',
            18 => 'Moh. Niamillah, S.Pd',
            19 => 'Saeful Amri, S.Pd',
            20 => 'Muh. Usman Ali, S.Pd.I',
            21 => 'Tutut Shigotun, S.Sos',
            22 => 'Nurhikmah, S.Pd',
            23 => 'Mohamad Apandi, S.Pd',
            24 => 'Muh. Nurul Hilmi An., S.Pd',
            25 => 'Siti Konaah, S.Pd',
            26 => 'Mohammad Iqbal, S.Pd',
            27 => 'Siti Sholihah, S.Pd',
            29 => 'Siti Musyarofah, S.Pd',
            30 => 'Asri Putri Pertiwi, S.Pd',
            31 => 'Ali Imam Hasbulloh, S.F.U',
        ];

        $subjects = [
            'A' => 'Al-Qur’an Hadis',
            'B' => 'Aqidah Akhlak',
            'C' => 'Fiqih',
            'D' => 'SKI',
            'E' => 'PKn',
            'F' => 'Bahasa Indonesia',
            'G' => 'Bahasa Arab',
            'H' => 'Bahasa Inggris',
            'I' => 'Matematika',
            'J' => 'IPA',
            'K' => 'IPS',
            'L' => 'Seni Budaya',
            'M' => 'Penjaskes',
            'N' => 'Prakarya',
            'O' => 'Tahfidzul Qur’an',
            'P' => 'Qowaid',
            'Q' => 'TIK',
        ];

        foreach ($teachers as $name) {
            $teacher = Teacher::firstOrCreate(
                ['name' => $name],
                [
                    'hourly_rate' => 25000,
                    'is_active' => true,
                    'is_picket_officer' => false,
                ]
            );

            $teacher->institutions()->syncWithoutDetaching([$mts->id]);
        }

        foreach ($subjects as $name) {
            Subject::firstOrCreate(['name' => $name]);
        }

        $times = [
            1 => ['07:20', '08:00'],
            2 => ['08:00', '08:40'],
            3 => ['08:40', '09:20'],
            4 => ['09:20', '10:00'],
            5 => ['10:25', '11:05'],
            6 => ['11:05', '11:45'],
            7 => ['11:45', '12:25'],
            8 => ['12:25', '13:05'],
        ];

        $classes = [
            'VII A', 'VII B', 'VII C', 'VII D',
            'VIII A', 'VIII B', 'VIII C', 'VIII D',
            'IX A', 'IX B', 'IX C',
        ];

        $mtsRows = [
            [
                'day' => 'Sabtu',
                'hour' => 1,
                'codes' => ['11I', '6H', '24Q', '29J', '15J', '22Q', '26H', '13L', '9N', '23I', '17M'],
            ],
            [
                'day' => 'Sabtu',
                'hour' => 2,
                'codes' => ['11I', '6H', '24Q', '29J', '15J', '22Q', '26H', '13L', '9N', '23I', '17M'],
            ],
            [
                'day' => 'Sabtu',
                'hour' => 3,
                'codes' => ['6H', '11I', '29J', '16O', '23I', '15J', '9N', '10B', '12K', '2O', '13L'],
            ],
            [
                'day' => 'Sabtu',
                'hour' => 4,
                'codes' => ['6H', '11I', '29J', '16O', '23I', '15J', '9N', '10B', '12K', '2O', '13L'],
            ],
            [
                'day' => 'Ahad',
                'hour' => 1,
                'codes' => ['20C', '22N', '19B', '6H', '17M', '8P', '18O', '11F', '23I', '13L', '16P'],
            ],
            [
                'day' => 'Ahad',
                'hour' => 2,
                'codes' => ['20C', '22N', '19B', '6H', '17M', '8P', '18O', '11F', '23I', '13L', '16P'],
            ],
        ];

        foreach ($mtsRows as $row) {
            foreach ($row['codes'] as $index => $code) {
                $this->createScheduleFromCode(
                    institution: $mts,
                    teachers: $teachers,
                    subjects: $subjects,
                    className: $classes[$index],
                    day: $row['day'],
                    time: $times[$row['hour']],
                    code: $code
                );
            }
        }

        $maTeachers = [
            'Drs.H. Maknun',
            'H. Umar Hamdan, M.Pd.I',
            'Hj. Lihayati, M.Pd.I',
            'Muhammad Faiz, Lc',
            'Ahmad Syukri, S.Pd',
            'Ahmad Rifqi, S.Pd, S.Ag',
            'Hilmi An Naufal, S.Pd.I',
            'Toto, S.Pd.I',
            'Endang Sari, S.Pd',
        ];

        foreach ($maTeachers as $name) {
            $teacher = Teacher::firstOrCreate(
                ['name' => $name],
                [
                    'hourly_rate' => 25000,
                    'is_active' => true,
                    'is_picket_officer' => false,
                ]
            );

            $teacher->institutions()->syncWithoutDetaching([$ma->id]);
        }

        $maSubjects = [
            'Akidah Akhlak',
            'Sejarah Indonesia',
            'Jurmiyah',
            'Bahasa Inggris',
            'Sulamunnajat',
            'TIK',
            'Fathul Qorib',
            'Tahfidzul Qur’an',
            'SKI',
            'Matematika',
            'Geografi',
            'Bahasa Arab',
            'Tafsir - Ilmu Tafsir',
            'Ekonomi',
            'Fikih',
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
        ];

        foreach ($maSubjects as $name) {
            Subject::firstOrCreate(['name' => $name]);
        }
    }

    private function createScheduleFromCode(
        Institution $institution,
        array $teachers,
        array $subjects,
        string $className,
        string $day,
        array $time,
        string $code
    ): void {
        preg_match('/^(\d+)([A-Z])$/', $code, $matches);

        if (! $matches) {
            return;
        }

        $teacherCode = (int) $matches[1];
        $subjectCode = $matches[2];

        if (! isset($teachers[$teacherCode], $subjects[$subjectCode])) {
            return;
        }

        $teacher = Teacher::firstOrCreate(
            ['name' => $teachers[$teacherCode]],
            [
                'hourly_rate' => 25000,
                'is_active' => true,
                'is_picket_officer' => false,
            ]
        );

        $subject = Subject::firstOrCreate([
            'name' => $subjects[$subjectCode],
        ]);

        $teacher->institutions()->syncWithoutDetaching([$institution->id]);

        TeachingSchedule::updateOrCreate(
            [
                'institution_id' => $institution->id,
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'class_name' => $className,
                'day' => $day,
                'start_time' => $time[0],
                'end_time' => $time[1],
            ],
            [
                'hours_count' => 1,
            ]
        );
    }
}

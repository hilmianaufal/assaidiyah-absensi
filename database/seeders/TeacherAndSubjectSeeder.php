<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherAndSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $ma = Institution::firstOrCreate(
            ['code' => 'ma'],
            [
                'name' => 'MA Assaidiyyah',
                'is_active' => true,
            ]
        );

        $mts = Institution::firstOrCreate(
            ['code' => 'mts'],
            [
                'name' => 'MTs Assaidiyyah',
                'is_active' => true,
            ]
        );

        $teachers = [
            'Drs.H. Maknun',
            'H. Umar Hamdan, M.Pd.I',
            'Hj. Lihayati, M.Pd.I',
            'Muhammad Faiz, Lc',
            'Ahmad Syukri, S.Pd',
            'Ahmad Rifqi, S.Ag, M.Pd',
            'Abdul Rozak, M.Pd.I',
            'Endang Sari, S.Pd',
            'Hilmi An Naufal, S.Pd.I',
            'Toto, S.Pd.I',
            'Ali Imam Hasbullah, S.F.U',
            'Anik Nailah, S.Pd.I',
            'Nur Hikmah, S.Pd',
            'Muhammad Ikbal, S.Pd',
            'Siti Musyarofah, S.Pd',
            'Asri Putri Pratiwi, S.Pd',
            'Linda Septiyani, S.Pd',
            'Rotiah, S.Pd',
            'Mohamad Apandi, S.Pd',
            'Puji Ambarwati, S.Pd',
        ];

        foreach ($teachers as $name) {

            $teacher = Teacher::firstOrCreate(
                ['name' => $name],
                [
                    'hourly_rate' => 25000,
                    'is_active' => true,
                ]
            );

            $teacher->institutions()->syncWithoutDetaching([
                $ma->id,
                $mts->id,
            ]);
        }

        $subjects = [
            'Akidah Akhlak',
            'Al-Qur\'an Hadis',
            'Bahasa Arab',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'Ekonomi',
            'Fikih',
            'Geografi',
            'Hadis - Ilmu Hadis',
            'Ilmu Kalam',
            'IPA',
            'IPS',
            'Jurmiyah',
            'Matematika',
            'MHQ',
            'Pendidikan Pancasila',
            'Prakarya',
            'Qowaid',
            'Sejarah',
            'Sejarah Indonesia',
            'SKI',
            'Sosiologi',
            'Sulamunnajat',
            'Tahfidzul Qur\'an',
            'Tahsinul Qur\'an',
            'Tafsir - Ilmu Tafsir',
            'TIK',
            'Tufatul Athfal',
            'Seni Budaya',
            'Bulughul Marom',
        ];

        foreach ($subjects as $name) {

            Subject::firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}

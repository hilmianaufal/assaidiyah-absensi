<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaceEnrollmentSaveController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'teacher_id' => [
                'required',
                'integer',
                'exists:teachers,id',
            ],

            'descriptor' => [
                'required',
                'array',
                'size:128',
            ],

            'descriptor.*' => [
                'required',
                'numeric',
            ],
        ]);

        $teacher = Teacher::query()
            ->where('is_active', true)
            ->findOrFail($data['teacher_id']);

        $descriptor = array_map(
            static fn ($value) => (float) $value,
            $data['descriptor']
        );

        $teacher->face_descriptor = $descriptor;
        $teacher->save();
        $teacher->refresh();

        if (
            ! is_array($teacher->face_descriptor)
            || count($teacher->face_descriptor) !== 128
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Descriptor gagal tersimpan ke database.',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Wajah berhasil diregistrasi.',
            'teacher_id' => $teacher->id,
            'teacher_name' => $teacher->name,
            'descriptor_count' => count(
                $teacher->face_descriptor
            ),
        ]);
    }
}

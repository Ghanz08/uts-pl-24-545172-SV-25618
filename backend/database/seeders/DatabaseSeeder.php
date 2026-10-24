<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the demo account. Safe to run on every container start: existing data is left alone.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => env('DEMO_USER_EMAIL', 'demo@ideavault.test')],
            ['name' => 'Ghani', 'password' => env('DEMO_USER_PASSWORD', 'password123')],
        );

        if (! $user->wasRecentlyCreated) {
            return;
        }

        $user->projectIdeas()->createMany([
            [
                'title' => 'KosMate',
                'tagline' => 'Tinder, tapi buat nyari teman sekamar kos',
                'description' => 'Matching calon teman kos berdasarkan jam tidur, budget, dan toleransi berisik.',
                'tech_stack' => ['Laravel', 'Vue', 'FrankenPHP'],
                'difficulty' => 'medium',
                'status' => 'brewing',
                'origin' => 'nongkrong',
                'hype_level' => 4,
                'target_event' => 'Gemastik 2026',
                'is_pinned' => true,
            ],
            [
                'title' => 'Antre.in',
                'tagline' => 'Notifikasi antrean fotokopi kampus biar gak nunggu berdiri',
                'description' => 'Tukang fotokopi input nomor antrean, mahasiswa dapat notifikasi saat gilirannya dekat.',
                'tech_stack' => ['Flutter', 'Firebase'],
                'difficulty' => 'easy',
                'status' => 'spark',
                'origin' => 'kelas',
                'hype_level' => 3,
            ],
            [
                'title' => 'ShowerThoughts AI',
                'tagline' => 'Rekam ide pakai suara pas mandi, otomatis jadi to-do',
                'description' => 'Speaker tahan air + speech-to-text yang mengubah ocehan jadi kartu ide.',
                'tech_stack' => ['Python', 'Whisper', 'Raspberry Pi'],
                'difficulty' => 'hard',
                'status' => 'spark',
                'origin' => 'shower',
                'hype_level' => 5,
            ],
            [
                'title' => 'IdeaVault',
                'tagline' => 'Tempat parkir ide dadakan',
                'description' => 'Project UTS ini sendiri. Sudah shipped!',
                'tech_stack' => ['Laravel', 'Vue', 'FrankenPHP', 'Docker'],
                'difficulty' => 'medium',
                'status' => 'shipped',
                'origin' => 'kelas',
                'hype_level' => 5,
                'target_event' => 'UTS PL',
            ],
        ]);
    }
}

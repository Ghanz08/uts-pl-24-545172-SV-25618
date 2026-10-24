<?php

namespace App\Models;

use Database\Factories\ProjectIdeaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectIdea extends Model
{
    /** @use HasFactory<ProjectIdeaFactory> */
    use HasFactory;

    public const DIFFICULTIES = ['easy', 'medium', 'hard'];

    /** spark → brewing → building → shipped, or shelved when parked. */
    public const STATUSES = ['spark', 'brewing', 'building', 'shipped', 'shelved'];

    /** Where the idea was born. */
    public const ORIGINS = ['shower', 'nongkrong', 'kelas', 'mimpi', 'scrolling'];

    /** Statuses that still count as "open" for Idea Roulette. */
    public const OPEN_STATUSES = ['spark', 'brewing', 'building'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'tagline',
        'description',
        'tech_stack',
        'difficulty',
        'status',
        'origin',
        'hype_level',
        'target_event',
        'deadline',
        'repo_url',
        'is_pinned',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tech_stack' => 'array',
            'hype_level' => 'integer',
            'deadline' => 'date',
            'is_pinned' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'shipped';
    }
}

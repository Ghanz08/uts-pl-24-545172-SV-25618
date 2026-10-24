<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectIdeaRequest;
use App\Http\Requests\UpdateProjectIdeaRequest;
use App\Http\Resources\ProjectIdeaResource;
use App\Models\ProjectIdea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ProjectIdeaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(ProjectIdea::STATUSES)],
            'difficulty' => ['nullable', Rule::in(ProjectIdea::DIFFICULTIES)],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $ideas = $request->user()->projectIdeas()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['difficulty'] ?? null, fn ($q, $difficulty) => $q->where('difficulty', $difficulty))
            ->when($filters['q'] ?? null, function ($q, $term) {
                $q->where(fn ($inner) => $inner
                    ->where('title', 'like', "%{$term}%")
                    ->orWhere('tagline', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%"));
            })
            ->orderByDesc('is_pinned')
            ->latest()
            ->latest('id')
            ->get();

        return ProjectIdeaResource::collection($ideas);
    }

    public function store(StoreProjectIdeaRequest $request): JsonResponse
    {
        $idea = $request->user()->projectIdeas()->create($request->validated());

        return (new ProjectIdeaResource($idea->refresh()))
            ->additional(['message' => 'Ide berhasil disimpan'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id): ProjectIdeaResource
    {
        return new ProjectIdeaResource($this->findOwned($request, $id));
    }

    public function update(UpdateProjectIdeaRequest $request, int $id): ProjectIdeaResource
    {
        $idea = $this->findOwned($request, $id);
        $idea->update($request->validated());

        return (new ProjectIdeaResource($idea))->additional(['message' => 'Ide berhasil diperbarui']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->findOwned($request, $id)->delete();

        return response()->json(['message' => 'Ide berhasil dihapus']);
    }

    /**
     * Idea Roulette: pick one open idea at random, weighted by hype_level.
     */
    public function roulette(Request $request): JsonResponse|ProjectIdeaResource
    {
        $candidates = $request->user()->projectIdeas()
            ->whereIn('status', ProjectIdea::OPEN_STATUSES)
            ->get();

        if ($candidates->isEmpty()) {
            return response()->json(['message' => 'Belum ada ide yang bisa diundi. Tambah ide dulu!'], 404);
        }

        $ticket = random_int(1, $candidates->sum('hype_level'));
        $picked = $candidates->first(function (ProjectIdea $idea) use (&$ticket) {
            $ticket -= $idea->hype_level;

            return $ticket <= 0;
        });

        return (new ProjectIdeaResource($picked))->additional(['message' => 'Roda berputar... ini ide kamu hari ini!']);
    }

    public function stats(Request $request): JsonResponse
    {
        $counts = $request->user()->projectIdeas()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byStatus = collect(ProjectIdea::STATUSES)->mapWithKeys(fn ($s) => [$s => (int) ($counts[$s] ?? 0)]);
        $total = $byStatus->sum();

        return response()->json(['data' => [
            'total' => $total,
            'by_status' => $byStatus,
            'shipping_rate' => $total ? round($byStatus['shipped'] / $total * 100, 1) : 0,
        ]]);
    }

    /**
     * Ideas are private: another user's idea answers 404, as if it does not exist.
     */
    private function findOwned(Request $request, int $id): ProjectIdea
    {
        return $request->user()->projectIdeas()->findOrFail($id);
    }
}

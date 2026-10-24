<?php

namespace App\Http\Requests;

use App\Models\ProjectIdea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectIdeaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'tech_stack' => ['nullable', 'array', 'max:10'],
            'tech_stack.*' => ['string', 'max:30'],
            'difficulty' => ['sometimes', Rule::in(ProjectIdea::DIFFICULTIES)],
            'status' => ['sometimes', Rule::in(ProjectIdea::STATUSES)],
            'origin' => ['sometimes', Rule::in(ProjectIdea::ORIGINS)],
            'hype_level' => ['sometimes', 'integer', 'between:1,5'],
            'target_event' => ['nullable', 'string', 'max:120'],
            'deadline' => ['nullable', 'date'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'is_pinned' => ['sometimes', 'boolean'],
        ];
    }
}

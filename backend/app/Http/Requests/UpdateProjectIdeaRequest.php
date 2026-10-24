<?php

namespace App\Http\Requests;

class UpdateProjectIdeaRequest extends StoreProjectIdeaRequest
{
    /**
     * Same rules as create, but every field is optional so PUT/PATCH can send partial updates.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['title'] = ['sometimes', 'required', 'string', 'max:120'];

        return $rules;
    }
}

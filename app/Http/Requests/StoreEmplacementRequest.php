<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmplacementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:emplacements,name',
            'description' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Le nom de l'emplacement est obligatoire.",
            'name.string' => "Le nom de l'emplacement doit être une chaîne de caractères.",
            'name.max' => "Le nom de l'emplacement ne doit pas dépasser 255 caractères.",
            'name.unique' => "Ce nom d'emplacement existe déjà.",
            'description.required' => 'La description est obligatoire.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne doit pas dépasser 1000 caractères.',
        ];
    }
}


<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFournisseurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fournisseurId = $this->route('fournisseur')?->id;

        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'nom_entreprise' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:20|unique:fournisseurs,telephone,' . $fournisseurId,
            'email' => 'required|email|max:255|unique:fournisseurs,email,' . $fournisseurId,
            'ville' => 'required|string|max:100',
            'pays' => 'required|string|max:100',
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du fournisseur est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'nom_entreprise.required' => "Le nom de l'entreprise est obligatoire.",
            'adresse.required' => "L'adresse est obligatoire.",
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé par un autre fournisseur.',
            'email.required' => "L'adresse e-mail est obligatoire.",
            'email.email' => "L'adresse e-mail n'est pas valide.",
            'email.max' => "L'adresse e-mail ne doit pas dépasser 255 caractères.",
            'email.unique' => 'Cette adresse e-mail est déjà utilisée par un autre fournisseur.',
            'ville.required' => 'La ville est obligatoire.',
            'pays.required' => 'Le pays est obligatoire.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'La photo doit être de type : jpeg, png, jpg, gif.',
            'photo.max' => 'La photo ne doit pas dépasser 50MB.',
        ];
    }
}


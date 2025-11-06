<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Categorie;
use App\Models\Emplacement;
use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    /**
     * Affiche une liste des ressources.
     *
     * @param  \Illuminate\Http\Request  $request La requête HTTP.
     * @return \Illuminate\View\View La vue contenant la liste des articles.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $search = $request->input('search');

        $articles = Article::when($search, fn($query, $term) => $query->searchByText($term))->latest()->get();

        return view('articles.index', compact('articles'));
    }

    /**
     * Affiche le formulaire de création d'une nouvelle ressource.
     *
     * @return \Illuminate\View\View La vue du formulaire de création.
     */
    public function create(): \Illuminate\View\View
    {
        $categories = Categorie::all();
        $fournisseurs = Fournisseur::all();
        $emplacements = Emplacement::all();
        // $users n'est pas nécessaire ici car created_by est géré automatiquement
        return view('articles.create', compact('categories', 'fournisseurs', 'emplacements'));
    }

    /**
     * Enregistre une nouvelle ressource dans la base de données.
     *
     * @param  \App\Http\Requests\StoreArticleRequest  $request La requête de stockage validée.
     * @return \Illuminate\Http\RedirectResponse Une redirection vers la liste des articles avec un message de succès.
     */
    public function store(StoreArticleRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validated();

        // Gérer la valeur booléenne pour 'est_visible'
        $validatedData['est_visible'] = $request->has('est_visible');

        // Générer le slug s'il est vide
        if (empty($validatedData['slug']) && !empty($validatedData['name'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
            // Assurer l'unicité du slug généré (rare, mais possible)
            $originalSlug = $validatedData['slug'];
            $count = 1;
            while (Article::where('slug', $validatedData['slug'])->exists()) {
                $validatedData['slug'] = $originalSlug . '-' . $count++;
            }
        }

        // S'assurer que prix_promotionnel est null si vide au lieu de 0 potentiellement
        if (isset($validatedData['prix_promotionnel']) && $validatedData['prix_promotionnel'] === null) {
            $validatedData['prix_promotionnel'] = null;
        }


        // Ajoute l'ID de l'utilisateur authentifié comme créateur de l'article.
        $validatedData['created_by'] = auth()->id();

        // Gestion de l'upload de l'image principale
        if ($request->hasFile('image_principale')) {
            $imagePath = $request->file('image_principale')->store('articles_images', 'public');
            $validatedData['image_principale'] = $imagePath;
        }

        Article::create($validatedData);

        return redirect()->route('articles.index')->with('success', 'Article créé avec succès.');
    }

    /**
     * Affiche la ressource spécifiée.
     *
     * @param  \App\Models\Article  $article L'instance de l'article à afficher.
     * @return \Illuminate\View\View La vue affichant les détails de l'article.
     */
    public function show(Article $article): \Illuminate\View\View
    {
        return view('articles.show', compact('article'));
    }

    /**
     * Affiche le formulaire de modification de la ressource spécifiée.
     *
     * @param  \App\Models\Article  $article L'instance de l'article à modifier.
     * @return \Illuminate\View\View La vue du formulaire de modification.
     */
    public function edit(Article $article): \Illuminate\View\View
    {
        $categories = Categorie::all();
        $fournisseurs = Fournisseur::all();
        $emplacements = Emplacement::all();
        return view('articles.edit', compact('article', 'categories', 'fournisseurs', 'emplacements'));
    }

    /**
     * Met à jour la ressource spécifiée dans la base de données.
     *
     * @param  \App\Http\Requests\UpdateArticleRequest  $request La requête de mise à jour validée.
     * @param  \App\Models\Article  $article L'instance de l'article à mettre à jour.
     * @return \Illuminate\Http\RedirectResponse Une redirection vers la liste des articles avec un message de succès.
     */
    public function update(UpdateArticleRequest $request, Article $article): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validated();

        // Gérer la valeur booléenne pour 'est_visible'
        $validatedData['est_visible'] = $request->has('est_visible');

        // Générer le slug s'il est vide ou si le nom a changé et le slug n'a pas été explicitement fourni
        if (empty($validatedData['slug']) && !empty($validatedData['name'])) {
            $newSlug = Str::slug($validatedData['name']);
            if ($newSlug !== $article->slug) {
                 $validatedData['slug'] = $newSlug;
                // Assurer l'unicité du slug généré
                $originalSlug = $validatedData['slug'];
                $count = 1;
                // On vérifie l'unicité en excluant l'article actuel
                while (Article::where('slug', $validatedData['slug'])->where('id', '!=', $article->id)->exists()) {
                    $validatedData['slug'] = $originalSlug . '-' . $count++;
                }
            } else {
                // Si le slug est vide dans la requête mais que le nom n'a pas changé, on garde l'ancien slug
                 $validatedData['slug'] = $article->slug;
            }
        }

        // S'assurer que prix_promotionnel est null si vide
        if (array_key_exists('prix_promotionnel', $validatedData) && $validatedData['prix_promotionnel'] === null) {
            $validatedData['prix_promotionnel'] = null;
        } else if (!array_key_exists('prix_promotionnel', $validatedData)) {
            // Si la clé n'est pas du tout dans les données validées (parce que nullable et non envoyée), la définir à null
            // pour éviter les problèmes si elle était précédemment définie.
             $validatedData['prix_promotionnel'] = null;
        }


        // Gestion de la suppression de l'image principale si la case est cochée
        if ($request->has('supprimer_image_principale') && $request->input('supprimer_image_principale') == '1') {
            if ($article->image_principale) {
                Storage::disk('public')->delete($article->image_principale);
                $validatedData['image_principale'] = null;
            }
        }

        // Gestion de l'upload d'une nouvelle image principale
        if ($request->hasFile('image_principale')) {
            // Supprimer l'ancienne image si elle existe
            if ($article->image_principale) {
                Storage::disk('public')->delete($article->image_principale);
            }
            $imagePath = $request->file('image_principale')->store('articles_images', 'public');
            $validatedData['image_principale'] = $imagePath;
        }
        // Si ni suppression ni nouvel upload, et que image_principale n'est pas dans validatedData,
        // on s'assure de ne pas l'effacer par erreur si le champ file était juste vide.
        // validatedData ne contiendra image_principale que si un nouveau fichier est uploadé.
        // Si le champ est vide, il n'est pas dans la requête.
        // Si supprimer_image_principale est coché, il sera mis à null ci-dessus.
        // Donc, si image_principale n'est pas dans validatedData et que supprimer_image_principale n'est pas coché,
        // on ne touche pas à la valeur existante. $article->update ne la modifiera pas.


        $article->update($validatedData);

        return redirect()->route('articles.index')->with('success', 'Article mis à jour avec succès.');
    }

    /**
     * Supprime la ressource spécifiée de la base de données.
     *
     * @param  \App\Models\Article  $article L'instance de l'article à supprimer.
     * @return \Illuminate\Http\RedirectResponse Une redirection vers la liste des articles avec un message de succès.
     */
    public function destroy(Article $article): \Illuminate\Http\RedirectResponse
    {
        // Supprimer l'image principale associée si elle existe
        if ($article->image_principale) {
            Storage::disk('public')->delete($article->image_principale);
        }

        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Article supprimé avec succès.');
    }
}

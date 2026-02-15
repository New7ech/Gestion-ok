<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Article;
use App\Models\Fournisseur;
use App\Models\User;
use App\Models\Categorie;
use Carbon\Carbon;

class AccueilController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Statistiques des factures
        $nombreFactures = Facture::query()->count();

        $montantTotal = Facture::query()
            ->whereBetween('date_facture', [$startOfMonth, $endOfMonth])
            ->sum('montant_ttc');

        $nombreFacturesPayees = Facture::query()
            ->where('statut_paiement', Facture::STATUS_PAYEE)
            ->count();

        $nombreFacturesImpayees = Facture::query()
            ->where('statut_paiement', Facture::STATUS_IMPAYEE)
            ->count();

        $montantImpayes = Facture::query()
            ->where('statut_paiement', Facture::STATUS_IMPAYEE)
            ->sum('montant_ttc');

        $nombreFacturesMoisCourant = Facture::query()
            ->whereBetween('date_facture', [$startOfMonth, $endOfMonth])
            ->count();

        $montantCarte = Facture::query()->where('mode_paiement', 'carte')->sum('montant_ttc');
        $montantCheque = Facture::query()->where('mode_paiement', 'chèque')->sum('montant_ttc');
        $montantEspeces = Facture::query()->where('mode_paiement', 'espèces')->sum('montant_ttc');

        // Statistiques supplémentaires
        $nombreArticles = Article::query()->count();
        $nombreFournisseurs = Fournisseur::query()->count();
        $nombreUtilisateurs = User::query()->count();
        $nombreCategories = Categorie::query()->count();

        $facturesImpayees = Facture::query()
            ->where('statut_paiement', Facture::STATUS_IMPAYEE)
            ->latest('date_facture')
            ->get();

        $facturesRecentes = Facture::query()
            ->latest('date_facture')
            ->limit(10)
            ->get();

        // Données pour les graphiques
        $driver = Facture::query()->getConnection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "CAST(strftime('%m', date_facture) AS INTEGER)"
            : 'MONTH(date_facture)';

        $totauxImpayesParMois = Facture::query()
            ->selectRaw($monthExpression . ' as mois, SUM(montant_ttc) as total')
            ->whereYear('date_facture', $now->year)
            ->where('statut_paiement', Facture::STATUS_IMPAYEE)
            ->groupByRaw($monthExpression)
            ->pluck('total', 'mois');

        // Données pour graphique des modes de paiement
        $paiementModes = Facture::query()
            ->selectRaw('mode_paiement, COUNT(*) as count, SUM(montant_ttc) as total')
            ->whereBetween('date_facture', [$startOfMonth, $endOfMonth])
            ->groupBy('mode_paiement')
            ->get();

        // Données pour graphique des articles par catégorie
        $articlesParCategorie = Article::query()
            ->join('categories', 'articles.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category, COUNT(*) as count')
            ->groupBy('categories.name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Données pour graphique des fournisseurs actifs
        $fournisseursActifs = Fournisseur::query()
            ->withCount('articles')
            ->orderByDesc('articles_count')
            ->limit(5)
            ->get();

        $labels = [];
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = Carbon::create($now->year, $month, 1)->translatedFormat('M');
            $data[] = (float) ($totauxImpayesParMois[$month] ?? 0);
        }

        return view('welcome', compact(
            'nombreFactures',
            'montantTotal',
            'nombreFacturesPayees',
            'nombreFacturesImpayees',
            'montantImpayes',
            'nombreFacturesMoisCourant',
            'montantCarte',
            'montantCheque',
            'montantEspeces',
            'nombreArticles',
            'nombreFournisseurs',
            'nombreUtilisateurs',
            'nombreCategories',
            'facturesImpayees',
            'facturesRecentes',
            'paiementModes',
            'articlesParCategorie',
            'fournisseursActifs',
            'labels',
            'data'
        ));
    }
}


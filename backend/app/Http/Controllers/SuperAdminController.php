<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Categorie;
use App\Models\Groupe;
use App\Models\Joueur;
use App\Models\Paiement;
use App\Models\NotificationAcademie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    // dashborad
    public function index()
    {
        $stats = [
            'coachs'    => User::where('role', 'coach')->count(),
            'parents'   => User::where('role', 'parent')->count(),
            'joueurs'   => Joueur::count(),
            'groupes'   => Groupe::count(),
            'categories'=> Categorie::count(),
            'paiements' => Paiement::where('statut', 'paid')->sum('montant'),
        ];

        $recentPaiements = Paiement::with('parent')->latest()->take(5)->get();
        $notifications   = NotificationAcademie::with('destinataire')->latest()->take(5)->get();

        $allCategories = Categorie::withCount(['groupes', 'joueurs'])
            ->orderBy('nom')
            ->get();

        $allGroupes = Groupe::with(['categorie', 'coach'])
            ->withCount('joueurs')
            ->orderBy('nom')
            ->get();

        $allJoueurs = Joueur::with(['parent', 'categorie', 'groupe'])
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $allCoachs = User::where('role', 'coach')
            ->withCount('groupesGeres')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        $allParents = User::where('role', 'parent')
            ->withCount('joueurs')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        return response()->json([
            'stats'            => $stats,
            'recentPaiements'  => $recentPaiements,
            'notifications'    => $notifications,
            'allCategories'    => $allCategories,
            'allGroupes'       => $allGroupes,
            'allJoueurs'       => $allJoueurs,
            'allCoachs'        => $allCoachs,
            'allParents'       => $allParents
        ]);
    }

    // coach
    public function storeCoach(Request $request)
    {
        $data = $request->validate([
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $coach = User::create([
            'nom'      => $data['nom'],
            'prenom'   => $data['prenom'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'coach',
        ]);

        return response()->json([
            'message' => 'Coach créé avec succès.',
            'coach'   => $coach
        ], 201);
    }

    public function updateCoach(Request $request, User $coach)
    {
        abort_if($coach->role !== 'coach', 404);
        $data = $request->validate([
            'nom'    => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email'  => 'required|email|unique:users,email,' . $coach->id,
        ]);

        $coach->update($data);

        return response()->json([
            'message' => 'Coach mis à jour.',
            'coach'   => $coach
        ]);
    }

    public function destroyCoach(User $coach)
    {
        abort_if($coach->role !== 'coach', 404);
        $coach->delete();

        return response()->json([
            'message' => 'Coach supprimé.'
        ]);
    }

    // parents
    public function storeParent(Request $request)
    {
        $data = $request->validate([
            'nom'      => 'required|string|max:100',
            'prenom'   => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $parent = User::create([
            'nom'      => $data['nom'],
            'prenom'   => $data['prenom'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'parent',
        ]);

        return response()->json([
            'message' => 'Parent créé avec succès.',
            'parent'  => $parent
        ], 201);
    }

    public function updateParent(Request $request, User $parent)
    {
        abort_if($parent->role !== 'parent', 404);
        $data = $request->validate([
            'nom'    => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email'  => 'required|email|unique:users,email,' . $parent->id,
        ]);

        $parent->update($data);

        return response()->json([
            'message' => 'Parent mis à jour.',
            'parent'  => $parent
        ]);
    }

    public function destroyParent(User $parent)
    {
        abort_if($parent->role !== 'parent', 404);
        $parent->delete();

        return response()->json([
            'message' => 'Parent supprimé.'
        ]);
    }

    // categorie
    public function storeCategorie(Request $request)
    {
        $data = $request->validate([
            'nom'         => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $categorie = Categorie::create($data);

        return response()->json([
            'message'   => 'Catégorie créée.',
            'categorie' => $categorie
        ], 201);
    }

    public function updateCategorie(Request $request, Categorie $categorie)
    {
        $data = $request->validate([
            'nom'         => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        $categorie->update($data);

        return response()->json([
            'message'   => 'Catégorie mise à jour.',
            'categorie' => $categorie
        ]);
    }

    public function destroyCategorie(Categorie $categorie)
    {
        $categorie->delete();

        return response()->json([
            'message' => 'Catégorie supprimée.'
        ]);
    }

    // groupe
    public function storeGroupe(Request $request)
    {
        $data = $request->validate([
            'nom'          => 'required|string|max:100',
            'categorie_id' => 'required|exists:categories,id',
            'coach_id'     => 'nullable|exists:users,id',
        ]);

        $groupe = Groupe::create($data);

        return response()->json([
            'message' => 'Groupe créé.',
            'groupe'  => $groupe
        ], 201);
    }

    public function updateGroupe(Request $request, Groupe $groupe)
    {
        $data = $request->validate([
            'nom'          => 'required|string|max:100',
            'categorie_id' => 'required|exists:categories,id',
            'coach_id'     => 'nullable|exists:users,id',
        ]);

        $groupe->update($data);

        return response()->json([
            'message' => 'Groupe mis à jour.',
            'groupe'  => $groupe
        ]);
    }

    public function destroyGroupe(Groupe $groupe)
    {
        $groupe->delete();

        return response()->json([
            'message' => 'Groupe supprimé.'
        ]);
    }

    public function assignCoachToGroupe(Request $request, Groupe $groupe)
    {
        $request->validate(['coach_id' => 'required|exists:users,id']);

        $groupe->update(['coach_id' => $request->coach_id]);

        return response()->json([
            'message' => 'Coach assigné au groupe.'
        ]);
    }

    // joueur
    public function joueurs()
    {
        $joueurs = Joueur::with(['parent', 'categorie', 'groupe'])->latest()->paginate(20);

        return response()->json($joueurs);
    }

    public function storeJoueur(Request $request)
    {
        $data = $request->validate([
            'nom'             => 'required|string|max:100',
            'prenom'          => 'required|string|max:100',
            'date_naissance'  => 'required|date',
            'parent_id'       => 'required|exists:users,id',
            'categorie_id'    => 'nullable|exists:categories,id',
            'photo'           => 'nullable|string',
        ]);

        $joueur = Joueur::create($data);

        return response()->json([
            'message' => 'Joueur créé.',
            'joueur'  => $joueur
        ], 201);
    }

    public function updateJoueur(Request $request, Joueur $joueur)
    {
        $data = $request->validate([
            'nom'            => 'required|string|max:100',
            'prenom'         => 'required|string|max:100',
            'date_naissance' => 'required|date',
            'parent_id'      => 'required|exists:users,id',
            'categorie_id'   => 'nullable|exists:categories,id',
            'photo'          => 'nullable|string',
        ]);

        $joueur->update($data);

        return response()->json([
            'message' => 'Joueur mis à jour.',
            'joueur'  => $joueur
        ]);
    }

    public function destroyJoueur(Joueur $joueur)
    {
        $joueur->delete();

        return response()->json([
            'message' => 'Joueur supprimé.'
        ]);
    }

    public function assignJoueurToCategorie(Request $request, Joueur $joueur)
    {
        $request->validate(['categorie_id' => 'required|exists:categories,id']);

        $joueur->update(['categorie_id' => $request->categorie_id, 'groupe_id' => null]);

        return response()->json([
            'message' => 'Joueur affecté à la catégorie.'
        ]);
    }

    // paiement
    public function paiements()
    {
        $paiements = Paiement::with('parent')->latest()->paginate(20);

        return response()->json($paiements);
    }

    public function validerPaiement(Paiement $paiement)
    {
        if ($paiement->statut !== 'pending') {
            return response()->json([
                'message' => 'Seuls les paiements en attente peuvent etre valides.'
            ], 422);
        }

        try {
            $paiement->update(['statut' => 'paid']);

            $mois = optional($paiement->mois_concerne)->format('m/Y') ?? 'ce mois';
            $senderId = auth()->id();

            NotificationAcademie::create([
                'titre' => 'Paiement validé',
                'message' => 'Votre paiement de ' . $paiement->montant . ' MAD pour le mois ' . $mois . ' a été validé avec succès par l\'administration.',
                'user_id' => $paiement->parent_id,
                'sender_id' => $senderId,
                'est_lu' => false,
            ]);

            return response()->json([
                'message' => 'Paiement valide et notification envoyee au parent.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Paiement valide, mais la notification a echoue: ' . $e->getMessage()
            ], 500);
        }
    }

    //
    public function storeNotification(Request $request)
    {
        $request->validate([
            'titre'    => 'required|string|max:200',
            'message'  => 'required|string',
            'cible'    => 'required|in:all,coaches,parents,specific',
            'user_ids' => 'required_if:cible,specific|array',
        ]);

        $sender = auth()->user();

        $destinataires = match($request->cible) {
            'all'      => User::where('role', '!=', 'superadmin')->get(),
            'coaches'  => User::where('role', 'coach')->get(),
            'parents'  => User::where('role', 'parent')->get(),
            'specific' => User::whereIn('id', $request->user_ids)->get(),
        };

        foreach ($destinataires as $user) {
            NotificationAcademie::create([
                'titre'     => $request->titre,
                'message'   => $request->message,
                'user_id'   => $user->id,
                'sender_id' => $sender->id,
                'est_lu'    => false,
            ]);
        }

        return response()->json([
            'message' => 'Notification envoyée à ' . $destinataires->count() . ' utilisateur(s).'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Joueur;
use App\Models\Paiement;
use App\Models\NotificationAcademie;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class ParentController extends Controller
{
    // ... (index and showJoueur omitted for brevity, but I must keep them if I'm replacing the whole block or use multi_replace)

    // dashboard
    public function index()
    {
        $parent = auth()->user();

        $joueurs = $parent->joueurs()
            ->select(['id', 'parent_id', 'categorie_id', 'groupe_id', 'prenom', 'nom'])
            ->with([
                'categorie:id,nom',
                'groupe:id,nom',
            ])
            ->withCount('evaluations')
            ->get();

        $paiements = $parent->paiements()
            ->select(['id', 'parent_id', 'montant', 'mois_concerne', 'statut', 'created_at'])
            ->latest()
            ->take(5)
            ->get();

        $notifications = $parent->notificationsRecues()
            ->select(['id', 'user_id', 'sender_id', 'titre', 'message', 'est_lu', 'created_at'])
            ->where('est_lu', false)
            ->with('expediteur:id,prenom,nom')
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'parent'        => $parent,
            'joueurs'       => $joueurs,
            'paiements'      => $paiements,
            'notifications' => $notifications
        ]);
    }
    // $js=Joueur::where("date_naissance","<","01/01/2011")->get();
    // foreach($js as $j){
    // $j=toUpper($j->nom." ".$j->prenom);
    // }

    //joueur
    public function showJoueur(Joueur $joueur)
    {
        abort_unless($joueur->parent_id === auth()->id(), 403);


        $joueur->load(
            'categorie',
            'groupe.coach',
            'evaluations.coach',
            'presences.seance'
        );

        $seancesGroupe = $joueur->groupe
            ? $joueur->groupe->seances()->orderBy('date_seance', 'desc')->take(10)->get()
            : collect();

        return response()->json([
            'joueur'        => $joueur,
            'seancesGroupe' => $seancesGroupe
        ]);
    }

    // paiement
    public function createStripeIntent(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:1',
            'mois_concerne' => 'required|date_format:Y-m',
        ]);

        $parent = auth()->user();

        try {
            $stripeSecret = (string) config('services.stripe.secret');
            if ($stripeSecret === '') {
                throw new \RuntimeException('La cle Stripe secret est manquante dans le fichier .env.');
            }

            Stripe::setApiKey($stripeSecret);

            $intent = PaymentIntent::create([
                'amount' => (int) round(((float) $request->montant) * 100),
                'currency' => 'mad',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'parent_id' => (string) $parent->id,
                    'mois_concerne' => $request->mois_concerne,
                ],
            ]);

            return response()->json([
                'client_secret' => $intent->client_secret,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Creation du paiement Stripe impossible: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function confirmStripeIntent(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'montant' => 'required|numeric|min:1',
            'mois_concerne' => 'required|date_format:Y-m',
        ]);

        $parent = auth()->user();

        try {
            $stripeSecret = (string) config('services.stripe.secret');
            if ($stripeSecret === '') {
                throw new \RuntimeException('La cle Stripe secret est manquante dans le fichier .env.');
            }

            Stripe::setApiKey($stripeSecret);

            $intent = PaymentIntent::retrieve($request->payment_intent_id);

            if (($intent->metadata->parent_id ?? null) !== (string) $parent->id) {
                return response()->json([
                    'message' => 'Paiement invalide pour cet utilisateur.',
                ], 403);
            }

            if (($intent->metadata->mois_concerne ?? null) !== $request->mois_concerne) {
                return response()->json([
                    'message' => 'Le mois de paiement ne correspond pas.',
                ], 422);
            }

            $statut = match ($intent->status) {
                'succeeded' => 'paid',
                'processing', 'requires_capture' => 'pending',
                default => 'failed',
            };

            $paiement = Paiement::updateOrCreate(
                ['stripe_transaction_id' => $intent->id],
                [
                    'montant' => $request->montant,
                    'mois_concerne' => $request->mois_concerne . '-01',
                    'statut' => $statut,
                    'parent_id' => $parent->id,
                ]
            );

            if ($statut === 'paid') {
                NotificationAcademie::create([
                    'titre' => 'Paiement effectué',
                    'message' => 'Votre paiement de ' . $request->montant . ' MAD pour le mois ' . $request->mois_concerne . ' a été reçu avec succès.',
                    'user_id' => $parent->id,
                    'sender_id' => null, // Système
                    'est_lu' => false,
                ]);
            }

            return response()->json([
                'message' => $statut === 'paid'
                    ? 'Paiement valide automatiquement.'
                    : ($statut === 'pending'
                        ? 'Paiement enregistre. En cours de traitement.'
                        : 'Paiement refuse ou echoue.'),
                'statut' => $statut,
                'paiement' => $paiement
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Confirmation Stripe impossible: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function storePaiement(Request $request)
    {
        $request->validate([
            'montant'       => 'required|numeric|min:1',
            'mois_concerne' => 'required|date_format:Y-m',
            'card_number'   => 'nullable|string',
            'exp_month'     => 'nullable|integer|between:1,12',
            'exp_year'      => 'nullable|integer|min:' . now()->year,
            'cvv'           => 'nullable|regex:/^\d{3,4}$/',
            'carte_test'    => 'nullable|in:visa,declined,insufficient_funds,authentication_required',
        ]);

        $parent = auth()->user();

        try {
            $stripeSecret = (string) config('services.stripe.secret');
            if ($stripeSecret === '') {
                throw new \RuntimeException('La cle Stripe secret est manquante dans le fichier .env.');
            }

            Stripe::setApiKey($stripeSecret);

            $paymentMethodMap = [
                'visa' => 'pm_card_visa',
                'declined' => 'pm_card_chargeDeclined',
                'insufficient_funds' => 'pm_card_chargeCustomerFail',
                'authentication_required' => 'pm_card_authenticationRequired',
            ];

            $cardNumber = preg_replace('/\D+/', '', (string) $request->input('card_number', ''));

            $cardByNumber = [
                '4242424242424242' => 'visa',
                '4000000000000002' => 'declined',
                '4000000000009995' => 'insufficient_funds',
                '4000002500003155' => 'authentication_required',
            ];

            if ($cardNumber !== '') {
                if (!$request->filled('exp_month') || !$request->filled('exp_year') || !$request->filled('cvv')) {
                    return response()->json([
                        'message' => 'Veuillez renseigner le numero de carte, la date d\'expiration et le CVV.',
                    ], 422);
                }

                if (!array_key_exists($cardNumber, $cardByNumber)) {
                    return response()->json([
                        'message' => 'Carte test non reconnue. Utilisez un numero de carte fake Stripe valide.',
                    ], 422);
                }

                $selectedCard = $cardByNumber[$cardNumber];
            } else {
                $selectedCard = $request->input('carte_test', 'visa');
            }

            $intent = PaymentIntent::create([
                'amount' => (int) round(((float) $request->montant) * 100),
                'currency' => 'eur',
                'payment_method_types' => ['card'],
                'payment_method' => $paymentMethodMap[$selectedCard] ?? 'pm_card_visa',
                'confirm' => true,
                'metadata' => [
                    'parent_id' => (string) $parent->id,
                    'mois_concerne' => $request->mois_concerne,
                    'carte_test' => $selectedCard,
                    'card_last4' => $cardNumber !== '' ? substr($cardNumber, -4) : null,
                ],
            ]);

            $statut = $intent->status === 'succeeded' ? 'paid' : 'failed';

            $paiement = Paiement::create([
                'montant'               => $request->montant,
                'mois_concerne'         => $request->mois_concerne . '-01',
                'statut'                => $statut,
                'stripe_transaction_id' => $intent->id,
                'parent_id'             => $parent->id,
            ]);

            if ($statut === 'paid') {
                NotificationAcademie::create([
                    'titre' => 'Paiement effectué',
                    'message' => 'Votre paiement de ' . $request->montant . ' MAD pour le mois ' . $request->mois_concerne . ' a été reçu avec succès.',
                    'user_id' => $parent->id,
                    'sender_id' => null, // Système
                    'est_lu' => false,
                ]);
            }

            return response()->json([
                'message'  => $statut === 'paid'
                    ? 'Paiement valide automatiquement.'
                    : 'Paiement refuse ou echoue.',
                'paiement' => $paiement
            ]);
        } catch (\Throwable $e) {
            Paiement::create([
                'montant'               => $request->montant,
                'mois_concerne'         => $request->mois_concerne . '-01',
                'statut'                => 'failed',
                'stripe_transaction_id' => null,
                'parent_id'             => $parent->id,
            ]);

            return response()->json([
                'message' => 'Paiement Stripe test echoue: ' . $e->getMessage(),
            ], 422);
        }
    }
}

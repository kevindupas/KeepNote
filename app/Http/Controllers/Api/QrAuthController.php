<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QrAuthController extends Controller
{
    /**
     * Authentification par QR Code
     */
    public function authenticate(string $token)
    {
        // Récupérer le token QR et vérifier sa validité
        $qrToken = DB::table('qr_auth_tokens')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$qrToken) {
            return response()->json([
                'message' => 'Token QR code invalide ou expiré'
            ], 401);
        }

        // Récupérer l'utilisateur associé au token
        $user = User::findOrFail($qrToken->user_id);

        // Supprimer le token pour éviter sa réutilisation
        DB::table('qr_auth_tokens')
            ->where('token', $token)
            ->delete();

        // Générer un token d'API pour l'utilisateur
        $apiToken = $user->createToken('qr_login')->plainTextToken;

        return response()->json([
            'access_token' => $apiToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
}

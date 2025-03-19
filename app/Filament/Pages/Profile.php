<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Profile extends BaseEditProfile
{
    public ?string $qrCodeToken = null;

    public function mount(): void
    {
        parent::mount();

        // Générer un token temporaire unique pour ce QR code
        $this->qrCodeToken = $this->generateQrToken();
    }

    protected function generateQrToken(): string
    {
        $user = Auth::user();
        $token = Str::random(64); // Token unique
        $expiresAt = now()->addMinutes(5); // Expire dans 5 minutes

        // Stocker le token temporaire en base de données
        DB::table('qr_auth_tokens')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);

        return $token;
    }

    public function refreshQrCode(): void
    {
        $this->qrCodeToken = $this->generateQrToken();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations personnelles')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required(),
                        TextInput::make('email')
                            ->label('Adresse e-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('Mot de passe')
                    ->schema([
                        TextInput::make('password')
                            ->label('Nouveau mot de passe')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn($state): bool => filled($state))
                            ->rule(Password::default()),
                        TextInput::make('password_confirmation')
                            ->label('Confirmer le mot de passe')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->requiredWith('password')
                            ->same('password'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Connexion mobile')
                    ->schema([
                        ViewField::make('qr_code')
                            ->view('filament.pages.profile.qr-code'),
                    ])
                    ->collapsible(),
            ]);
    }
}

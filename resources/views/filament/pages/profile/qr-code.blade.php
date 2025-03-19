{{-- resources/views/filament/pages/profile/qr-code.blade.php --}}
<div>
    <div class="flex justify-center mb-4">
        {!! QrCode::size(200)->generate(config('app.url') . '/api/auth/qr-login/' . $this->qrCodeToken) !!}
    </div>

    <div class="text-center text-sm text-gray-500">
        Ce QR code expire dans 5 minutes.
    </div>

    <div class="mt-4 flex justify-center">
        <x-filament::button wire:click="refreshQrCode" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="refreshQrCode">Générer un nouveau QR code</span>
            <span wire:loading wire:target="refreshQrCode">Génération...</span>
        </x-filament::button>
    </div>
</div>

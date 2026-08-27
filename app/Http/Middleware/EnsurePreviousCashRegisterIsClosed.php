<?php

namespace App\Http\Middleware;

use App\Filament\Resources\CashRegisters\CashRegisterResource;
use App\Models\CashRegister;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePreviousCashRegisterIsClosed
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ne pas exécuter pour les requêtes de déconnexion ou assets
        if ($request->routeIs('filament.admin.auth.logout') || $request->is('livewire/livewire.js', 'livewire/preview-file/*')) {
            return $next($request);
        }

        // Vérifier s'il existe une caisse ouverte d'un jour antérieur
        $unclosedPastRegister = CashRegister::where('status', 'open')
            ->where(function ($query): void {
                $query->whereDate('openedAt', '<', now()->toDateString())
                    ->orWhere(function ($q): void {
                        $q->whereNull('openedAt')
                            ->whereDate('created_at', '<', now()->toDateString());
                    });
            })
            ->oldest('openedAt')
            ->first();

        if (! $unclosedPastRegister) {
            return $next($request);
        }

        $targetUrl = CashRegisterResource::getUrl('view', ['record' => $unclosedPastRegister]);

        // Autoriser si l'utilisateur est déjà sur la page de détail de cette caisse
        if ($request->fullUrlIs($targetUrl.'*') || $request->url() === $targetUrl) {
            return $next($request);
        }

        // Pour les requêtes Livewire (ex: validation de la modal de clôture)
        if ($request->hasHeader('X-Livewire')) {
            // Si la requête provient de la page de la caisse à clôturer, laisser passer
            $referer = (string) $request->header('referer');
            if (str_contains($referer, "/admin/finance/cash-registers/{$unclosedPastRegister->id}")) {
                return $next($request);
            }
        }

        // Notification d'avertissement
        $dateStr = $unclosedPastRegister->openedAt ? $unclosedPastRegister->openedAt->format('d/m/Y') : $unclosedPastRegister->created_at->format('d/m/Y');

        Notification::make()
            ->title('Clôture de caisse requise')
            ->body("La session de caisse #{$unclosedPastRegister->id} du {$dateStr} n'a pas été clôturée. Veuillez procéder à son comptage et sa clôture avant toute autre opération.")
            ->warning()
            ->persistent()
            ->send();

        return redirect()->to($targetUrl);
    }
}

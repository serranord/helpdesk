<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider {
    public function register(): void {
        Passport::ignoreRoutes();
    }

    public function boot(): void {
        Paginator::useBootstrapFive();
        Passport::tokensCan([
            'mcp:use' => 'Consultar tickets y estadísticas y crear tickets en el HelpDesk',
            'offline_access' => 'Mantener la conexión mediante renovación de tokens',
        ]);
        Passport::tokensExpireIn(new \DateInterval('PT1H'));
        Passport::defaultScopes(['mcp:use']);
        Passport::refreshTokensExpireIn(new \DateInterval('P30D'));
        Passport::authorizationView('mcp.authorize');
    }
}

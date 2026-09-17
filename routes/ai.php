<?php

use App\Http\Controllers\McpOAuthMetadataController;
use App\Http\Middleware\AuthenticateMcp;
use App\Http\Middleware\AuthorizeMcpConnection;
use App\Http\Middleware\ValidateMcpOAuthRequest;
use App\Mcp\Servers\HelpdeskServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;

Route::middleware([ValidateMcpOAuthRequest::class, 'throttle:60,1'])->group(function () {
    Route::get('/.well-known/oauth-authorization-server', [McpOAuthMetadataController::class, 'authorizationServer'])->name('mcp.oauth.authorization-server');
    Route::get('/.well-known/oauth-protected-resource', [McpOAuthMetadataController::class, 'protectedResource'])->name('mcp.oauth.protected-resource');
    Route::get('/.well-known/oauth-protected-resource/mcp/helpdesk', [McpOAuthMetadataController::class, 'protectedResource']);
    Mcp::oauthRoutes();
    Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])->name('passport.token');
});

Route::middleware(['web', ValidateMcpOAuthRequest::class, AuthorizeMcpConnection::class, 'throttle:60,1'])->group(function () {
    Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->name('passport.authorizations.authorize');
    Route::post('/oauth/authorize', [ApproveAuthorizationController::class, 'approve'])->name('passport.authorizations.approve');
    Route::delete('/oauth/authorize', [DenyAuthorizationController::class, 'deny'])->name('passport.authorizations.deny');
});

Mcp::local('helpdesk', HelpdeskServer::class);

Route::middleware([AuthenticateMcp::class, 'throttle:60,1'])->group(function () {
    Mcp::web('/mcp/helpdesk', HelpdeskServer::class);
});

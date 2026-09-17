<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\RefreshToken;

class McpConnectionController extends Controller
{
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens()->with('client')->where('revoked', false)->latest('created_at')->get();

        return view('mcp.connections', compact('tokens'));
    }

    public function destroy(Request $request, string $client)
    {
        DB::transaction(function () use ($request, $client) {
            $tokens = $request->user()->tokens()->where('client_id', $client)->lockForUpdate()->get();
            foreach ($tokens as $token) {
                RefreshToken::where('access_token_id', $token->id)->update(['revoked' => true]);
                $token->update(['revoked' => true]);
            }
        });

        return redirect()->route('mcp.connections')->with('status', 'Conexión revocada. La aplicación deberá solicitar autorización nuevamente.');
    }
}

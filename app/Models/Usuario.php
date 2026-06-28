<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use SoftDeletes, Notifiable;

    protected $table    = 'usuarios';
    protected $fillable = [
        'microsoft_id','nombre','correo','password','rol','estado',
        'telefono','departamento','cargo','login_microsoft',
    ];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = [
        'password'        => 'hashed',
        'login_microsoft' => 'boolean',
    ];

    public function esAdministrador(): bool { return $this->rol === 'administrador'; }
    public function esTecnico(): bool       { return $this->rol === 'tecnico'; }
    public function esSolicitante(): bool   { return $this->rol === 'solicitante'; }
    public function puedeGestionar(): bool  { return in_array($this->rol, ['administrador','tecnico']); }

    public function getRolLabelAttribute(): string {
        return match($this->rol) {
            'administrador' => 'Administrador',
            'tecnico'       => 'Técnico',
            'solicitante'   => 'Solicitante',
            default         => $this->rol,
        };
    }

    public function ticketsCreados()   { return $this->hasMany(Ticket::class, 'solicitante_id'); }
    public function ticketsAsignados() { return $this->hasMany(Ticket::class, 'tecnico_id'); }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AvisoTI extends Model {
    protected $table    = 'avisos_ti';
    protected $fillable = ['titulo','mensaje','tipo','activo','expira_en','creado_por'];
    protected $casts    = ['activo' => 'boolean', 'expira_en' => 'datetime'];

    public function creadoPor() { return $this->belongsTo(Usuario::class, 'creado_por'); }

    public function estaVigente(): bool {
        if (!$this->activo) return false;
        if ($this->expira_en && now()->gt($this->expira_en)) return false;
        return true;
    }

    public function getIconoAttribute(): string {
        return match($this->tipo) {
            'info'         => 'ℹ️',
            'advertencia'  => '⚠️',
            'critico'      => '🔴',
            'mantenimiento'=> '🔧',
            'resuelto'     => '✅',
            default        => '📢',
        };
    }

    public function getColorAttribute(): string {
        return match($this->tipo) {
            'info'         => 'blue',
            'advertencia'  => 'amber',
            'critico'      => 'red',
            'mantenimiento'=> 'purple',
            'resuelto'     => 'green',
            default        => 'gray',
        };
    }

    public static function vigentes() {
        return static::where('activo', true)
            ->where(fn($q) => $q->whereNull('expira_en')->orWhere('expira_en', '>', now()))
            ->orderByDesc('created_at');
    }
}

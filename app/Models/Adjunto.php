<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Adjunto extends Model {
    protected $table    = 'adjuntos';
    protected $fillable = ['ticket_id','usuario_id','nombre_original','nombre_guardado','mime_type','tamano'];

    public function ticket()  { return $this->belongsTo(Ticket::class); }
    public function usuario() { return $this->belongsTo(Usuario::class); }

    public function getTamanoFormateadoAttribute(): string {
        $kb = $this->tamano / 1024;
        return $kb < 1024 ? round($kb, 1).' KB' : round($kb/1024, 1).' MB';
    }

    public function esImagen(): bool {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIconoAttribute(): string {
        if ($this->esImagen()) return '🖼️';
        return match(true) {
            str_contains($this->mime_type, 'pdf')  => '📄',
            str_contains($this->mime_type, 'word') => '📝',
            str_contains($this->mime_type, 'excel')|| str_contains($this->mime_type, 'spreadsheet') => '📊',
            default => '📎',
        };
    }
}

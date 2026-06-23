<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KbArticulo extends Model {
    protected $table    = 'kb_articulos';
    protected $fillable = ['titulo','contenido','slug','categoria_id','autor_id','estado','vistas','destacado'];
    protected $casts    = ['destacado' => 'boolean'];

    public function categoria() { return $this->belongsTo(Categoria::class); }
    public function autor()     { return $this->belongsTo(Usuario::class, 'autor_id'); }

    public static function generarSlug(string $titulo): string {
        $slug = Str::slug($titulo);
        $count = static::where('slug', 'like', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }

    public function getTamanoContenidoAttribute(): string {
        $words = str_word_count(strip_tags($this->contenido));
        $mins  = ceil($words / 200);
        return "{$mins} min de lectura";
    }
}

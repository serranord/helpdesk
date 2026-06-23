<?php
namespace App\Http\Controllers;
use App\Models\KbArticulo; use App\Models\Categoria;
use Illuminate\Http\Request;

class KbController extends Controller {

    public function index(Request $request) {
        $query = KbArticulo::with(['categoria','autor'])->where('estado','publicado');
        if ($request->filled('buscar'))
            $query->where(fn($q) => $q->where('titulo','like','%'.$request->buscar.'%')
                ->orWhere('contenido','like','%'.$request->buscar.'%'));
        if ($request->filled('categoria'))
            $query->where('categoria_id', $request->categoria);

        $articulos   = $query->orderByDesc('destacado')->orderByDesc('created_at')->paginate(12)->withQueryString();
        $destacados  = KbArticulo::where('estado','publicado')->where('destacado',true)->limit(3)->get();
        $categorias  = Categoria::whereHas('kb_articulos')->get();
        return view('kb.index', compact('articulos','destacados','categorias'));
    }

    public function show(KbArticulo $kbArticulo) {
        if ($kbArticulo->estado !== 'publicado' && !auth()->user()->puedeGestionar()) abort(404);
        $kbArticulo->increment('vistas');
        $relacionados = KbArticulo::where('estado','publicado')
            ->where('id','!=',$kbArticulo->id)
            ->where('categoria_id',$kbArticulo->categoria_id)
            ->limit(3)->get();
        return view('kb.show', compact('kbArticulo','relacionados'));
    }

    // Admin: gestión
    public function admin() {
        $articulos  = KbArticulo::with(['categoria','autor'])->orderByDesc('created_at')->paginate(15);
        $categorias = Categoria::where('activa',true)->get();
        return view('kb.admin', compact('articulos','categorias'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'titulo'       => 'required|string|max:200',
            'contenido'    => 'required|string',
            'categoria_id' => 'nullable|exists:categorias,id',
            'estado'       => 'required|in:borrador,publicado',
            'destacado'    => 'boolean',
        ]);
        $data['autor_id'] = auth()->id();
        $data['slug']     = KbArticulo::generarSlug($data['titulo']);
        KbArticulo::create($data);
        return back()->with('success', 'Artículo creado correctamente.');
    }

    public function edit(KbArticulo $kbArticulo) {
        $categorias = Categoria::where('activa',true)->get();
        return view('kb.edit', compact('kbArticulo','categorias'));
    }

    public function update(Request $request, KbArticulo $kbArticulo) {
        $data = $request->validate([
            'titulo'       => 'required|string|max:200',
            'contenido'    => 'required|string',
            'categoria_id' => 'nullable|exists:categorias,id',
            'estado'       => 'required|in:borrador,publicado',
            'destacado'    => 'boolean',
        ]);
        $kbArticulo->update($data);
        return redirect()->route('kb.admin')->with('success', 'Artículo actualizado.');
    }

    public function destroy(KbArticulo $kbArticulo) {
        $kbArticulo->delete();
        return back()->with('success', 'Artículo eliminado.');
    }
}

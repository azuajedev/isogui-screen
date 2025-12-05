<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesignImageController extends Controller
{
    /**
     * Subir una imagen para un diseño.
     * Guarda en storage/app/designs/{user_id}/{design_id}/{random_name}.ext
     */
    public function store(Request $request, $designId)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // max 10MB
        ]);

        // Verificar que el diseño existe y pertenece al usuario
        $design = Design::where('id', $designId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $file = $request->file('image');
        $userId = auth()->id();
        
        // Generar nombre único
        $randomName = Str::random(40);
        $extension = $file->getClientOriginalExtension();
        $filename = "{$randomName}.{$extension}";
        
        // Guardar en storage/app/designs/{user_id}/{design_id}/
        $path = "{$userId}/{$designId}/{$filename}";
        
        $disk = 'designs'; // Usar el disco 'designs' configurado
        Storage::disk($disk)->putFileAs(
            "{$userId}/{$designId}",
            $file,
            $filename
        );

        return response()->json([
            'success' => true,
            'filename' => $filename,
            'url' => route('api.designs.images.show', [
                'design' => $designId,
                'filename' => $filename
            ]),
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    /**
     * Servir una imagen (protegido, solo el dueño puede verla).
     */
    public function show($designId, $filename)
    {
        // Verificar que el diseño existe y pertenece al usuario
        $design = Design::where('id', $designId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $userId = auth()->id();
        $path = "{$userId}/{$designId}/{$filename}";
        
        $disk = 'designs'; // Usar el disco 'designs' configurado
        
        if (!Storage::disk($disk)->exists($path)) {
            abort(404, 'Imagen no encontrada');
        }

        $file = Storage::disk($disk)->get($path);
        $mimeType = Storage::disk($disk)->mimeType($path);

        return response($file, 200)->header('Content-Type', $mimeType);
    }

    /**
     * Eliminar una imagen.
     */
    public function destroy($designId, $filename)
    {
        // Verificar que el diseño existe y pertenece al usuario
        $design = Design::where('id', $designId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $userId = auth()->id();
        $path = "{$userId}/{$designId}/{$filename}";
        
        $disk = 'designs'; // Usar el disco 'designs' configurado
        
        if (Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Imagen eliminada'
        ]);
    }
}

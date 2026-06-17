<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ContactosConnection;
use App\Http\Resources\ContactoResource;
use App\Models\Contacto;

class ContactoController extends Controller
{
    public function index() {
        $contactos = \App\Models\User::with('image')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'foto' => $user->image ? $user->image->url : null,
            ];
        });
        return response()->json($contactos, 200);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20',    
            'email' => 'nullable|email|max:255',   
            'foto'  => 'nullable|string',          
        ]);
        
        // 1 y 2. Guardar el registro usando User
        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => bcrypt('secret123'),
        ]);

        // 4. Guardar imagen polimórfica si existe
        if (!empty($validated['foto'])) {
            $user->image()->create(['url' => $validated['foto']]);
        }
        
        // 5. Retornar el JSON compatible
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'foto' => $validated['foto'] ?? null,
        ], 201);
    }

    public function show($id) {
        $user = \App\Models\User::with('image')->find($id);
        if (!$user) return response()->json(['message' => 'No encontrado'], 404);
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'foto' => $user->image ? $user->image->url : null,
        ], 200);
    }

    public function update(Request $request, $id) {
        $user = \App\Models\User::with('image')->find($id);
        if (!$user) return response()->json(['message' => 'No encontrado'], 404);
        
        $validated = $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20', 
            'email' => 'nullable|email|max:255',
            'foto'  => 'nullable|string',                 
        ]);
        
        $userData = collect($validated)->except('foto')->toArray();
        if (!empty($userData)) {
            $user->update($userData);
        }

        if (array_key_exists('foto', $validated)) {
            if (empty($validated['foto'])) {
                if ($user->image) {
                    $user->image()->delete();
                }
            } else {
                if ($user->image) {
                    $user->image()->update(['url' => $validated['foto']]);
                } else {
                    $user->image()->create(['url' => $validated['foto']]);
                }
            }
        }
        
        $user->load('image'); // Recargar relación actualizada
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'foto' => $user->image ? $user->image->url : null,
        ], 200);
    }

    public function destroy($id) {
        $user = \App\Models\User::with('image')->find($id);
        if (!$user) return response()->json(['message' => 'No encontrado'], 404);
        
        // Eliminar explícitamente la imagen polimórfica primero (Evitar huérfanos)
        if ($user->image) {
            $user->image()->delete();
        }
        
        // Luego eliminar al usuario
        $user->delete();
        return response()->json(['message' => 'Eliminado correctamente'], 200);
    }
}
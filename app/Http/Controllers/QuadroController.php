<?php

namespace App\Http\Controllers;

use App\Models\Quadro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuadroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // listar quadros do usuário autenticado
        $userId = Auth::id();
        $quadros = Quadro::where('user_id', $userId)->orderBy('created_at', 'desc')->get();

        // carregar lista de usuarios para popular o modal de criação (somente admins podem alterar)
        $users = User::orderBy('name')->get();

        return view('dashboard', compact('quadros', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'publico' => 'sometimes|boolean',
        ]);

        $quadro = Quadro::create([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'publico' => isset($data['publico']) ? (bool)$data['publico'] : false,
            'user_id' => Auth::id(),
        ]);

        // associar usuários enviados (array de usuarios[<id>][papel]=... quando checkbox marcado)
        $usersInput = $request->input('usuarios', []);
        foreach ($usersInput as $userId => $info) {
            // somente quando marcado explicitamente
            if (empty($info['include'])) continue;
            // validar existência do usuário
            $userExists = User::find((int)$userId);
            if (!$userExists) continue;
            $papel = in_array($info['papel'] ?? '', ['owner','admin','membro']) ? $info['papel'] : 'membro';
            // evitar duplicar owner
            if ((int)$userId === (int)Auth::id()) {
                $papel = 'owner';
            }
            \App\Models\QuadroUsuario::create([
                'quadro_id' => $quadro->id,
                'user_id' => $userId,
                'papel' => $papel,
            ]);
        }

        // garantir que o criador é owner
        if (!\App\Models\QuadroUsuario::where('quadro_id', $quadro->id)->where('user_id', Auth::id())->exists()) {
            \App\Models\QuadroUsuario::create([
                'quadro_id' => $quadro->id,
                'user_id' => Auth::id(),
                'papel' => 'owner'
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Quadro criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quadro $quadro)
    {
        // Verifica se usuário autenticado tem acesso
        $userId = Auth::id();
        if (!$quadro->publico && $quadro->user_id !== $userId) {
            return redirect()->route('dashboard')->withErrors(['access' => 'Você não tem acesso a este quadro.']);
        }

        // carregar colunas e tarefas com anexos
        $colunas = $quadro->colunas()->with(['tarefas' => function ($query) {
            $query->with('anexos');
        }])->orderBy('ordem')->get();

        // carregar lista de usuarios para selecionar responsável
        $users = User::orderBy('name')->get();

        return view('quadro', compact('quadro', 'colunas', 'users'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quadro $quadro)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quadro $quadro)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quadro $quadro)
    {
        //
    }
}

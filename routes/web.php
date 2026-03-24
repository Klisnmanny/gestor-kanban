<?php

/**
 * GESTOR3S - Rotas da Aplicação
 * 
 * Arquivo principal de rotas da aplicação.
 * Organização: Autenticação → Dashboard → Quadros → Colunas → Tarefas → Anexos → API
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuadroController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

if (!function_exists('storeAnexoWithWebp')) {
    /**
     * Armazena o anexo convertendo imagens para WebP quando possível.
     * Retorna o caminho salvo no disco "public".
     */
    function storeAnexoWithWebp($file)
    {
        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (in_array($file->getMimeType(), $imageMimes) && function_exists('imagewebp')) {
            try {
                $image = imagecreatefromstring(file_get_contents($file->getRealPath()));

                if ($image !== false) {
                    ob_start();
                    imagewebp($image, null, 80);
                    $webpData = ob_get_clean();
                    imagedestroy($image);

                    if ($webpData !== false) {
                        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $safe = Str::slug($name, '-');
                        $filename = ($safe ?: 'anexo') . '-' . uniqid() . '.webp';
                        $path = 'anexos/' . $filename;
                        Storage::disk('public')->put($path, $webpData);
                        return $path;
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Falha ao converter WebP: ' . $e->getMessage());
            }
        }

        // Fallback: salva o arquivo original
        return $file->store('anexos', 'public');
    }
}

// ============================================================================
// ROTAS PÚBLICAS
// ============================================================================

/**
 * Página de Login/Registro
 */
Route::get('/', function () {
    return view('login');
});

// ============================================================================
// AUTENTICAÇÃO (sem middleware)
// ============================================================================

/**
 * Logout do usuário
 */
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

/**
 * Login
 */
Route::post('/login', [AuthController::class, 'login'])->name('login');

/**
 * Registro de novo usuário
 */
Route::post('/register', [AuthController::class, 'register'])->name('register');

// ============================================================================
// ROTAS AUTENTICADAS - PERFIL DO USUÁRIO
// ============================================================================

/**
 * Atualizar perfil do usuário (nome, email, senha, foto)
 * Requisição: POST
 * Resposta: JSON
 */
Route::post('/profile/update', function (Request $request) {
    try {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,gif,webp|max:5120'
        ]);

        if (!empty($data['name'])) {
            $user->name = $data['name'];
        }
        
        if (!empty($data['email'])) {
            $user->email = $data['email'];
        }

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        // Processar upload de foto
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $path = $file->store('perfis', 'public');
            $user->foto = $path;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Perfil atualizado com sucesso'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
})->name('profile.update')->middleware('auth');

// ============================================================================
// ROTAS DE QUADROS (Dashboard e Gerenciamento)
// ============================================================================

/**
 * Dashboard - Listar todos os quadros do usuário
 * Requisição: GET
 * Resposta: HTML
 */
Route::get('/dashboard', [QuadroController::class, 'index'])->name('dashboard')->middleware('auth');

/**
 * Criar novo quadro
 * Requisição: POST
 * Resposta: Redireciona para dashboard
 */
Route::post('/quadros', [QuadroController::class, 'store'])->name('quadros.store')->middleware('auth');

/**
 * Exibir quadro específico com suas colunas e tarefas
 * Requisição: GET
 * Resposta: HTML
 */
Route::get('/quadros/{quadro}', [QuadroController::class, 'show'])->name('quadros.show')->middleware('auth');

/**
 * Atualizar informações do quadro (nome, descrição, status)
 * Requisição: POST
 * Resposta: Redireciona para dashboard
 */
Route::post('/quadros/{quadro}/update', function (Request $request, $quadroId) {
    try {
        $q = App\Models\Quadro::findOrFail($quadroId);
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'publico' => 'sometimes|boolean',
            'status' => 'nullable|string|in:em_andamento,parado,arquivado'
        ]);

        $q->nome = $data['nome'];
        $q->descricao = $data['descricao'] ?? null;
        $q->publico = isset($data['publico']) ? (bool)$data['publico'] : $q->publico;
        if (!empty($data['status'])) $q->status = $data['status'];
        $q->save();

        // Processar usuários se fornecido
        $usersInput = $request->input('usuarios', []);
        if (!empty($usersInput)) {
            // Remover associações atuais e refazer
            App\Models\QuadroUsuario::where('quadro_id', $q->id)->delete();
            foreach ($usersInput as $userId => $info) {
                if (empty($info['include'])) continue;
                $userExists = App\Models\User::find((int)$userId);
                if (!$userExists) continue;
                $papel = in_array($info['papel'] ?? '', ['owner','admin','membro']) ? $info['papel'] : 'membro';
                if ((int)$userId === (int)Auth::id()) $papel = 'owner';
                App\Models\QuadroUsuario::create(['quadro_id'=>$q->id,'user_id'=>$userId,'papel'=>$papel]);
            }
            // Garantir que o usuário atual é owner
            if (!App\Models\QuadroUsuario::where('quadro_id', $q->id)->where('user_id', Auth::id())->exists()) {
                App\Models\QuadroUsuario::create(['quadro_id'=>$q->id,'user_id'=>Auth::id(),'papel'=>'owner']);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Quadro atualizado.');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['quadro_error' => 'Erro ao atualizar quadro: ' . $e->getMessage()])->withInput();
    }
})->name('quadros.update')->middleware('auth');

/**
 * Deletar quadro
 * Requisição: POST
 * Resposta: Redireciona para dashboard
 */
Route::post('/quadros/{quadro}/delete', function (Request $request, $quadroId) {
    try {
        $q = App\Models\Quadro::findOrFail($quadroId);
        $q->delete();
        return redirect()->route('dashboard')->with('success', 'Quadro excluído.');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['quadro_error' => 'Erro ao excluir quadro: ' . $e->getMessage()]);
    }
})->name('quadros.delete')->middleware('auth');

// ============================================================================
// ROTAS DE COLUNAS (Criar, Atualizar, Deletar)
// ============================================================================

/**
 * Criar nova coluna em um quadro
 * Requisição: POST
 * Resposta: Redireciona para o quadro
 */
Route::post('/colunas', function (Request $request) {
    try {
        // Remover espaços em branco do nome
        $request->merge(['nome' => trim((string)$request->input('nome'))]);

        $data = $request->validate([
            'quadro_id' => 'required|exists:quadros,id',
            'nome' => 'required|string|max:255',
        ]);

        // Calcular próxima ordem
        $maxOrdem = App\Models\Coluna::where('quadro_id', $data['quadro_id'])->max('ordem');
        $nextOrdem = is_null($maxOrdem) ? 1 : ($maxOrdem + 1);

        $coluna = App\Models\Coluna::create([
            'quadro_id' => $data['quadro_id'],
            'nome' => $data['nome'],
            'ordem' => $nextOrdem,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('quadros.show', $data['quadro_id'])->with('success', 'Coluna criada.');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['coluna_error' => 'Erro ao criar coluna: ' . $e->getMessage()])->withInput();
    }
})->name('colunas.store')->middleware('auth');

/**
 * Atualizar coluna (nome)
 * Requisição: POST
 * Resposta: Redireciona para o quadro
 */
Route::post('/colunas/{coluna}/update', function (Request $request, $coluna) {
    try {
        $col = App\Models\Coluna::findOrFail($coluna);
        $data = $request->validate([
            'nome' => 'required|string|max:255',
        ]);
        $col->nome = trim($data['nome']);
        $col->save();
        return redirect()->back()->with('success', 'Coluna atualizada.');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['coluna_error' => 'Erro ao atualizar coluna: ' . $e->getMessage()]);
    }
})->name('colunas.update')->middleware('auth');

/**
 * Deletar coluna
 * Requisição: POST
 * Resposta: Redireciona para o quadro
 */
Route::post('/colunas/{coluna}/delete', function (Request $request, $coluna) {
    try {
        $col = App\Models\Coluna::findOrFail($coluna);
        $quadroId = $col->quadro_id;
        $col->delete();
        return redirect()->route('quadros.show', $quadroId)->with('success', 'Coluna excluída.');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['coluna_error' => 'Erro ao excluir coluna: ' . $e->getMessage()]);
    }
})->name('colunas.delete')->middleware('auth');

// ============================================================================
// ROTAS DE TAREFAS (Criar, Atualizar, Deletar, Reordenar)
// ============================================================================

/**
 * Criar nova tarefa em uma coluna
 * Inclui suporte a: checklist, anexos, datas, responsável
 * Requisição: POST
 * Resposta: Redireciona para o quadro
 */
Route::post('/tarefas', function (Request $request) {
    try {
        // Garantir que 'anexos' sempre seja array para validação
        if ($request->hasFile('anexos') && !is_array($request->file('anexos'))) {
            $request->files->set('anexos', [$request->file('anexos')]);
        }

        // Remover espaços em branco do título
        $request->merge(['titulo' => trim((string)$request->input('titulo'))]);

        // Verificar se a coluna 'cor' existe no banco
        $hasCor = \Illuminate\Support\Facades\Schema::hasColumn('tarefas', 'cor');

        $rules = [
            'coluna_id' => 'required|exists:colunas,id',
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'usuario_responsavel_id' => 'nullable|exists:users,id',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date',
            'anexos' => 'nullable|array',
            'anexos.*' => 'nullable|file|mimes:pdf,png,jpg,jpeg,gif,webp,doc,docx,xls,xlsx|max:5120'
        ];
        if ($hasCor) {
            $rules['cor'] = ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'];
        }

        $data = $request->validate($rules);

        // Gerar cor aleatória se não fornecida
        $cor = null;
        if ($hasCor) {
            $cor = $data['cor'] ?? null;
            if (!$cor) {
                $palette = ['#F59E0B','#EF4444','#10B981','#3B82F6','#8B5CF6','#F97316','#06B6D4'];
                $cor = $palette[array_rand($palette)];
            }
        }

        // Validar consistência de datas
        if (!empty($data['data_inicio']) && !empty($data['data_fim'])) {
            try {
                $start = \Carbon\Carbon::parse($data['data_inicio']);
                $end = \Carbon\Carbon::parse($data['data_fim']);
                if ($start->gt($end)) {
                    return redirect()->back()->withErrors(['tarefa_error' => 'A data de início não pode ser posterior à data fim.'])->withInput();
                }
            } catch (\Exception $e) {
                // Ignorar se não conseguir fazer parse
            }
        }

        // Preparar dados da tarefa
        $tarefaData = [
            'coluna_id' => $data['coluna_id'],
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?? null,
            'ordem' => 0,
            'user_id' => Auth::id(),
        ];

        if ($hasCor) $tarefaData['cor'] = $cor;
        if (!empty($data['data_inicio'])) $tarefaData['data_inicio'] = $data['data_inicio'];
        if (!empty($data['data_fim'])) $tarefaData['data_fim'] = $data['data_fim'];
        if (!empty($data['usuario_responsavel_id'])) $tarefaData['usuario_responsavel_id'] = $data['usuario_responsavel_id'];

        // Processar checklist
        $checklistInput = $request->input('checklist_data');
        if (!empty($checklistInput)) {
            try {
                $checklist = json_decode($checklistInput, true);
                if (is_array($checklist)) {
                    $tarefaData['checklist_data'] = $checklist;
                }
            } catch (\Exception $e) {
                // Ignorar se não conseguir fazer parse
            }
        }

        // Criar tarefa
        $tarefa = App\Models\Tarefa::create($tarefaData);

        // Processar anexos se houver
        if ($request->hasFile('anexos')) {
            $files = $request->file('anexos');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    try {
                        $path = storeAnexoWithWebp($file);
                        App\Models\Anexo::create([
                            'tarefa_id' => $tarefa->id,
                            'caminho_arquivo' => $path
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Erro ao salvar anexo: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('quadros.show', App\Models\Coluna::find($data['coluna_id'])->quadro_id)->with('success', 'Tarefa criada.');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['tarefa_error' => 'Erro ao criar tarefa: ' . $e->getMessage()])->withInput();
    }
})->name('tarefas.store')->middleware('auth');

/**
 * Deletar comentário de uma tarefa
 * Rota específica deve vir ANTES da rota genérica /tarefas/{tarefa}
 * Requisição: DELETE
 * Resposta: JSON
 */
Route::delete('/tarefas/{tarefa}/comentarios/{index}', function (Request $request, $tarefa, $index) {
    try {
        \Log::info("DELETE comentário - Tarefa: $tarefa, Index: $index, User: " . Auth::id());
        
        $t = App\Models\Tarefa::findOrFail($tarefa);
        $comments = $t->comentarios_data ?? [];
        
        if (!is_array($comments)) {
            $comments = [];
        }

        \Log::info("Comments before delete: " . json_encode($comments));

        // Verificar se o comentário existe e se o usuário atual é o autor
        if (!isset($comments[$index])) {
            \Log::warning("Comentário não encontrado - Index: $index");
            return response()->json(['error' => 'Comentário não encontrado'], 404);
        }

        // Verificar permissão (apenas autor pode deletar)
        if ($comments[$index]['user_id'] != Auth::id()) {
            \Log::warning("Sem permissão - User: " . Auth::id() . ", Comment User: " . $comments[$index]['user_id']);
            return response()->json(['error' => 'Você não tem permissão para deletar este comentário'], 403);
        }

        // Remover comentário
        unset($comments[$index]);
        $comments = array_values($comments); // Reindexar array

        $t->comentarios_data = $comments;
        $t->save();

        \Log::info("Comentário deletado com sucesso. Comments after: " . json_encode($comments));

        return response()->json(['status' => 'success', 'message' => 'Comentário deletado com sucesso']);
    } catch (\Exception $e) {
        \Log::error('Erro ao deletar comentário: ' . $e->getMessage());
        return response()->json(['error' => 'Erro ao deletar comentário: ' . $e->getMessage()], 400);
    }
})->name('comentarios.delete')->middleware('auth');

/**
 * Deletar tarefa (DELETE)
 * Requisição: DELETE
 * Resposta: JSON ou Redireciona para o quadro
 */
Route::delete('/tarefas/{tarefa}', function (Request $request, $tarefa) {
    try {
        $t = App\Models\Tarefa::findOrFail($tarefa);
        $quadroId = $t->coluna ? $t->coluna->quadro_id : null;
        
        $t->delete();
        
        // Se for requisição AJAX, retorna JSON
        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Tarefa excluída.', 'quadro_id' => $quadroId]);
        }
        
        if ($quadroId) {
            return redirect()->route('quadros.show', $quadroId)->with('success', 'Tarefa excluída.');
        }
        return redirect()->route('dashboard')->with('success', 'Tarefa excluída.');
    } catch (\Exception $e) {
        \Log::error('Erro ao deletar tarefa: ' . $e->getMessage());
        if ($request->wantsJson()) {
            return response()->json(['error' => 'Erro ao excluir tarefa: ' . $e->getMessage()], 400);
        }
        return redirect()->back()->withErrors(['tarefa_error' => 'Erro ao excluir tarefa: ' . $e->getMessage()]);
    }
})->name('tarefas.destroy')->middleware('auth');

/**
 * Atualizar tarefa (PATCH - edição completa)
 * Requisição: PATCH
 * Resposta: JSON
 */
Route::patch('/tarefas/{tarefa}', function (Request $request, $tarefa) {
    try {
        $t = App\Models\Tarefa::findOrFail($tarefa);

        // Atualizar campos simples
        if ($request->filled('titulo')) {
            $t->titulo = $request->input('titulo');
        }
        if ($request->has('descricao')) {
            $t->descricao = $request->input('descricao');
        }
        if ($request->has('usuario_responsavel_id')) {
            $t->usuario_responsavel_id = $request->input('usuario_responsavel_id');
        }
        if ($request->has('data_inicio')) {
            $t->data_inicio = $request->input('data_inicio');
        }
        if ($request->has('data_fim')) {
            $t->data_fim = $request->input('data_fim');
        }

        // Atualizar checklist
        $checklistInput = $request->input('checklist_data');
        if ($checklistInput && is_string($checklistInput)) {
            try {
                $checklist = json_decode($checklistInput, true);
                if (is_array($checklist)) {
                    $t->checklist_data = $checklist;
                }
            } catch (\Exception $e) {
                // Ignorar erro de parse
            }
        }

        $t->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Tarefa atualizada com sucesso.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Erro ao atualizar tarefa: ' . $e->getMessage()
        ], 400);
    }
})->name('tarefas.update')->middleware('auth');

/**
 * Reordenar / Mover tarefas entre colunas (via AJAX)
 * Requisição: POST
 * Resposta: JSON
 * 
 * Deve vir ANTES da rota POST /tarefas/{tarefa} para evitar conflito de routing
 */
Route::post('/tarefas/reorder', function (Request $request) {
    $data = $request->validate([
        'coluna_id' => 'required|exists:colunas,id',
        'ordered' => 'required|array',
        'ordered.*' => 'integer|exists:tarefas,id'
    ]);

    $colunaId = $data['coluna_id'];
    $ordered = $data['ordered'];

    foreach ($ordered as $index => $tarefaId) {
        $tarefa = App\Models\Tarefa::find($tarefaId);
        if (!$tarefa) continue;
        $tarefa->coluna_id = $colunaId;
        $tarefa->ordem = $index + 1;
        $tarefa->save();
    }

    return response()->json(['status' => 'ok']);
})->name('tarefas.reorder')->middleware('auth');

/**
 * Atualizar tarefa (POST - checklist, comentários, status, anexos via AJAX)
 * Requisição: POST
 * Resposta: JSON ou Redireciona
 * 
 * Deve vir DEPOIS da rota POST /tarefas/reorder para evitar conflito de routing
 */
Route::post('/tarefas/{tarefa}', function (Request $request, $tarefa) {
    try {
        $t = App\Models\Tarefa::findOrFail($tarefa);

        // Atualizar status
        $status = $request->input('status');
        if (!empty($status) && in_array($status, ['pendente', 'concluida', 'em_andamento'])) {
            $t->status = $status;
        }

        // Atualizar checklist
        $checklistInput = $request->input('checklist_data');
        if (!empty($checklistInput)) {
            try {
                $checklist = json_decode($checklistInput, true);
                if (is_array($checklist)) {
                    $t->checklist_data = $checklist;
                }
            } catch (\Exception $e) {
                // Ignorar erro de parse
            }
        }

        // Adicionar novo comentário
        $newCommentText = trim((string)$request->input('new_comment', ''));
        if (!empty($newCommentText)) {
            $comments = $t->comentarios_data ?? [];
            if (!is_array($comments)) {
                $comments = [];
            }
            $comments[] = [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'text' => $newCommentText,
                'created_at' => now()->toIso8601String()
            ];
            $t->comentarios_data = $comments;
        }

        // Processar anexos se houver
        if ($request->hasFile('anexos')) {
            $files = $request->file('anexos');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    try {
                        $path = storeAnexoWithWebp($file);
                        App\Models\Anexo::create([
                            'tarefa_id' => $t->id,
                            'caminho_arquivo' => $path
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Erro ao salvar anexo: ' . $e->getMessage());
                    }
                }
            }
        }

        $t->save();

        // Se for requisição AJAX/JSON, retornar dados atualizados da tarefa
        if ($request->wantsJson()) {
            // Recarregar dados da tarefa com relacionamentos
            $t = $t->fresh();
            $t->load('responsavel', 'anexos');
            
            // Calcular cor da tarefa baseado no status e data
            if ($t->status === 'concluida') {
                $taskColor = '#1f2937';
            } elseif (!$t->data_fim) {
                $taskColor = '#92400e';
            } else {
                try {
                    $dataFim = \Carbon\Carbon::parse($t->data_fim)->startOfDay();
                    $hoje = \Carbon\Carbon::now()->startOfDay();
                    $diasRestantes = abs($dataFim->diffInDays($hoje));
                    
                    if ($dataFim < $hoje) {
                        $taskColor = '#dc2626';
                    } elseif ($diasRestantes <= 5) {
                        $taskColor = '#dc2626';
                    } elseif ($diasRestantes <= 10) {
                        $taskColor = '#ea580c';
                    } elseif ($diasRestantes <= 20) {
                        $taskColor = '#eab308';
                    } elseif ($diasRestantes <= 40) {
                        $taskColor = '#22c55e';
                    } else {
                        $taskColor = '#3b83f6';
                    }
                } catch (\Exception $e) {
                    $taskColor = '#92400e';
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tarefa atualizada com sucesso.',
                'data' => [
                    'id' => $t->id,
                    'titulo' => $t->titulo,
                    'descricao' => $t->descricao,
                    'cor' => $t->cor,
                    'status' => $t->status,
                    'data_inicio' => $t->data_inicio,
                    'data_fim' => $t->data_fim,
                    'task_color' => $taskColor,
                    'usuario_responsavel_id' => $t->usuario_responsavel_id,
                    'responsavel' => $t->responsavel ? [
                        'id' => $t->responsavel->id,
                        'name' => $t->responsavel->name
                    ] : null,
                    'etiquetas_data' => $t->etiquetas_data ?? [],
                    'checklist_data' => $t->checklist_data ?? null,
                    'membros_data' => $t->membros_data ?? [],
                    'comentarios_data' => $t->comentarios_data ?? [],
                    'anexos' => $t->anexos ? $t->anexos->map(function ($a) {
                        return [
                            'id' => $a->id,
                            'caminho_arquivo' => $a->caminho_arquivo
                        ];
                    })->toArray() : [],
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Tarefa atualizada.');
    } catch (\Exception $e) {
        if ($request->wantsJson()) {
            return response()->json(['error' => 'Erro ao atualizar tarefa: ' . $e->getMessage()], 400);
        }
        return redirect()->back()->withErrors(['tarefa_error' => 'Erro ao atualizar tarefa: ' . $e->getMessage()]);
    }
})->name('tarefas.update')->middleware('auth');

// ============================================================================
// ROTAS DE ANEXOS (Download, Deletar)
// ============================================================================

/**
 * Deletar anexo
 * Requisição: DELETE
 * Resposta: JSON
 */
Route::delete('/anexos/{anexo}', function (Request $request, $anexo) {
    try {
        $anexoObj = App\Models\Anexo::findOrFail($anexo);
        
        // Deletar arquivo do storage se existir
        if ($anexoObj->caminho_arquivo && Storage::disk('public')->exists($anexoObj->caminho_arquivo)) {
            Storage::disk('public')->delete($anexoObj->caminho_arquivo);
        }
        
        // Deletar registro do banco de dados
        $anexoObj->delete();
        
        return response()->json(['status' => 'success', 'message' => 'Anexo deletado com sucesso']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Anexo não encontrado'], 404);
    }
})->name('anexos.delete')->middleware('auth');

// ============================================================================
// ROTAS DE COMENTÁRIOS (Deletar)
// ============================================================================

// ============================================================================
// ROTAS API (Dados em JSON para AJAX)
// ============================================================================

/**
 * API: Obter dados completos da tarefa (para modal de edição)
 * Inclui: responsável, anexos, cores baseadas em datas
 * Requisição: GET
 * Resposta: JSON
 */
Route::get('/api/tarefas/{tarefa}', function (Request $request, $tarefa) {
    try {
        $t = App\Models\Tarefa::with('responsavel', 'anexos')->findOrFail($tarefa);
        
        // Calcular cor da tarefa baseado no status e data
        if ($t->status === 'concluida') {
            $taskColor = '#1f2937'; // Preto para concluído
        } elseif (!$t->data_fim) {
            $taskColor = '#92400e'; // Marrom para sem data
        } else {
            try {
                $dataFim = \Carbon\Carbon::parse($t->data_fim)->startOfDay();
                $hoje = \Carbon\Carbon::now()->startOfDay();
                $diasRestantes = abs($dataFim->diffInDays($hoje));
                
                if ($dataFim < $hoje) {
                    $taskColor = '#dc2626'; // Vermelho - Atrasado
                } elseif ($diasRestantes <= 5) {
                    $taskColor = '#dc2626'; // Vermelho - 5 dias ou menos
                } elseif ($diasRestantes <= 10) {
                    $taskColor = '#ea580c'; // Laranja - 6-10 dias
                } elseif ($diasRestantes <= 20) {
                    $taskColor = '#eab308'; // Amarelo - 11-20 dias
                } elseif ($diasRestantes <= 40) {
                    $taskColor = '#22c55e'; // Verde - 21-40 dias
                } else {
                    $taskColor = '#3b83f6'; // Azul - 41+ dias
                }
            } catch (\Exception $e) {
                $taskColor = '#92400e'; // Marrom se houver erro
            }
        }
        
        return response()->json([
            'id' => $t->id,
            'titulo' => $t->titulo,
            'descricao' => $t->descricao,
            'cor' => $t->cor,
            'status' => $t->status,
            'data_inicio' => $t->data_inicio,
            'data_fim' => $t->data_fim,
            'task_color' => $taskColor,
            'usuario_responsavel_id' => $t->usuario_responsavel_id,
            'responsavel' => $t->responsavel ? [
                'id' => $t->responsavel->id,
                'name' => $t->responsavel->name
            ] : null,
            'etiquetas_data' => $t->etiquetas_data ?? [],
            'checklist_data' => $t->checklist_data ?? null,
            'membros_data' => $t->membros_data ?? [],
            'comentarios_data' => $t->comentarios_data ?? [],
            'anexos' => $t->anexos ? $t->anexos->map(function ($a) {
                return [
                    'id' => $a->id,
                    'caminho_arquivo' => $a->caminho_arquivo
                ];
            })->toArray() : [],
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Tarefa não encontrada'], 404);
    }
})->name('api.tarefas.show')->middleware('auth');
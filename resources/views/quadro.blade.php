<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $quadro->nome }} - Quadro</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quadro.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quadro-responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        html { height: auto !important; }
        body { 
            height: auto !important; 
            min-height: 100vh !important; 
            overflow-y: auto !important;
            display: flex !important;
            flex-direction: column !important;
        }
        main { 
            height: auto !important; 
            min-height: auto !important; 
            overflow: visible !important;
            flex-grow: 1;
        }
    </style>
</head>
<body 
    data-current-user-id="{{ Auth::id() }}"
    data-user-name="{{ Auth::user()->name ?? '' }}"
    data-user-email="{{ Auth::user()->email ?? '' }}"
    data-route-profile-update="{{ route('profile.update') }}"
    data-route-tarefas-reorder="{{ route('tarefas.reorder') }}"
    data-api-tarefas-url="{{ url('/api/tarefas') }}"
>

<nav class="top-nav">
            <div class="nav-left">
                <button class="nav-btn" onclick="location.href='{{ route('dashboard') }}'" title="Voltar"><i class="fa-solid fa-arrow-left"></i></button>
                <div class="logo">
                    <i class="fa-solid fa-rocket logo-icon"></i>
                    <span class="brand">{{ $quadro->nome }}</span>
                </div>
            </div>

            <div class="nav-center">
                <div style="display:flex;flex-direction:column;align-items:center;gap:2px">
                    <span style="color:white;font-weight:600">{{ $quadro->nome }}</span>
                    <small style="color:rgba(255,255,255,0.7)">Gerencie suas colunas e tarefas</small>
                </div>
            </div>

            <div class="nav-right">
                @if(Auth::check())
                    @if(Auth::user()->foto)
                        <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="avatar" class="profile-avatar" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.15)">
                    @else
                        <div class="profile-avatar" style="width:34px;height:34px;border-radius:50%;background:#fff6;display:flex;align-items:center;justify-content:center;color:white;font-weight:700">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div>
                    @endif
                    <span style="color:white;font-weight:600">{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline">
                        @csrf
                        <button type="submit" class="nav-btn" title="Sair"><i class="fa-solid fa-sign-out-alt"></i></button>
                    </form>
                @endif
            </div>
        </nav>

    @if($errors->any())
        <div style="max-width:960px;margin:12px auto;">
            <div style="background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px">{{ $errors->first() }}</div>
        </div>
    @endif

    @if(session('success'))
        <div style="max-width:960px;margin:12px auto;">
            <div style="background:#ecfdf5;color:#065f46;padding:10px;border-radius:8px">{{ session('success') }}</div>
        </div>
    @endif

    <!-- Legenda das cores das tarefas -->
    <div class="task-legend">
        <strong style="color:#111827">Legenda:</strong>
        
        <div class="task-legend__item">
            <div class="task-legend__bar task-legend__bar--concluida"></div>
            <span style="color:#666">Concluída</span>
        </div>
        
        <div class="task-legend__item">
            <div class="task-legend__bar task-legend__bar--5-dias"></div>
            <span style="color:#666">5 dias ou menos</span>
        </div>
        
        <div class="task-legend__item">
            <div class="task-legend__bar task-legend__bar--6-10-dias"></div>
            <span style="color:#666">6-10 dias</span>
        </div>
        
        <div class="task-legend__item">
            <div class="task-legend__bar task-legend__bar--11-20-dias"></div>
            <span style="color:#666">11-20 dias</span>
        </div>
        
        <div class="task-legend__item">
            <div class="task-legend__bar task-legend__bar--21-40-dias"></div>
            <span style="color:#666">21-40 dias</span>
        </div>
        
        <div class="task-legend__item">
            <div class="task-legend__bar task-legend__bar--41-dias"></div>
            <span style="color:#666">41+ dias</span>
        </div>
        
        <div class="task-legend__item">
            <div class="task-legend__bar task-legend__bar--sem-data"></div>
            <span style="color:#666">Sem data</span>
        </div>
    </div>

    <main class="board-canvas">
    @foreach($colunas as $coluna)
        @php
            $isPrimeiraColuna = $loop->first;
        @endphp
        <section class="board-column">
            @php
                // Cor fixa: gradiente roxo (mesma do botão "Adicionar tarefa")
                $colColor = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                $textColor = '#ffffff';
            @endphp

            <div class="column-header" style="background:{{ $colColor }};color:{{ $textColor }};border-radius:6px;padding:8px;">
                <div style="display:flex;align-items:center;gap:8px;width:100%;justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:10px">
                        <h3 style="margin:0;font-size:1rem">{{ $coluna->nome }}</h3>
                    </div>
                    <div style="display:flex;gap:6px;align-items:center">
                        <button class="nav-btn edit-col-btn" data-coluna-id="{{ $coluna->id }}" data-coluna-nome="{{ htmlspecialchars($coluna->nome, ENT_QUOTES) }}" title="Editar coluna"><i class="fa-solid fa-pen-to-square"></i></button>
                        <form action="{{ route('colunas.delete', $coluna->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Confirma excluir esta coluna?')">
                            @csrf
                            <button class="nav-btn" type="submit" title="Excluir coluna"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="cards-container tasks-list" data-coluna-id="{{ $coluna->id }}">
                @forelse($coluna->tarefas as $tarefa)
                    @php
                        // Determinar cor baseado no status e data de término
                        if ($tarefa->status === 'concluida') {
                            $taskColor = '#1f2937'; // Preto para concluído
                        } elseif (!$tarefa->data_fim) {
                            $taskColor = '#92400e'; // Marrom para sem data
                        } else {
                            try {
                                $dataFim = \Carbon\Carbon::parse($tarefa->data_fim)->startOfDay();
                                $hoje = \Carbon\Carbon::now()->startOfDay();
                                $diasRestantes = abs($dataFim->diffInDays($hoje)); // Usar valor absoluto
                                
                                // Se a data foi no passado
                                if ($dataFim < $hoje) {
                                    $taskColor = '#dc2626'; // Vermelho para atrasado
                                } elseif ($diasRestantes <= 5) {
                                    $taskColor = '#dc2626'; // Vermelho para 5 dias ou menos
                                } elseif ($diasRestantes <= 10) {
                                    $taskColor = '#ea580c'; // Laranja para 6-10 dias
                                } elseif ($diasRestantes <= 20) {
                                    $taskColor = '#eab308'; // Amarelo para 11-20 dias
                                } elseif ($diasRestantes <= 40) {
                                    $taskColor = '#22c55e'; // Verde para 21-40 dias
                                } else {
                                    $taskColor = '#3b83f6'; // Azul para 41+ dias
                                }
                            } catch (\Exception $e) {
                                $taskColor = '#92400e'; // Marrom se houver erro
                            }
                        }
                    @endphp
                    <div class="card task-item" data-tarefa-id="{{ $tarefa->id }}" data-task-color="{{ $taskColor }}" data-status="{{ $tarefa->status }}" data-data-fim="{{ $tarefa->data_fim }}" style="border-left:6px solid {{ $taskColor }};">
                        <button type="button" class="edit-task-btn" data-tarefa-id="{{ $tarefa->id }}" title="Editar tarefa">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <strong style="display:block;margin-bottom:6px;font-size:0.95rem">{{ $tarefa->titulo }}</strong>
                        @if($tarefa->descricao)
                            <p style="margin:0 0 8px 0;color:#666;font-size:0.85rem;line-height:1.4">{{ \Illuminate\Support\Str::limit($tarefa->descricao, 100) }}</p>
                        @endif
                        
                        <!-- Indicadores: Anexos -->
                        @if($tarefa->anexos && $tarefa->anexos->count() > 0)
                            <div style="display:flex;align-items:center;gap:4px;margin-bottom:8px;font-size:0.75rem;color:#999">
                                <i class="fa-solid fa-paperclip" style="color:#667eea;font-size:0.85rem" title="{{ $tarefa->anexos->count() }} anexo(s)"></i>
                                <span>{{ $tarefa->anexos->count() }}</span>
                            </div>
                        @endif
                        
                        <!-- Avatares dos usuários -->
                        <div style="display:flex;align-items:center;gap:6px;margin-top:8px;flex-wrap:wrap">
                            @if($tarefa->criador)
                                <div style="display:flex;align-items:center;gap:4px;font-size:0.75rem;color:#999" title="Criador: {{ $tarefa->criador->name }}">
                                    @if($tarefa->criador->foto)
                                        <img src="{{ asset('storage/'.$tarefa->criador->foto) }}" alt="avatar" style="width:24px;height:24px;border-radius:50%;object-fit:cover;border:1px solid #e5e7eb">
                                    @else
                                        <div style="width:24px;height:24px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.7rem;color:#666">{{ strtoupper(substr($tarefa->criador->name,0,1)) }}</div>
                                    @endif
                                </div>
                            @endif
                            
                            @if($tarefa->responsavel && $tarefa->responsavel->id !== $tarefa->criador?->id)
                                <div style="display:flex;align-items:center;gap:4px;font-size:0.75rem;color:#999" title="Responsável: {{ $tarefa->responsavel->name }}">
                                    @if($tarefa->responsavel->foto)
                                        <img src="{{ asset('storage/'.$tarefa->responsavel->foto) }}" alt="avatar" style="width:24px;height:24px;border-radius:50%;object-fit:cover;border:1px solid #e5e7eb">
                                    @else
                                        <div style="width:24px;height:24px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.7rem;color:#666">{{ strtoupper(substr($tarefa->responsavel->name,0,1)) }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                @endforelse
            </div>

            @if($isPrimeiraColuna)
            <div class="add-task-area">
                <button class="open-task-modal add-task-btn" data-coluna-id="{{ $coluna->id }}">
                    <i class="fa-solid fa-plus"></i>
                    <span>Adicionar tarefa</span>
                </button>
            </div>
            @endif
        </section>
    @endforeach

    <!-- Botão criar coluna rápido -->
    <section class="board-column new-column">
        <form action="{{ route('colunas.store') }}" method="POST" style="width:100%">
            @csrf
            <input type="hidden" name="quadro_id" value="{{ $quadro->id }}">
            <input id="newColumnInput" type="text" name="nome" placeholder="Nova coluna" value="{{ old('nome') }}" required style="width:100%;padding:10px;border-radius:8px;border:1px dashed #d1d5db">
            @if($errors->has('nome'))
                <div style="color:#991b1b;margin-top:6px;font-size:13px">{{ $errors->first('nome') }}</div>
            @endif
            @if($errors->has('coluna_error'))
                <div style="color:#991b1b;margin-top:6px;font-size:13px">{{ $errors->first('coluna_error') }}</div>
            @endif
            <div style="margin-top:8px">
                <button class="btn-secondary" type="submit">Criar coluna</button>
            </div>
        </form>
    </section>

    </main>

<!-- Scripts carregados ao final -->

<!-- Modal para editar coluna -->
<div id="colEditModal" class="overlay" aria-hidden="true" style="display:none">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">Editar Coluna</div>
            <button class="close-btn" id="closeColModal">✕</button>
        </div>
        <div class="modal-body">
            <form id="colEditForm" method="POST">
                @csrf
                <div class="input-group">
                    <label>Nome</label>
                    <div class="input-wrapper"><input type="text" id="colEditName" name="nome" required></div>
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
                    <button type="button" class="btn-secondary" id="colEditCancel">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS movido para public/js/quadro.js -->

<!-- Modal para criar tarefa -->
<div id="taskModal" class="overlay" aria-hidden="true" style="display:none">
    <div class="modal" style="max-height:90vh;overflow-y:auto;width:90%;max-width:800px">
        <div class="modal-header">
            <div class="modal-title">Nova Tarefa</div>
            <button class="close-btn" id="closeTaskModal">✕</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('tarefas.store') }}" method="POST" id="taskForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="coluna_id" id="taskColunaId" value="">

                <!-- Título -->
                <div class="input-group">
                    <label for="taskTitulo">Título <span style="color:red">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" id="taskTitulo" name="titulo" required placeholder="Digite o título da tarefa" style="font-size:1.1rem;font-weight:600">
                    </div>
                </div>

                <!-- Descrição -->
                <div class="input-group">
                    <label for="taskDescricao">Descrição</label>
                    <div class="input-wrapper">
                        <textarea id="taskDescricao" name="descricao" rows="4" placeholder="Descreva a tarefa (opcional)..."></textarea>
                    </div>
                </div>

                <!-- Responsável -->
                <div class="input-group">
                    <label for="taskResp">Responsável</label>
                    <div class="input-wrapper">
                        <select id="taskResp" name="usuario_responsavel_id" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem">
                            <option value="">-- Nenhum responsável --</option>
                            @foreach($users ?? collect() as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Datas -->
                <div class="input-group" style="display:flex;gap:12px">
                    <div style="flex:1">
                        <label for="taskStart">Data de Início</label>
                        <div class="input-wrapper">
                            <input type="date" id="taskStart" name="data_inicio" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem">
                        </div>
                    </div>
                    <div style="flex:1">
                        <label for="taskEnd">Data de Fim</label>
                        <div class="input-wrapper">
                            <input type="date" id="taskEnd" name="data_fim" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem">
                        </div>
                    </div>
                </div>

                <!-- Checklist -->
                <div class="input-group">
                    <label>Checklist</label>
                    <div style="display:flex;flex-direction:column;gap:8px;padding:12px;border:1px solid #e5e7eb;border-radius:8px;background:#fafbfc">
                        <!-- Título do Checklist -->
                        <div>
                            <label for="checklistName" style="font-size:0.9rem;color:#666;display:block;margin-bottom:4px">Título do checklist</label>
                            <input type="text" id="checklistName" placeholder="Ex: Preparação, Validação..." style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem">
                        </div>

                        <!-- Items do Checklist -->
                        <div style="border-top:1px solid #e5e7eb;padding-top:8px">
                            <label style="font-size:0.9rem;color:#666;display:block;margin-bottom:8px">Itens do checklist</label>
                            <div id="checklistItems" style="display:flex;flex-direction:column;gap:6px;margin-bottom:8px">
                                <!-- Items serão adicionados aqui dinamicamente -->
                            </div>

                            <!-- Adicionar novo item -->
                            <div style="display:flex;gap:6px">
                                <input type="text" id="newChecklistItem" placeholder="Novo item..." style="flex:1;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem">
                                <button type="button" id="addChecklistItem" class="btn-secondary" style="white-space:nowrap;padding:8px 16px">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <small style="color:#666;margin-top:6px;display:block">Adicione os itens que precisam ser feitos nesta tarefa</small>
                </div>

                <!-- Anexos -->
                <div class="input-group">
                    <label for="taskAnexos">Anexos</label>
                    <div class="input-wrapper">
                        <input type="file" id="taskAnexos" name="anexos[]" multiple style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem">
                    </div>
                    <small style="color:#666;margin-top:6px;display:block">Você pode anexar múltiplos arquivos (PDF, imagens, documentos, etc)</small>
                </div>

                <!-- Botões de ação -->
                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px;border-top:1px solid #e5e7eb;padding-top:12px">
                    <button type="button" class="btn-secondary" id="cancelTask">Cancelar</button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-check"></i> Criar Tarefa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS movido para public/js/quadro.js -->

<!-- Modal para visualizar/editar tarefa -->
<div id="viewTaskModal" class="overlay" aria-hidden="true" style="display:none">
    <div class="modal" style="max-height:90vh;overflow-y:auto;width:90%;max-width:800px">
        <div class="modal-body">
            <div id="viewTaskContent">
                <!-- Conteúdo será preenchido via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar tarefa -->
<div id="editTaskModal" class="overlay" aria-hidden="true" style="display:none">
    <div class="modal" style="max-height:90vh;overflow-y:auto;width:90%;max-width:800px">
        <div class="modal-body">
            <form id="editTaskForm" style="display:flex;flex-direction:column;gap:16px">
                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Título</label>
                    <input type="text" id="editTitle" name="titulo" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Descrição</label>
                    <textarea id="editDescription" name="descricao" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit;resize:vertical;min-height:120px;max-height:240px"></textarea>
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Responsável</label>
                    <select id="editResponsavel" name="usuario_responsavel_id" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                        <option value="">Nenhum responsável</option>
                        @forelse($users as $usuario)
                            <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Data Início</label>
                        <input type="date" id="editDataInicio" name="data_inicio" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Data Fim</label>
                        <input type="date" id="editDataFim" name="data_fim" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem">
                    </div>
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:8px;color:#333">Checklist</label>
                    <div id="editChecklistSection" style="display:flex;flex-direction:column;gap:8px">
                        <div style="display:flex;gap:8px">
                            <input type="text" id="editChecklistName" placeholder="Nome do checklist" style="flex:1;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem">
                        </div>
                        <div id="editChecklistItems" style="display:flex;flex-direction:column;gap:6px;max-height:200px;overflow-y:auto">
                            <!-- Items serão adicionados aqui via JavaScript -->
                        </div>
                        <button type="button" id="editAddChecklistItem" style="padding:8px 12px;background:#f3f4f6;border:1px solid #e5e7eb;color:#374151;border-radius:6px;cursor:pointer;font-weight:600;font-size:0.9rem;align-self:flex-start"><i class="fa-solid fa-plus"></i> Adicionar item</button>
                    </div>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;padding-top:8px;border-top:1px solid #e5e7eb">
                    <button type="button" onclick="closeEditTaskModal()" class="btn-secondary" style="padding:10px 20px;background:#f3f4f6;border:1px solid #e5e7eb;color:#374151;border-radius:6px;cursor:pointer;font-weight:600">Cancelar</button>
                    <button type="submit" class="btn-primary" style="padding:10px 20px;background:#667eea;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600"><i class="fa-solid fa-save"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Perfil do Usuário -->
<div id="profileModal" class="overlay" aria-hidden="true" style="display:none">
    <div class="modal" style="max-height:90vh;overflow-y:auto;width:90%;max-width:600px">
        <div class="modal-body">
            <form id="profileForm" style="display:flex;flex-direction:column;gap:16px">
                <h2 style="margin:0 0 16px 0;font-size:1.5rem;color:#111">Meu Perfil</h2>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Nome</label>
                    <input type="text" id="profileName" name="name" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">E-mail</label>
                    <input type="email" id="profileEmail" name="email" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Senha</label>
                    <input type="password" id="profilePassword" name="password" placeholder="Deixe em branco para não alterar" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                </div>

                <div>
                    <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Confirmar Senha</label>
                    <input type="password" id="profilePasswordConfirm" name="password_confirmation" placeholder="Confirme a nova senha" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;padding-top:8px;border-top:1px solid #e5e7eb">
                    <button type="button" onclick="closeProfileModal()" class="btn-secondary" style="padding:10px 20px;background:#f3f4f6;border:1px solid #e5e7eb;color:#374151;border-radius:6px;cursor:pointer;font-weight:600">Cancelar</button>
                    <button type="submit" class="btn-primary" style="padding:10px 20px;background:#667eea;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600"><i class="fa-solid fa-save"></i> Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS movido para public/js/quadro.js -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="{{ asset('js/quadro.js') }}"></script>
    
</body>
</html>
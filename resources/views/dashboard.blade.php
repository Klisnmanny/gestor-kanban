<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestor3S - Meus Quadros</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">    
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <nav class="top-nav">
        <div class="nav-left">
            
            <div class="logo">
                <i class="fa-solid fa-rocket logo-icon"></i>
                <span class="brand">Meus Projetos</span>
            </div>
        </div>

        <div class="nav-right">

            @if(Auth::check())
                <div class="profile-wrapper" id="profileWrapper">
                    @if(Auth::user()->foto)
                        <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="avatar" class="profile-avatar" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.15)">
                    @else
                        <div class="profile-avatar" style="width:34px;height:34px;border-radius:50%;background:#fff6;display:flex;align-items:center;justify-content:center;color:white;font-weight:700">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div>
                    @endif
                    <span class="profile-name">{{ Auth::user()->name }}</span>
                    <button id="profileBtn" class="nav-btn"><i class="fa-solid fa-caret-down"></i></button>

                    <div id="profileMenu" class="profile-menu" style="display:none;">
                        <button type="button" onclick="openProfileModal(); event.stopPropagation();" style="background:none;border:none;color:#172b4d;cursor:pointer;width:100%;text-align:left;padding:12px;font-size:0.95rem;display:flex;align-items:center;gap:8px;font-weight:500;border-radius:4px;transition:background 0.2s" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='transparent'">
                            <i class="fa-solid fa-user-circle"></i>
                            Perfil
                        </button>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;padding:0;width:100%">
                            @csrf
                            <button type="submit" style="padding:12px;font-size:0.95rem;display:flex;align-items:center;gap:8px;font-weight:500;background:none;border:none;color:#172b4d;cursor:pointer;width:100%;text-align:left;border-radius:4px;transition:background 0.2s" onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='transparent'">
                                <i class="fa-solid fa-sign-out-alt"></i>
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </nav>

    <main class="board-canvas">

        <aside class="side-menu" id="sideMenu" style="width:220px;min-width:220px;padding:12px;">
            <button id="createBoardBtn" class="btn-add-card" style="width:100%;justify-content:center"><i class="fa-solid fa-plus"></i> Criar Projeto</button>
            <hr style="margin:12px 0;border:none;border-top:1px solid rgba(0,0,0,0.06)">
            <nav style="display:flex;flex-direction:column;gap:8px">
                <a href="#" style="text-decoration:none;color:#172b4d;font-weight:600">Projetos públicos</a>
            </nav>
            <hr style="margin:12px 0;border:none;border-top:1px solid rgba(0,0,0,0.06)">
            <div style="display:flex;flex-direction:column;gap:12px">
                @php
                    $quadrosPublicos = \App\Models\Quadro::where('publico', 1)->get();
                @endphp
                @foreach($quadrosPublicos as $quadro)
                    <div style="width:100%;padding:12px;background:#f9fafb;border-radius:8px;border-left:4px solid #000000ff;box-shadow:0 1px 3px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s,box-shadow 0.2s;display:flex;justify-content:space-between;align-items:center" onmouseover="this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)'" onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'" onclick="location.href='{{ route('quadros.show', $quadro->id) }}'">
                        <div style="flex:1">
                            <strong style="font-size:0.9rem;color:#172b4d;display:block;word-break:break-word">{{ \Illuminate\Support\Str::limit($quadro->nome, 20) }}</strong>
                            <small style="color:#666;font-size:0.8rem;text-transform:capitalize">{{ str_replace('_',' ', $quadro->status ?? 'em_andamento') }}</small>
                        </div>
                        <span style="{{ ($quadro->status ?? 'em_andamento') === 'em_andamento' ? 'background:#10b981' : (($quadro->status === 'parado') ? 'background:#ef4444' : (($quadro->status === 'arquivado') ? 'background:#172b4d' : 'background:#8b5cf6')) }};width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-left:8px"></span>
                    </div>
                @endforeach
            </div>
        </aside>

        <section style="flex:1;display:flex;flex-direction:column;gap:12px;">

            @if(session('success'))
                <div style="background:#ecfdf5;color:#065f46;padding:10px;border-radius:8px">{{ session('success') }}</div>
            @endif

            {{-- Coluna direita: Meus Projetos --}}
            <div style="flex:1;display:flex;flex-direction:column;gap:12px">
                <h2 style="margin:0 0 16px 0;color:#172b4d;font-size:1.2rem">Meus Projetos</h2>
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                    @if(isset($quadros) && $quadros->count())
                        @foreach($quadros as $quadro)
                            <div class="card" style="width:240px">
                                <div class="card-title">
                                        {{-- ponto de status: em_andamento (verde), parado (vermelho), arquivado (preto) --}}
                                        <span class="dot {{ ($quadro->status ?? 'em_andamento') === 'em_andamento' ? 'green' : (($quadro->status === 'parado') ? 'red' : (($quadro->status === 'arquivado') ? 'black' : 'purple')) }}"></span>
                                        <h3>{{ $quadro->nome }}</h3>
                                        <div style="margin-left:auto;display:flex;gap:6px;align-items:center">
                                            <button class="nav-btn edit-quadro-btn" data-id="{{ $quadro->id }}" data-nome="{{ htmlspecialchars($quadro->nome, ENT_QUOTES) }}" data-desc="{{ htmlspecialchars($quadro->descricao ?? '', ENT_QUOTES) }}" data-publico="{{ $quadro->publico ? '1' : '0' }}" data-status="{{ $quadro->status ?? 'em_andamento' }}" title="Editar projeto"><i class="fa-solid fa-pen-to-square"></i></button>
                                            <form action="{{ route('quadros.delete', $quadro->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Confirma excluir este projeto? Todas as colunas e tarefas serão removidas.')">
                                                @csrf
                                                <button class="nav-btn" type="submit" title="Excluir projeto"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    <div style="margin-top:6px">
                                            <small style="color:var(--text-muted);font-weight:600">Status: <span style="text-transform:capitalize">{{ str_replace('_',' ', $quadro->status ?? 'em_andamento') }}</span></small>
                                        </div>
                                <div class="card-content">
                                    <p style="color:var(--text-muted);font-size:0.85rem">{{ \Illuminate\Support\Str::limit($quadro->descricao, 120) }}</p>
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
                                        <small style="color:var(--text-muted)">Criado em {{ $quadro->created_at->format('d/m/Y') }}</small>
                                        <a href="{{ route('quadros.show', $quadro->id) }}" style="text-decoration:none">Abrir</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div style="padding:22px;background:white;border-radius:8px">Você ainda não criou nenhum quadro. Clique em "Criar Projeto" para começar.</div>
                    @endif
                </div>
            </div>

        </section>

    </main>

    <!-- Modal criar quadro (centralizado) -->
    <div id="createOverlay" class="overlay" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="createTitle">
            <div class="modal-header">
                <div class="modal-title" id="createTitle">Criar Projeto</div>
                <button class="close-btn" id="closeCreate" aria-label="Fechar">✕</button>
            </div>
            <div class="modal-body">
                <form action="{{ route('quadros.store') }}" method="POST">
                    @csrf

                    <div class="input-group">
                        <label>Nome do projeto</label>
                        <div class="input-wrapper">
                            <input type="text" name="nome" required placeholder="Nome do projeto">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Descrição</label>
                        <div class="input-wrapper">
                            <textarea name="descricao" rows="4" placeholder="Descrição breve do projeto"></textarea>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>
                            <input type="checkbox" name="publico" value="1"> Tornar público
                        </label>
                    </div>

                    <div class="input-group">
                        <label>Adicionar usuários ao projeto</label>
                        <div class="input-wrapper user-multiselect" data-users='@json(($users ?? collect())->map(function($u){ return ['id'=>$u->id,'name'=>$u->name,'email'=>$u->email]; }))' style="position:relative;padding:6px;border:1px solid #eef2ff;border-radius:6px;background:#fff">
                            <input type="text" id="userSearch" placeholder="Digite nome ou email e pressione Enter" autocomplete="off" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:8px">
                            <div class="suggestions" id="userSuggestions" style="display:none"></div>
                            <div class="selected-list" id="selectedUsers" style="margin-top:8px;display:flex;flex-direction:column;gap:6px"></div>
                        </div>
                        <small style="color:var(--text-muted)">Busque por nome ou e-mail. O criador será owner.</small>
                    </div>

                    <div style="display:flex;gap:8px;margin-top:12px;justify-content:flex-end">
                        <button class="btn-secondary" type="button" id="cancelCreate">Cancelar</button>
                        <button class="btn-primary" type="submit">Criar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Perfil do Usuário -->
    <div id="profileModal" class="overlay" aria-hidden="true" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);flex-direction:column;justify-content:center;align-items:center;z-index:1300">
        <div class="modal" style="max-height:90vh;overflow-y:auto;width:90%;max-width:600px;background:white;border-radius:8px;box-shadow:0 20px 25px rgba(0,0,0,0.15)">
            <div class="modal-body">
                <h2 style="margin:0 0 24px 0;font-size:1.5rem;color:#111">Meu Perfil</h2>
                
                <!-- Abas -->
                <div style="display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin-bottom:24px">
                    <button type="button" class="tab-btn active" data-tab="tab-foto" onclick="switchTab('tab-foto', this)" style="flex:1;padding:12px;border:none;background:none;color:#6b7280;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;transition:all 0.2s;position:relative;top:2px" onmouseover="this.style.color='#172b4d'" onmouseout="this.style.color=this.classList.contains('active')?'#172b4d':'#6b7280'">
                        <i class="fa-solid fa-image"></i> Foto
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-dados" onclick="switchTab('tab-dados', this)" style="flex:1;padding:12px;border:none;background:none;color:#6b7280;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;transition:all 0.2s;position:relative;top:2px" onmouseover="this.style.color='#172b4d'" onmouseout="this.style.color='#6b7280'">
                        <i class="fa-solid fa-user"></i> Dados Pessoais
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-senha" onclick="switchTab('tab-senha', this)" style="flex:1;padding:12px;border:none;background:none;color:#6b7280;font-weight:600;cursor:pointer;border-bottom:2px solid transparent;transition:all 0.2s;position:relative;top:2px" onmouseover="this.style.color='#172b4d'" onmouseout="this.style.color='#6b7280'">
                        <i class="fa-solid fa-lock"></i> Senha
                    </button>
                </div>

                <form id="profileForm" style="display:flex;flex-direction:column;gap:16px">
                    <!-- Aba 1: Foto -->
                    <div id="tab-foto" class="tab-content" style="display:block">
                        <div style="text-align:center;padding:20px 0">
                            <div id="fotoPreview" style="width:120px;height:120px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;margin:0 auto 20px">
                                <span style="font-size:4rem;color:#9ca3af">👤</span>
                            </div>
                            <input type="file" id="profileFoto" name="foto" accept="image/*" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit;cursor:pointer">
                            <small style="color:#6b7280;display:block;margin-top:12px">
                                <i class="fa-solid fa-info-circle"></i> Máximo 5MB<br>
                                Formatos: JPG, PNG, GIF
                            </small>
                        </div>
                    </div>

                    <!-- Aba 2: Dados Pessoais -->
                    <div id="tab-dados" class="tab-content" style="display:none">
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Nome Completo</label>
                            <input type="text" id="profileName" name="name" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                        </div>

                        <div style="margin-top:16px">
                            <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">E-mail</label>
                            <input type="email" id="profileEmail" name="email" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                        </div>
                    </div>

                    <!-- Aba 3: Senha -->
                    <div id="tab-senha" class="tab-content" style="display:none">
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Nova Senha</label>
                            <input type="password" id="profilePassword" name="password" placeholder="Digite uma nova senha (mínimo 8 caracteres)" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                        </div>

                        <div style="margin-top:16px">
                            <label style="display:block;font-weight:600;margin-bottom:6px;color:#333">Confirmar Senha</label>
                            <input type="password" id="profilePasswordConfirm" name="password_confirmation" placeholder="Confirme a nova senha" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit">
                        </div>

                        <small style="color:#6b7280;display:block;margin-top:16px;padding:10px;background:#f9fafb;border-radius:6px;border-left:3px solid #667eea">
                            <i class="fa-solid fa-lock"></i> Deixe em branco se não quiser alterar sua senha
                        </small>
                    </div>

                    <div style="display:flex;gap:8px;justify-content:flex-end;padding-top:16px;border-top:1px solid #e5e7eb;margin-top:16px">
                        <button type="button" onclick="closeProfileModal()" class="btn-secondary" style="padding:10px 20px;background:#f3f4f6;border:1px solid #e5e7eb;color:#374151;border-radius:6px;cursor:pointer;font-weight:600">Cancelar</button>
                        <button type="submit" class="btn-primary" style="padding:10px 20px;background:#667eea;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600"><i class="fa-solid fa-save"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal editar quadro -->
    <div id="editOverlay" class="overlay" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editTitle">
            <div class="modal-header">
                <div class="modal-title" id="editTitle">Editar Projeto</div>
                <button class="close-btn" id="closeEdit" aria-label="Fechar">✕</button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST">
                    @csrf
                    <div class="input-group">
                        <label>Nome do projeto</label>
                        <div class="input-wrapper">
                            <input type="text" id="editNome" name="nome" required placeholder="Nome do projeto">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Descrição</label>
                        <div class="input-wrapper">
                            <textarea id="editDesc" name="descricao" rows="4" placeholder="Descrição breve do projeto"></textarea>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>
                            <input type="hidden" name="publico" value="0">
                            <input type="checkbox" id="editPublico" name="publico" value="1"> Tornar público
                        </label>
                    </div>

                    <div class="input-group">
                        <label>Status</label>
                        <div class="input-wrapper">
                            <select id="editStatus" name="status">
                                <option value="em_andamento">Em andamento</option>
                                <option value="parado">Parado</option>
                                <option value="arquivado">Arquivado</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:flex;gap:8px;margin-top:12px;justify-content:flex-end">
                        <button class="btn-secondary" type="button" id="cancelEdit">Cancelar</button>
                        <button class="btn-primary" type="submit">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/profile-foto.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>
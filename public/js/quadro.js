// JS extraído de quadro.blade.php
(() => {
  const csrf = () => document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const bodyData = document.body.dataset || {};
  const currentUserId = Number(bodyData.currentUserId || 0);
  const userName = bodyData.userName || '';
  const userEmail = bodyData.userEmail || '';
  const routeProfileUpdate = bodyData.routeProfileUpdate || '';
  const routeTarefasReorder = bodyData.routeTarefasReorder || '';
  const apiTarefasBase = bodyData.apiTarefasUrl || '/api/tarefas';
  let isDraggingTask = false;
  let dragJustEnded = false;

  // Sortable - mover tarefas
  const initSortable = () => {
    const token = csrf();
    document.querySelectorAll('.tasks-list').forEach((listEl) => {
      new Sortable(listEl, {
        group: 'tarefas',
        animation: 150,
        fallbackOnBody: true,
        swapThreshold: 0.65,
        delay: 180,
        delayOnTouchOnly: true,
        touchStartThreshold: 6,
        onStart: () => {
          isDraggingTask = true;
        },
        onEnd: (evt) => {
          isDraggingTask = false;
          dragJustEnded = true;
          setTimeout(() => {
            dragJustEnded = false;
          }, 250);

          const target = evt.to;
          const colunaId = target.getAttribute('data-coluna-id');
          const ordered = Array.from(target.querySelectorAll('.task-item')).map((el) => parseInt(el.getAttribute('data-tarefa-id')));

          fetch(routeTarefasReorder || '/tarefas/reorder', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': token,
              Accept: 'application/json',
            },
            body: JSON.stringify({ coluna_id: colunaId, ordered }),
          })
            .then((r) => r.json())
            .then((result) => {
              if (result.status !== 'ok') {
                alert('Erro ao salvar posição das tarefas');
                location.reload();
              }
            })
            .catch(() => {
              alert('Erro ao salvar posição das tarefas. Recarregue a página.');
              location.reload();
            });
        },
      });
    });
  };

  // Foco rápido para criar coluna e dropdown do perfil
  const initQuickActions = () => {
    const addColumnBtn = document.getElementById('addColumnBtn');
    const newColumnInput = document.getElementById('newColumnInput');
    if (addColumnBtn && newColumnInput) {
      addColumnBtn.addEventListener('click', () => {
        newColumnInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
          try {
            newColumnInput.focus();
          } catch (e) {}
        }, 250);
      });
    }

    const profileWrapper = document.getElementById('profileWrapper');
    const profileBtn = document.getElementById('profileBtn');
    if (profileBtn && profileWrapper) {
      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileWrapper.classList.toggle('open');
      });

      document.addEventListener('click', (e) => {
        if (!profileWrapper.contains(e.target)) profileWrapper.classList.remove('open');
      });
    }
  };

  // Modal editar coluna
  const initColEditModal = () => {
    const editBtns = document.querySelectorAll('.edit-col-btn');
    if (!editBtns || !editBtns.length) return;

    const colEditModal = document.getElementById('colEditModal');
    const colEditForm = document.getElementById('colEditForm');
    const colEditName = document.getElementById('colEditName');
    const closeColModal = document.getElementById('closeColModal');
    const colEditCancel = document.getElementById('colEditCancel');

    editBtns.forEach((b) =>
      b.addEventListener('click', function () {
        const id = this.getAttribute('data-coluna-id');
        const nome = this.getAttribute('data-coluna-nome');
        colEditForm.action = '/colunas/' + id + '/update';
        colEditName.value = nome;
        colEditModal.classList.add('active');
        colEditModal.setAttribute('aria-hidden', 'false');
        colEditModal.style.display = 'flex';
      }),
    );

    const closeCol = () => {
      colEditModal.classList.remove('active');
      colEditModal.setAttribute('aria-hidden', 'true');
      colEditModal.style.display = 'none';
    };
    if (closeColModal) closeColModal.addEventListener('click', closeCol);
    if (colEditCancel) colEditCancel.addEventListener('click', closeCol);
    if (colEditModal) colEditModal.addEventListener('click', (e) => {
      if (e.target === colEditModal) closeCol();
    });
  };

  // Modal criar tarefa
  const initCreateTaskModal = () => {
    const taskModal = document.getElementById('taskModal');
    const closeTaskModalBtn = document.getElementById('closeTaskModal');
    const cancelTask = document.getElementById('cancelTask');
    const taskColunaInput = document.getElementById('taskColunaId');
    const taskTitulo = document.getElementById('taskTitulo');
    const checklistItems = document.getElementById('checklistItems');
    const newChecklistItem = document.getElementById('newChecklistItem');
    const addChecklistItem = document.getElementById('addChecklistItem');
    const checklistName = document.getElementById('checklistName');
    const taskForm = document.getElementById('taskForm');

    if (!taskModal || !taskForm) return;

    const addChecklistRow = (text, checked = false) => {
      const row = document.createElement('div');
      row.style.display = 'flex';
      row.style.alignItems = 'center';
      row.style.gap = '8px';
      row.style.padding = '8px';
      row.style.background = 'white';
      row.style.borderRadius = '6px';
      row.style.border = '1px solid #e5e7eb';

      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.checked = checked;
      checkbox.style.cursor = 'pointer';

      const label = document.createElement('span');
      label.textContent = text;
      label.style.flex = '1';
      label.style.fontSize = '0.95rem';
      label.style.color = '#333';

      const deleteBtn = document.createElement('button');
      deleteBtn.type = 'button';
      deleteBtn.innerHTML = '<i class="fa-solid fa-trash" style="color:#ef4444"></i>';
      deleteBtn.style.background = 'none';
      deleteBtn.style.border = 'none';
      deleteBtn.style.cursor = 'pointer';
      deleteBtn.style.padding = '4px 8px';
      deleteBtn.addEventListener('click', (e) => {
        e.preventDefault();
        row.remove();
      });

      row.appendChild(checkbox);
      row.appendChild(label);
      row.appendChild(deleteBtn);
      checklistItems.appendChild(row);
    };

    addChecklistItem?.addEventListener('click', (e) => {
      e.preventDefault();
      const itemText = newChecklistItem.value.trim();
      if (!itemText) {
        alert('Digite um item do checklist');
        return;
      }
      addChecklistRow(itemText);
      newChecklistItem.value = '';
      newChecklistItem.focus();
    });

    newChecklistItem?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        addChecklistItem.click();
      }
    });

    taskForm.addEventListener('submit', () => {
      const items = [];
      checklistItems.querySelectorAll('div').forEach((row) => {
        const checkbox = row.querySelector('input[type="checkbox"]');
        const label = row.querySelector('span');
        if (label && label.textContent) {
          items.push({
            text: label.textContent.trim(),
            checked: checkbox?.checked || false,
          });
        }
      });

      const checklistObj = {
        name: checklistName.value.trim() || null,
        items,
      };

      const oldChecklistInput = taskForm.querySelector('input[name="checklist_data"]');
      if (oldChecklistInput) oldChecklistInput.remove();

      const hiddenChecklist = document.createElement('input');
      hiddenChecklist.type = 'hidden';
      hiddenChecklist.name = 'checklist_data';
      hiddenChecklist.value = JSON.stringify(checklistObj);
      taskForm.appendChild(hiddenChecklist);
    });

    document.querySelectorAll('.open-task-modal').forEach((btn) => {
      btn.addEventListener('click', function () {
        const colunaId = this.getAttribute('data-coluna-id');
        taskColunaInput.value = colunaId;

        taskForm.reset();
        checklistItems.innerHTML = '';
        checklistName.value = '';
        newChecklistItem.value = '';

        taskModal.classList.add('active');
        taskModal.setAttribute('aria-hidden', 'false');
        taskModal.style.display = 'flex';

        setTimeout(() => {
          try {
            taskTitulo.focus();
          } catch (e) {}
        }, 100);
      });
    });

    const closeTaskModalFunc = () => {
      taskModal.classList.remove('active');
      taskModal.setAttribute('aria-hidden', 'true');
      taskModal.style.display = 'none';
    };

    closeTaskModalBtn?.addEventListener('click', closeTaskModalFunc);
    cancelTask?.addEventListener('click', closeTaskModalFunc);
    taskModal.addEventListener('click', (e) => {
      if (e.target === taskModal) closeTaskModalFunc();
    });
  };

  // Deletar tarefa (global)
  function deleteTask(tarefaId) {
    if (!confirm('Tem certeza que deseja deletar esta tarefa?')) return;

    const token = csrf();
    fetch(`/tarefas/${tarefaId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': token,
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
      .then((r) => r.text().then((text) => ({ status: r.status, data: text ? JSON.parse(text) : {} })))
      .then(({ status, data }) => {
        if ((status === 200 || status === 201) && data.status === 'success') {
          alert('Tarefa deletada com sucesso!');
          document.getElementById('viewTaskModal').style.display = 'none';
          setTimeout(() => window.location.reload(), 500);
        } else {
          const errorMsg = data.error || data.message || 'Erro desconhecido';
          alert('Erro ao deletar tarefa: ' + errorMsg);
        }
      })
      .catch((e) => {
        alert('Erro ao deletar tarefa: ' + e.message);
      });
  }

  // Modal de visualização de tarefa
  const initViewTaskModal = () => {
    const viewTaskModal = document.getElementById('viewTaskModal');
    const closeViewTaskModal = document.getElementById('closeViewTaskModal');
    const viewTaskContent = document.getElementById('viewTaskContent');

    if (!viewTaskModal || !viewTaskContent) return;

    document.addEventListener('click', (e) => {
      const taskCard = e.target.closest('.task-item');
      if (taskCard) {
        if (isDraggingTask || dragJustEnded) return;
        if (e.target.closest('.edit-task-btn')) return;
        const tarefaId = taskCard.getAttribute('data-tarefa-id');
        loadTaskDetails(tarefaId);
      }
    });

    const getTaskColor = (status, dataFim) => {
      if (status === 'concluida') return '#1f2937';
      if (!dataFim) return '#92400e';
      const hoje = new Date();
      hoje.setHours(0, 0, 0, 0);
      const [ano, mes, dia] = dataFim.split(' ')[0].split('-');
      const fim = new Date(ano, mes - 1, dia, 0, 0, 0, 0);
      const diasRestantes = Math.floor((fim - hoje) / (1000 * 60 * 60 * 24));
      if (diasRestantes < 0 || diasRestantes <= 5) return '#dc2626';
      if (diasRestantes <= 10) return '#ea580c';
      if (diasRestantes <= 20) return '#eab308';
      if (diasRestantes <= 40) return '#22c55e';
      return '#3b83f6';
    };

    function loadTaskDetails(tarefaId) {
      fetch(`${apiTarefasBase}/${tarefaId}`, {
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrf(),
        },
      })
        .then((r) => r.json())
        .then((data) => {
          const checklistProgress = data.checklist_data && data.checklist_data.items ? Math.round((data.checklist_data.items.filter((i) => i.checked).length / data.checklist_data.items.length) * 100) : 0;
          const taskBorderColor = getTaskColor(data.status, data.data_fim);
          const textColor = taskBorderColor === '#1f2937' ? '#ffffff' : '#111';

          // (HTML template preserved)
          let html = `
                    <div style="display:flex;flex-direction:column;gap:0">
                        <!-- Header com Cor e Título -->
                        <div style="background:linear-gradient(135deg, ${taskBorderColor} 0%, rgba(0,0,0,0.1) 100%);padding:24px;border-radius:8px 8px 0 0;margin:-12px -12px 0 -12px">
                            <div style="display:flex;gap:12px;align-items:flex-start">
                                <div style="width:40px;height:40px;border-radius:8px;background:${taskBorderColor};flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,0.15)"></div>
                                <div style="flex:1">
                                    <h2 style="margin:0 0 8px 0;font-size:1.5rem;color:${textColor};word-break:break-word">${data.titulo}</h2>
                                    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                                        <small style="color:${textColor === '#ffffff' ? 'rgba(255,255,255,0.8)' : '#666'}">ID #${data.id}</small>
                                        ${data.responsavel ? `<div style="display:inline-flex;align-items:center;gap:6px;background:${textColor === '#ffffff' ? 'rgba(255,255,255,0.2)' : 'white'};padding:4px 10px;border-radius:20px;font-size:0.85rem;color:${textColor}"><i class="fa-solid fa-user" style="color:${textColor};font-size:0.8rem"></i><span>${data.responsavel.name}</span></div>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="padding:0;display:flex;flex-direction:column;gap:0">
                            ${data.descricao ? `<div style="padding:16px;border-bottom:1px solid #e5e7eb"><h3 style="margin:0 0 12px 0;font-size:0.9rem;color:#666;text-transform:uppercase;font-weight:600">Descrição</h3><p style="margin:0;color:#333;line-height:1.6;white-space:pre-wrap;word-break:break-word">${data.descricao}</p></div>` : ''}

                            ${data.data_inicio || data.data_fim ? `<div style="padding:16px;border-bottom:1px solid #e5e7eb"><h3 style="margin:0 0 12px 0;font-size:0.9rem;color:#666;text-transform:uppercase;font-weight:600">Cronograma</h3><div style="display:flex;gap:24px;flex-wrap:wrap">${data.data_inicio ? `<div><small style="color:#999;display:block;margin-bottom:4px">Data de Início</small><div style="color:#333;font-weight:600">${new Date(data.data_inicio).toLocaleDateString('pt-BR')}</div></div>` : ''}${data.data_fim ? `<div><small style="color:#999;display:block;margin-bottom:4px">Data de Término</small><div style="color:#333;font-weight:600">${new Date(data.data_fim).toLocaleDateString('pt-BR')}</div></div>` : ''}</div></div>` : ''}

                            ${data.checklist_data && data.checklist_data.items && data.checklist_data.items.length > 0 ? `<div style="padding:16px;border-bottom:1px solid #e5e7eb"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><h3 style="margin:0;font-size:0.9rem;color:#666;text-transform:uppercase;font-weight:600">${data.checklist_data.name || 'Checklist'}</h3><small id="checklistProgressText" style="background:#f0fdf4;color:#166534;padding:4px 8px;border-radius:4px;font-weight:600">${checklistProgress}% concluído</small></div><div style="background:#e5e7eb;height:6px;border-radius:3px;margin-bottom:12px;overflow:hidden"><div id="checklistProgressBar" style="background:linear-gradient(90deg, #10b981, #34d399);height:100%;width:${checklistProgress}%;transition:width 0.3s ease"></div></div><div style="display:flex;flex-direction:column;gap:8px" id="checklistItemsContainer">${data.checklist_data.items.map((item, index) => `<div data-checklist-row="index_${index}" style="display:flex;align-items:center;gap:10px;padding:10px;background:#f9fafb;border-radius:6px;border-left:3px solid ${item.checked ? '#10b981' : '#d1d5db'}"><span style="width:24px;text-align:right;color:#666;font-weight:600">${index + 1}.</span><input type="checkbox" data-tarefa-id="${data.id}" data-item-index="index_${index}" ${item.checked ? 'checked' : ''} style="cursor:pointer;width:18px;height:18px;accent-color:#10b981"><span data-checklist-text="index_${index}" style="color:#333;text-decoration:${item.checked ? 'line-through' : 'none'};opacity:${item.checked ? '0.6' : '1'};flex:1">${item.text}</span>${item.checked ? `<i class="fa-solid fa-check" data-checklist-icon="index_${index}" style="color:#10b981;font-size:0.85rem"></i>` : `<i class="fa-solid fa-check" data-checklist-icon="index_${index}" style="color:#10b981;font-size:0.85rem;display:none"></i>`}</div>`).join('')}</div></div>` : ''}

                            <div style="padding:16px;border-bottom:1px solid #e5e7eb">
                                <h3 style="margin:0 0 12px 0;font-size:0.9rem;color:#666;text-transform:uppercase;font-weight:600">Anexos</h3>
                                <div style="display:flex;flex-direction:column;gap:12px">
                  <div class="attachment-row" style="display:flex;gap:8px">
                    <input type="file" id="newAnexoInput_${data.id}" multiple class="attachment-input" style="flex:1;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.9rem">
                    <button type="button" id="uploadAnexoBtn_${data.id}" data-tarefa-id="${data.id}" class="attachment-upload-btn" style="padding:8px 16px;background:#667eea;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:0.9rem"><i class="fa-solid fa-upload"></i> Anexar</button>
                  </div>
                                    <div id="anexosList_${data.id}" style="display:flex;flex-direction:column;gap:8px">
                                        ${data.anexos && data.anexos.length > 0 ? data.anexos.map((anexo) => `
                                            <div style="display:flex;align-items:center;gap:10px;padding:10px;background:#f9fafb;border-radius:6px;border-left:3px solid #667eea">
                                                <i class="fa-solid fa-file" style="color:#667eea;font-size:1rem"></i>
                                                <div style="flex:1;min-width:0">
                                                    <a href="/storage/${anexo.caminho_arquivo}" target="_blank" style="color:#667eea;text-decoration:none;word-break:break-all;font-weight:500;font-size:0.9rem">${anexo.caminho_arquivo.split('/').pop()}</a>
                                                </div>
                                                <button type="button" class="deleteAnexoBtn" data-tarefa-id="${data.id}" data-anexo-id="${anexo.id}" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem;padding:4px;transition:opacity 0.2s" title="Deletar anexo"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        `).join('') : '<div style="color:#999;font-size:0.9rem">Nenhum anexo ainda</div>'}
                                    </div>
                                </div>
                            </div>

                            <div style="padding:16px;border-bottom:1px solid #e5e7eb">
                                <h3 style="margin:0 0 12px 0;font-size:0.9rem;color:#666;text-transform:uppercase;font-weight:600">Comentários</h3>
                                <div id="commentsList" style="display:flex;flex-direction:column;gap:12px;margin-bottom:16px;max-height:300px;overflow-y:auto">
                                    ${data.comentarios_data && data.comentarios_data.length > 0 ? data.comentarios_data.map((comment, index) => {
            const dataFormatada = new Date(comment.created_at).toLocaleDateString('pt-BR');
            const horaFormatada = new Date(comment.created_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            const isAuthor = comment.user_id === currentUserId;
            return `
                                        <div style="padding:12px;background:#f9fafb;border-radius:6px;border-left:3px solid #667eea">
                                            <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;justify-content:space-between">
                                                <div style="display:flex;gap:8px;align-items:center">
                                                    <strong style="color:#333;font-size:0.9rem">${comment.user_name || 'Usuário'}</strong>
                                                    <small style="color:#999;font-size:0.8rem">${dataFormatada} ${horaFormatada}</small>
                                                </div>
                                                ${isAuthor ? `<button class="delete-comment-btn" data-tarefa-id="${data.id}" data-comment-index="${index}" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:4px;font-size:0.9rem;transition:opacity 0.2s" title="Deletar comentário"><i class="fa-solid fa-trash"></i></button>` : ''}
                                            </div>
                                            <p style="margin:0;color:#333;font-size:0.95rem;line-height:1.5">${comment.text}</p>
                                        </div>
                                    `;
          }).join('') : '<div style="color:#999;font-size:0.9rem">Nenhum comentário ainda</div>'}
                                </div>
                                <div style="display:flex;gap:8px">
                                    <textarea id="newCommentText" placeholder="Adicione um comentário..." style="flex:1;padding:10px;border:1px solid #e5e7eb;border-radius:6px;font-size:0.95rem;font-family:inherit;resize:vertical;min-height:60px;max-height:120px"></textarea>
                                </div>
                                <button type="button" id="addCommentBtn" data-tarefa-id="${data.id}" style="margin-top:8px;padding:8px 16px;background:#667eea;color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:0.9rem"><i class="fa-solid fa-paper-plane"></i> Comentar</button>
                            </div>

                            <div style="padding:16px;display:flex;gap:8px;justify-content:space-between;align-items:center;border-top:1px solid #e5e7eb">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <input type="checkbox" id="taskCompletedCheckbox" data-tarefa-id="${data.id}" ${data.status === 'concluida' ? 'checked' : ''} style="width:18px;height:18px;cursor:pointer;accent-color:#10b981">
                                    <label for="taskCompletedCheckbox" style="cursor:pointer;font-weight:600;color:#333">Marcar como concluída</label>
                                </div>
                                <div style="display:flex;gap:8px">
                                    <button type="button" class="btn-danger" onclick="deleteTask(${data.id})" style="background:#fef2f2;color:#991b1b;border:1px solid #fca5a5;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:600"><i class="fa-solid fa-trash"></i> Deletar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

          viewTaskContent.innerHTML = html;
          viewTaskModal.classList.add('active');
          viewTaskModal.setAttribute('aria-hidden', 'false');
          viewTaskModal.style.display = 'flex';

          setTimeout(() => {
            viewTaskContent.querySelectorAll('input[type="checkbox"][data-item-index]').forEach((checkbox) => {
              checkbox.addEventListener('change', function () {
                const tarefaId = this.getAttribute('data-tarefa-id');
                const itemKey = this.getAttribute('data-item-index');
                const itemIndex = parseInt(itemKey.replace('index_', ''));
                const isChecked = this.checked;
                if (data.checklist_data && data.checklist_data.items) {
                  data.checklist_data.items[itemIndex].checked = isChecked;

                  const row = viewTaskContent.querySelector(`[data-checklist-row="${itemKey}"]`);
                  const textEl = viewTaskContent.querySelector(`[data-checklist-text="${itemKey}"]`);
                  const iconEl = viewTaskContent.querySelector(`[data-checklist-icon="${itemKey}"]`);
                  const progressTextEl = viewTaskContent.querySelector('#checklistProgressText');
                  const progressBarEl = viewTaskContent.querySelector('#checklistProgressBar');
                  const items = data.checklist_data.items || [];
                  const checkedCount = items.filter((item) => item.checked).length;
                  const progress = items.length ? Math.round((checkedCount / items.length) * 100) : 0;

                  if (row) {
                    row.style.borderLeftColor = isChecked ? '#10b981' : '#d1d5db';
                  }
                  if (textEl) {
                    textEl.style.textDecoration = isChecked ? 'line-through' : 'none';
                    textEl.style.opacity = isChecked ? '0.6' : '1';
                  }
                  if (iconEl) {
                    iconEl.style.display = isChecked ? '' : 'none';
                  }
                  if (progressTextEl) {
                    progressTextEl.textContent = `${progress}% concluído`;
                  }
                  if (progressBarEl) {
                    progressBarEl.style.width = `${progress}%`;
                  }

                  fetch(`/tarefas/${tarefaId}`, {
                    method: 'POST',
                    headers: {
                      'X-CSRF-TOKEN': csrf(),
                      Accept: 'application/json',
                      'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ checklist_data: JSON.stringify(data.checklist_data) }),
                  }).catch(() => {});
                }
              });
            });
          }, 100);

          setTimeout(() => {
            const addCommentBtn = viewTaskContent.querySelector('#addCommentBtn');
            const newCommentText = viewTaskContent.querySelector('#newCommentText');
            if (addCommentBtn && newCommentText) {
              addCommentBtn.addEventListener('click', function () {
                const tarefaId = this.getAttribute('data-tarefa-id');
                const commentText = newCommentText.value.trim();
                if (!commentText) {
                  alert('Digite um comentário');
                  return;
                }
                fetch(`/tarefas/${tarefaId}`, {
                  method: 'POST',
                  headers: {
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                  },
                  body: JSON.stringify({ new_comment: commentText }),
                })
                  .then((r) => r.json())
                  .then((result) => {
                    if (result.status === 'success' && result.data) {
                      newCommentText.value = '';
                      const comentarios = result.data.comentarios_data || [];
                      const commentsList = viewTaskContent.querySelector('#commentsList');
                      if (commentsList && comentarios.length > 0) {
                        commentsList.innerHTML = '';
                        comentarios.forEach((comment, index) => {
                          const dataFormatada = new Date(comment.created_at).toLocaleDateString('pt-BR');
                          const horaFormatada = new Date(comment.created_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                          const isAuthor = comment.user_id === currentUserId;
                          const newCommentDiv = document.createElement('div');
                          newCommentDiv.style.cssText = 'padding:12px;background:#f9fafb;border-radius:6px;border-left:3px solid #667eea';
                          newCommentDiv.innerHTML = `
                                                <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;justify-content:space-between">
                                                    <div style="display:flex;gap:8px;align-items:center">
                                                        <strong style="color:#333;font-size:0.9rem">${comment.user_name || 'Usuário'}</strong>
                                                        <small style="color:#999;font-size:0.8rem">${dataFormatada} ${horaFormatada}</small>
                                                    </div>
                                                    ${isAuthor ? `<button class="delete-comment-btn" data-tarefa-id="${result.data.id}" data-comment-index="${index}" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:4px;font-size:0.9rem;transition:opacity 0.2s" title="Deletar comentário"><i class="fa-solid fa-trash"></i></button>` : ''}
                                                </div>
                                                <p style="margin:0;color:#333;font-size:0.95rem;line-height:1.5">${comment.text}</p>
                                            `;
                          commentsList.appendChild(newCommentDiv);
                        });
                      }
                    }
                  })
                  .catch(() => alert('Erro ao adicionar comentário'));
              });
              newCommentText.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && e.ctrlKey) addCommentBtn.click();
              });
            }
          }, 100);

          setTimeout(() => {
            const taskCompletedCheckbox = viewTaskContent.querySelector('#taskCompletedCheckbox');
            if (taskCompletedCheckbox) {
              taskCompletedCheckbox.addEventListener('change', function () {
                const tarefaId = this.getAttribute('data-tarefa-id');
                const isCompleted = this.checked;
                fetch(`/tarefas/${tarefaId}`, {
                  method: 'POST',
                  headers: {
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                  },
                  body: JSON.stringify({ status: isCompleted ? 'concluida' : 'pendente' }),
                }).catch(() => {
                  alert('Erro ao atualizar status');
                  this.checked = !isCompleted;
                });
              });
            }
          }, 100);

          setTimeout(() => {
            const uploadBtn = viewTaskContent.querySelector(`#uploadAnexoBtn_${data.id}`);
            const fileInput = viewTaskContent.querySelector(`#newAnexoInput_${data.id}`);
            if (uploadBtn && fileInput) {
              uploadBtn.addEventListener('click', function () {
                const files = fileInput.files;
                if (!files.length) {
                  alert('Selecione pelo menos um arquivo');
                  return;
                }
                const tarefaId = this.getAttribute('data-tarefa-id');
                const formData = new FormData();
                for (let i = 0; i < files.length; i++) formData.append('anexos[]', files[i]);
                uploadBtn.disabled = true;
                uploadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enviando...';
                fetch(`/tarefas/${tarefaId}`, {
                  method: 'POST',
                  headers: {
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                  },
                  body: formData,
                })
                  .then((r) => r.json())
                  .then(() => {
                    fileInput.value = '';
                    loadTaskDetails(tarefaId);
                  })
                  .catch(() => {
                    alert('Erro ao enviar anexos');
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fa-solid fa-upload"></i> Anexar';
                  });
              });
            }
          }, 100);

          setTimeout(() => {
            const deleteButtons = viewTaskContent.querySelectorAll('.deleteAnexoBtn');
            deleteButtons.forEach((btn) => {
              btn.addEventListener('click', function (e) {
                e.preventDefault();
                const tarefaId = this.getAttribute('data-tarefa-id');
                const anexoId = this.getAttribute('data-anexo-id');
                if (!confirm('Tem certeza que deseja deletar este anexo?')) return;
                fetch(`/anexos/${anexoId}`, {
                  method: 'DELETE',
                  headers: {
                    'X-CSRF-TOKEN': csrf(),
                    Accept: 'application/json',
                  },
                })
                  .then((r) => r.json())
                  .then(() => loadTaskDetails(tarefaId))
                  .catch(() => alert('Erro ao deletar anexo'));
              });
            });
          }, 100);
        })
        .catch((e) => {
          viewTaskContent.innerHTML = `<div style="padding:16px;background:#fef2f2;border:1px solid #fca5a5;border-radius:6px;color:#991b1b"><strong>Erro ao carregar dados da tarefa</strong><br><small>${e.message}</small></div>`;
          viewTaskModal.style.display = 'flex';
        });
    }

    const closeViewTask = () => {
      const checklistCheckbox = document.getElementById('viewTaskContent')?.querySelector('#taskCompletedCheckbox');
      if (checklistCheckbox) {
        const tarefaId = checklistCheckbox.getAttribute('data-tarefa-id');
        const taskCard = document.querySelector(`.task-item[data-tarefa-id="${tarefaId}"]`);
        fetch(`${apiTarefasBase}/${tarefaId}`, {
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
        })
          .then((r) => r.json())
          .then((data) => {
            if (taskCard) {
              const newColor = data.task_color || getTaskColor(data.status, data.data_fim);
              taskCard.style.borderLeftColor = newColor;
              taskCard.setAttribute('data-task-color', newColor);
              taskCard.setAttribute('data-status', data.status);
              taskCard.setAttribute('data-data-fim', data.data_fim);
            }
          })
          .catch(() => {});
      }
      viewTaskModal.classList.remove('active');
      viewTaskModal.setAttribute('aria-hidden', 'true');
      viewTaskModal.style.display = 'none';
    };

    closeViewTaskModal?.addEventListener('click', closeViewTask);
    viewTaskModal.addEventListener('click', (e) => {
      if (e.target === viewTaskModal) closeViewTask();
    });

    // Expor função para uso externo
    window.loadTaskDetails = loadTaskDetails;
  };

  // Modal editar tarefa
  function openEditTaskModal(tarefaId) {
    currentEditingTarefaId = tarefaId;
    const editTaskModal = document.getElementById('editTaskModal');
    fetch(`${apiTarefasBase}/${tarefaId}`, {
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
    })
      .then((r) => r.json())
      .then((data) => {
        document.getElementById('editTitle').value = data.titulo;
        document.getElementById('editDescription').value = data.descricao || '';
        document.getElementById('editResponsavel').value = data.usuario_responsavel_id || '';
        document.getElementById('editDataInicio').value = data.data_inicio || '';
        document.getElementById('editDataFim').value = data.data_fim || '';
        const checklistItemsDiv = document.getElementById('editChecklistItems');
        checklistItemsDiv.innerHTML = '';
        if (data.checklist_data && data.checklist_data.name) {
          document.getElementById('editChecklistName').value = data.checklist_data.name;
          if (data.checklist_data.items) {
            data.checklist_data.items.forEach((item, index) => {
              const itemDiv = document.createElement('div');
              itemDiv.style.cssText = 'display:flex;gap:8px;align-items:center';
              itemDiv.innerHTML = `
                            <span style="width:24px;text-align:right;color:#666;font-weight:600">${index + 1}.</span>
                            <input type="checkbox" ${item.checked ? 'checked' : ''} style="width:16px;height:16px;cursor:pointer">
                            <input type="text" value="${item.text}" style="flex:1;padding:6px;border:1px solid #e5e7eb;border-radius:4px;font-size:0.9rem">
                            <button type="button" class="remove-checklist-item" data-index="${index}" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem;padding:4px"><i class="fa-solid fa-trash"></i></button>
                        `;
              checklistItemsDiv.appendChild(itemDiv);
            });
          }
        }
        const commentsList = document.getElementById('commentsList');
        if (commentsList && data.comentarios_data) {
          commentsList.innerHTML = data.comentarios_data.length > 0
            ? data.comentarios_data
                .map((comment, index) => {
                  const dataFormatada = new Date(comment.created_at).toLocaleDateString('pt-BR');
                  const horaFormatada = new Date(comment.created_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                  const isAuthor = comment.user_id === currentUserId;
                  return `
                        <div style="padding:12px;background:#f9fafb;border-radius:6px;border-left:3px solid #667eea">
                            <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;justify-content:space-between">
                                <div style="display:flex;gap:8px;align-items:center">
                                    <strong style="color:#333;font-size:0.9rem">${comment.user_name || 'Usuário'}</strong>
                                    <small style="color:#999;font-size:0.8rem">${dataFormatada} ${horaFormatada}</small>
                                </div>
                                ${isAuthor ? `<button class="delete-comment-btn" data-tarefa-id="${data.id}" data-comment-index="${index}" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:4px;font-size:0.9rem;transition:opacity 0.2s" title="Deletar comentário"><i class="fa-solid fa-trash"></i></button>` : ''}
                            </div>
                            <p style="margin:0;color:#333;font-size:0.95rem;line-height:1.5">${comment.text}</p>
                        </div>
                    `;
                })
                .join('')
            : '<div style="color:#999;font-size:0.9rem">Nenhum comentário ainda</div>';
        }
        editTaskModal.style.display = 'flex';
      })
      .catch(() => alert('Erro ao carregar dados da tarefa'));
  }

  function closeEditTaskModal() {
    const editTaskModal = document.getElementById('editTaskModal');
    editTaskModal.style.display = 'none';
    currentEditingTarefaId = null;
  }

  const initEditTaskEvents = () => {
    document.addEventListener('click', (e) => {
      if (e.target.closest('.edit-task-btn')) {
        e.stopPropagation();
        const tarefaId = e.target.closest('.edit-task-btn').getAttribute('data-tarefa-id');
        openEditTaskModal(tarefaId);
      }
    });

    document.getElementById('editAddChecklistItem')?.addEventListener('click', (e) => {
      e.preventDefault();
      const checklistItemsDiv = document.getElementById('editChecklistItems');
      const newIndex = checklistItemsDiv.children.length;
  const itemDiv = document.createElement('div');
  itemDiv.style.cssText = 'display:flex;gap:8px;align-items:center';
  itemDiv.innerHTML = `
    <span style="width:24px;text-align:right;color:#666;font-weight:600">${newIndex + 1}.</span>
    <input type="checkbox" style="width:16px;height:16px;cursor:pointer">
    <input type="text" placeholder="Novo item..." style="flex:1;padding:6px;border:1px solid #e5e7eb;border-radius:4px;font-size:0.9rem;border-color:#667eea">
    <button type="button" class="remove-checklist-item" data-index="${newIndex}" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:1rem;padding:4px"><i class="fa-solid fa-trash"></i></button>
    `;
  checklistItemsDiv.appendChild(itemDiv);
    });

    document.addEventListener('click', (e) => {
      if (e.target.closest('.remove-checklist-item')) {
        e.preventDefault();
        e.target.closest('div').remove();
      }
    });

    document.getElementById('editTaskForm')?.addEventListener('submit', (e) => {
      e.preventDefault();
      const tarefaId = currentEditingTarefaId;
      const checklistName = document.getElementById('editChecklistName').value;
      const checklistItems = [];
      document.querySelectorAll('#editChecklistItems > div').forEach((itemDiv) => {
        const checkbox = itemDiv.querySelector('input[type="checkbox"]');
        const input = itemDiv.querySelector('input[type="text"]');
        if (input.value) {
          checklistItems.push({ text: input.value, checked: checkbox.checked });
        }
      });
      const formData = {
        titulo: document.getElementById('editTitle').value,
        descricao: document.getElementById('editDescription').value,
        usuario_responsavel_id: document.getElementById('editResponsavel').value || null,
        data_inicio: document.getElementById('editDataInicio').value || null,
        data_fim: document.getElementById('editDataFim').value || null,
        checklist_data: checklistName ? JSON.stringify({ name: checklistName, items: checklistItems }) : null,
      };
      fetch(`/tarefas/${tarefaId}`, {
        method: 'PATCH',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf(),
        },
        body: JSON.stringify(formData),
      })
        .then((r) => r.json())
        .then((result) => {
          if (result.status === 'success') {
            closeEditTaskModal();
            setTimeout(() => location.reload(), 300);
          } else {
            alert('Erro ao salvar: ' + (result.message || 'Erro desconhecido'));
          }
        })
        .catch((err) => alert('Erro ao salvar tarefa: ' + err.message));
    });

    document.getElementById('editTaskModal')?.addEventListener('click', (e) => {
      if (e.target === e.currentTarget) closeEditTaskModal();
    });

    document.addEventListener('click', (e) => {
      if (e.target.closest('.delete-comment-btn')) {
        e.stopPropagation();
        const deleteBtn = e.target.closest('.delete-comment-btn');
        const tarefaId = deleteBtn.getAttribute('data-tarefa-id');
        const commentIndex = deleteBtn.getAttribute('data-comment-index');
        if (confirm('Tem certeza que deseja deletar este comentário?')) {
          fetch(`/tarefas/${tarefaId}/comentarios/${commentIndex}`, {
            method: 'DELETE',
            headers: {
              Accept: 'application/json',
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf(),
            },
          })
            .then((r) => r.json())
            .then((result) => {
              if (result.status === 'success') {
                fetch(`${apiTarefasBase}/${tarefaId}`, {
                  headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
                })
                  .then((r) => r.json())
                  .then((data) => {
                    const commentsList = document.querySelector('#viewTaskModal #commentsList');
                    if (commentsList && data.comentarios_data) {
                      commentsList.innerHTML = data.comentarios_data.length > 0
                        ? data.comentarios_data
                            .map((comment, index) => {
                              const dataFormatada = new Date(comment.created_at).toLocaleDateString('pt-BR');
                              const horaFormatada = new Date(comment.created_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                              const isAuthor = comment.user_id === currentUserId;
                              return `
                                        <div style="padding:12px;background:#f9fafb;border-radius:6px;border-left:3px solid #667eea">
                                            <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;justify-content:space-between">
                                                <div style="display:flex;gap:8px;align-items:center">
                                                    <strong style="color:#333;font-size:0.9rem">${comment.user_name || 'Usuário'}</strong>
                                                    <small style="color:#999;font-size:0.8rem">${dataFormatada} ${horaFormatada}</small>
                                                </div>
                                                ${isAuthor ? `<button class="delete-comment-btn" data-tarefa-id="${data.id}" data-comment-index="${index}" style="background:none;border:none;color:#dc2626;cursor:pointer;padding:4px;font-size:0.9rem;transition:opacity 0.2s" title="Deletar comentário"><i class="fa-solid fa-trash"></i></button>` : ''}
                                            </div>
                                            <p style="margin:0;color:#333;font-size:0.95rem;line-height:1.5">${comment.text}</p>
                                        </div>
                                    `;
                            })
                            .join('')
                        : '<div style="color:#999;font-size:0.9rem">Nenhum comentário ainda</div>';
                    }
                  });
              } else {
                alert('Erro ao deletar comentário: ' + (result.error || 'Erro desconhecido'));
              }
            })
            .catch((err) => alert('Erro ao deletar comentário: ' + err.message));
        }
      }
    });
  };

  // Perfil do usuário
  function openProfileModal() {
    const profileModal = document.getElementById('profileModal');
    if (!profileModal) return;
    document.getElementById('profileName').value = userName;
    document.getElementById('profileEmail').value = userEmail;
    profileModal.style.display = 'flex';
    document.getElementById('profileWrapper')?.classList.remove('open');
  }

  function closeProfileModal() {
    const profileModal = document.getElementById('profileModal');
    if (profileModal) profileModal.style.display = 'none';
  }

  const initProfile = () => {
    document.getElementById('profileForm')?.addEventListener('submit', (e) => {
      e.preventDefault();
      const formData = {
        name: document.getElementById('profileName').value,
        email: document.getElementById('profileEmail').value,
        password: document.getElementById('profilePassword').value || null,
        password_confirmation: document.getElementById('profilePasswordConfirm').value || null,
      };
      if (formData.password || formData.password_confirmation) {
        if (formData.password !== formData.password_confirmation) {
          alert('As senhas não correspondem!');
          return;
        }
        if (formData.password.length < 8) {
          alert('A senha deve ter pelo menos 8 caracteres!');
          return;
        }
      }
      fetch(routeProfileUpdate || '/profile', {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf(),
        },
        body: JSON.stringify(formData),
      })
        .then((r) => r.json())
        .then((result) => {
          if (result.status === 'success') {
            alert('Perfil atualizado com sucesso!');
            closeProfileModal();
            setTimeout(() => location.reload(), 500);
          } else {
            alert('Erro ao salvar: ' + (result.message || 'Erro desconhecido'));
          }
        })
        .catch((e) => alert('Erro ao atualizar perfil: ' + e.message));
    });

    document.getElementById('profileModal')?.addEventListener('click', (e) => {
      if (e.target === e.currentTarget) closeProfileModal();
    });

    document.addEventListener('click', (e) => {
      const profileBtn = e.target.closest('#profileBtn');
      const profileWrapper = document.getElementById('profileWrapper');
      if (profileBtn && profileWrapper) {
        e.stopPropagation();
        profileWrapper.classList.toggle('open');
      } else if (!e.target.closest('.profile-wrapper')) {
        profileWrapper?.classList.remove('open');
      }
    });
  };

  // Inicialização
  document.addEventListener('DOMContentLoaded', () => {
    initSortable();
    initQuickActions();
    initColEditModal();
    initCreateTaskModal();
    initViewTaskModal();
    initEditTaskEvents();
    initProfile();
  });

  // Expor funções globais necessárias
  window.deleteTask = deleteTask;
  window.openEditTaskModal = openEditTaskModal;
  window.closeEditTaskModal = closeEditTaskModal;
  window.openProfileModal = openProfileModal;
  window.closeProfileModal = closeProfileModal;
})();

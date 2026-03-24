// ============================================
// Dashboard - Profile Modal Functions (Global)
// ============================================
function openProfileModal() {
    const profileModal = document.getElementById('profileModal');
    if (!profileModal) {
        console.error('Modal não encontrado');
        return;
    }
    profileModal.style.display = 'flex';
    const profileMenu = document.getElementById('profileMenu');
    if (profileMenu) profileMenu.style.display = 'none';
}

function closeProfileModal() {
    const profileModal = document.getElementById('profileModal');
    if (profileModal) profileModal.style.display = 'none';
}

// Aguardar o DOM estar pronto antes de executar os scripts
document.addEventListener('DOMContentLoaded', function() {

// ============================================
// Dashboard - Menu Toggle
// ============================================
(function(){
    const menuToggle = document.getElementById('menuToggle');
    const sideMenu = document.getElementById('sideMenu');
    if(menuToggle) menuToggle.addEventListener('click', () => {
        if(sideMenu.style.display === 'none') sideMenu.style.display = 'block'; else sideMenu.style.display = 'block';
    });
})();

// ============================================
// Dashboard - Profile Menu
// ============================================
(function(){
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    
    if(profileBtn && profileMenu) {
        profileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (profileMenu.style.display === 'flex' || profileMenu.style.display === 'block') {
                profileMenu.style.display = 'none';
            } else {
                profileMenu.style.display = 'block';
            }
        });
    }

    // Fechar menu ao clicar fora
    document.addEventListener('click', function(e) {
        if (profileMenu && !e.target.closest('.profile-wrapper')) {
            profileMenu.style.display = 'none';
        }
    });
})();

// Salvar dados do perfil
const profileForm = document.getElementById('profileForm');
if (profileForm) {
    profileForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = {
            name: document.getElementById('profileName').value,
            email: document.getElementById('profileEmail').value,
            password: document.getElementById('profilePassword').value || null,
            password_confirmation: document.getElementById('profilePasswordConfirm').value || null
        };

        // Validar se senhas correspondem
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

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch('/profile/update', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(formData)
        })
        .then(r => r.json())
        .then(result => {
            if (result.status === 'success') {
                alert('Perfil atualizado com sucesso!');
                closeProfileModal();
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Erro ao salvar: ' + (result.message || 'Erro desconhecido'));
            }
        })
        .catch(e => {
            console.error('Erro:', e);
            alert('Erro ao atualizar perfil: ' + e.message);
        });
    });
}

// Fechar modal de perfil ao clicar fora
const profileModalElement = document.getElementById('profileModal');
if (profileModalElement) {
    profileModalElement.addEventListener('click', function(e) {
        if (e.target === this) {
            closeProfileModal();
        }
    });
}

// ============================================
// Dashboard - Criar Projeto Modal
// ============================================
(function(){
    const createBtn = document.getElementById('createBoardBtn');
    const createOverlay = document.getElementById('createOverlay');
    const closeCreate = document.getElementById('closeCreate');
    const cancelCreate = document.getElementById('cancelCreate');
    
    if(createBtn) createBtn.addEventListener('click', () => { 
        createOverlay.classList.add('active'); 
        createOverlay.setAttribute('aria-hidden','false'); 
    });
    if(closeCreate) closeCreate.addEventListener('click', () => { 
        createOverlay.classList.remove('active'); 
        createOverlay.setAttribute('aria-hidden','true'); 
    });
    if(cancelCreate) cancelCreate.addEventListener('click', () => { 
        createOverlay.classList.remove('active'); 
        createOverlay.setAttribute('aria-hidden','true'); 
    });
    if(createOverlay) createOverlay.addEventListener('click', (e) => { 
        if(e.target === createOverlay){ 
            createOverlay.classList.remove('active'); 
            createOverlay.setAttribute('aria-hidden','true'); 
        } 
    });
})();

// ============================================
// Dashboard - Multiselect User Picker
// ============================================
(function(){
    const wrapper = document.querySelector('.user-multiselect');
    if(!wrapper) return;
    const users = JSON.parse(wrapper.getAttribute('data-users') || '[]');
    const search = document.getElementById('userSearch');
    const suggestions = document.getElementById('userSuggestions');
    const selectedList = document.getElementById('selectedUsers');
    const selected = {}; // map id -> {id,name,email,papel}

    function renderSuggestions(filter){
        const q = (filter||'').trim().toLowerCase();
        const matches = users.filter(u => {
            if(selected[u.id]) return false; // já selecionado
            return u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
        }).slice(0,50);
        if(matches.length === 0){ suggestions.style.display = 'none'; suggestions.innerHTML = ''; return; }
        suggestions.style.display = 'block';
        suggestions.innerHTML = matches.map(u => `
            <div class="item" data-id="${u.id}">
                <div style="display:flex;flex-direction:column">
                    <strong style="font-size:0.95rem">${u.name}</strong>
                    <small style="color:#64748b">${u.email}</small>
                </div>
                <div style="color:#0f172a;opacity:0.7">Adicionar</div>
            </div>
        `).join('');
    }

    function addUser(u){
        if(selected[u.id]) return;
        selected[u.id] = {...u, papel:'membro'};
        const chip = document.createElement('div'); 
        chip.className = 'user-chip';
        chip.setAttribute('data-id', u.id);
        chip.innerHTML = `
            <div class="meta">
                <strong>${u.name}</strong>
                <small>${u.email}</small>
            </div>
            <div class="controls">
                <select class="role-select" name="usuarios[${u.id}][papel]">
                    <option value="membro">Membro</option>
                    <option value="admin">Administrador</option>
                </select>
                <input type="hidden" name="usuarios[${u.id}][include]" value="1">
                <button type="button" class="remove">✕</button>
            </div>
        `;
        selectedList.appendChild(chip);

        const removeBtn = chip.querySelector('.remove');
        removeBtn.addEventListener('click', () => {
            delete selected[u.id];
            chip.remove();
            renderSuggestions(search.value);
        });

        const roleSelect = chip.querySelector('.role-select');
        roleSelect.addEventListener('change', () => {
            selected[u.id].papel = roleSelect.value;
        });

        // after adding, clear input and hide suggestions
        search.value = '';
        suggestions.style.display = 'none';
    }

    suggestions.addEventListener('click', function(e){
        const item = e.target.closest('.item');
        if(!item) return;
        const id = item.getAttribute('data-id');
        const user = users.find(u=>u.id == id);
        if(user) addUser(user);
    });

    search.addEventListener('input', function(e){ renderSuggestions(this.value); });
    search.addEventListener('keydown', function(e){
        if(e.key === 'Enter'){
            e.preventDefault();
            const first = suggestions.querySelector('.item');
            if(first){ const id = first.getAttribute('data-id'); const user = users.find(u=>u.id==id); if(user) addUser(user); }
        }
        if(e.key === 'Escape') { suggestions.style.display='none'; }
    });

    document.addEventListener('click', function(e){ 
        if(!wrapper.contains(e.target)) suggestions.style.display='none'; 
    });
})();

// Fim do DOMContentLoaded
});



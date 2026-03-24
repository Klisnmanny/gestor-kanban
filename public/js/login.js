// Toggle senha
function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}

// Modal de registro
const openBtn = document.getElementById('open-register');
const overlay = document.getElementById('registerOverlay');
const closeBtn = document.getElementById('closeRegister');

function openRegister() {
    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden', 'false');
}

function closeRegister() {
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');
}

if (openBtn) openBtn.addEventListener('click', openRegister);
if (closeBtn) closeBtn.addEventListener('click', closeRegister);
if (overlay) overlay.addEventListener('click', function(e) {
    if (e.target === overlay) closeRegister();
});

// Auto-abrir modal se houver erros de registro
if (document.body.getAttribute('data-open-register') === 'true') {
    openRegister();
}

// Preview da foto antes de enviar
const fotoInput = document.getElementById('fotoInput');
const fotoPreview = document.getElementById('fotoPreview');
const fotoNome = document.getElementById('fotoNome');

if (fotoInput) {
    fotoInput.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) {
            fotoPreview.style.display = 'none';
            fotoNome.style.display = 'none';
            fotoPreview.src = '';
            return;
        }

        const url = URL.createObjectURL(file);
        fotoPreview.src = url;
        fotoPreview.style.display = 'block';
        fotoNome.textContent = file.name;
        fotoNome.style.display = 'block';
    });
}

// ============================================
// Foto de Perfil - Preview e Upload
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    const profileFoto = document.getElementById('profileFoto');
    const fotoPreview = document.getElementById('fotoPreview');

    if (profileFoto && fotoPreview) {
        profileFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tipo de arquivo
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Formato de arquivo não permitido. Use JPG, PNG ou GIF.');
                    profileFoto.value = '';
                    fotoPreview.innerHTML = '<span style="font-size:4rem;color:#9ca3af">👤</span>';
                    return;
                }

                // Validar tamanho (5MB = 5242880 bytes)
                if (file.size > 5 * 1024 * 1024) {
                    alert('A foto deve ter no máximo 5MB!');
                    profileFoto.value = '';
                    fotoPreview.innerHTML = '<span style="font-size:4rem;color:#9ca3af">👤</span>';
                    return;
                }

                // Mostrar preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoPreview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

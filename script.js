const dragArea = document.getElementById('drag-area');
const fileInput = document.getElementById('file-input');
const previewContainer = document.getElementById('preview-container');
const uploadBtn = document.getElementById('upload-btn');
const maxFiles = 10; // Maksimum yüklenebilecek dosya sayısı

// Clipboard.js başlatma - sayfa yüklendikten sonra
document.addEventListener('DOMContentLoaded', function() {
    const clipboard = new ClipboardJS('.copy-btn');
    
    // Kopyalama başarılı olduğunda
    clipboard.on('success', function(e) {
        const btn = e.trigger;
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-primary');
        
        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        }, 2000);
        
        e.clearSelection();
    });

    // Kopyalama başarısız olduğunda
    clipboard.on('error', function(e) {
        const btn = e.trigger;
        btn.innerHTML = '<i class="fas fa-times"></i>';
        btn.classList.add('btn-danger');
        btn.classList.remove('btn-outline-primary');
        
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy"></i>';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    });
});

if (dragArea) {
    dragArea.onclick = () => fileInput.click();

    dragArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dragArea.classList.add('active');
    });

    dragArea.addEventListener('dragleave', () => {
        dragArea.classList.remove('active');
    });

    dragArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dragArea.classList.remove('active');
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', () => {
        handleFiles(fileInput.files);
    });
}

function handleFiles(files) {
    if (files.length > maxFiles) {
        alert(`En fazla ${maxFiles} resim seçebilirsiniz.`);
        return;
    }

    previewContainer.innerHTML = ''; // Önceki önizlemeleri temizle
    let validFiles = 0;

    Array.from(files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            validFiles++;
            createPreviewCard(file, index);
        }
    });

    uploadBtn.disabled = validFiles === 0;
}

function createPreviewCard(file, index) {
    const reader = new FileReader();
    const card = document.createElement('div');
    card.className = 'col-md-4 preview-card';
    card.innerHTML = `
        <div class="card">
            <div class="card-img-container">
                <img class="card-img-top preview-image" id="preview-${index}">
            </div>
            <div class="card-body">
                <p class="card-text small text-muted mb-0">${file.name}</p>
                <small class="text-muted">${formatFileSize(file.size)}</small>
            </div>
        </div>
    `;

    previewContainer.appendChild(card);

    reader.onload = (e) => {
        const img = card.querySelector(`#preview-${index}`);
        img.src = e.target.result;
        
        // Animasyon
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 50 * index);
    }
    reader.readAsDataURL(file);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
} 
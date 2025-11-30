// Enhanced JavaScript for Helport Green Theme
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize theme components
    initGreenTheme();
    
    // Form Validation for Create Ticket
    const ticketForm = document.getElementById('ticketForm');
    if (ticketForm) {
        initTicketForm(ticketForm);
    }
    
    // Admin panel features
    initAdminFeatures();
    
    // Character counter
    initCharacterCounter();
    
    // File upload handling
    initFileUpload();
});

function initGreenTheme() {
    // Add loading animation to all green buttons on click
    document.querySelectorAll('.btn-green').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.querySelector('.spinner-border')) {
                const spinner = document.createElement('span');
                spinner.className = 'spinner-border spinner-border-sm me-2';
                this.prepend(spinner);
            }
        });
    });
}

function initTicketForm(form) {
    form.addEventListener('submit', function(e) {
        const subject = document.getElementById('subject').value.trim();
        const description = document.getElementById('description').value.trim();
        const submitBtn = this.querySelector('button[type="submit"]');
        const spinner = document.getElementById('submitSpinner');
        
        // Show loading state
        if (spinner) {
            spinner.classList.remove('d-none');
        }
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        
        // Validation
        let isValid = true;
        let errorMessage = '';
        
        if (subject.length < 5) {
            isValid = false;
            errorMessage = 'Please enter a subject with at least 5 characters';
        } else if (description.length < 10) {
            isValid = false;
            errorMessage = 'Please provide a more detailed description (at least 10 characters)';
        }
        
        if (!isValid) {
            e.preventDefault();
            showGreenToast(errorMessage, 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '🚀 Submit Ticket';
            if (spinner) {
                spinner.classList.add('d-none');
            }
        }
    });
}

function initCharacterCounter() {
    const descriptionTextarea = document.getElementById('description');
    if (descriptionTextarea) {
        descriptionTextarea.addEventListener('input', function() {
            const charCount = document.getElementById('charCount');
            if (charCount) {
                const count = this.value.length;
                charCount.textContent = count;
                
                // Dynamic color based on length
                if (count < 10) {
                    charCount.className = 'text-danger fw-bold';
                } else if (count < 50) {
                    charCount.className = 'text-warning fw-bold';
                } else {
                    charCount.className = 'text-success fw-bold';
                }
            }
        });
        
        // Initial count
        descriptionTextarea.dispatchEvent(new Event('input'));
    }
}

function initFileUpload() {
    const fileInput = document.getElementById('file');
    if (fileInput) {
        // Create file upload area
        const uploadArea = document.createElement('div');
        uploadArea.className = 'file-upload-area mt-2';
        uploadArea.innerHTML = `
            <div class="text-center">
                <div class="mb-2">📁</div>
                <div class="fw-bold text-green">Click to upload or drag and drop</div>
                <div class="text-muted small">Max file size: 5MB</div>
            </div>
        `;
        
        fileInput.parentNode.insertBefore(uploadArea, fileInput.nextSibling);
        fileInput.style.display = 'none';
        
        // Click event
        uploadArea.addEventListener('click', () => fileInput.click());
        
        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            handleFileSelection(fileInput.files[0], uploadArea);
        });
        
        // File change event
        fileInput.addEventListener('change', (e) => {
            handleFileSelection(e.target.files[0], uploadArea);
        });
    }
}

function handleFileSelection(file, uploadArea) {
    if (file) {
        // Validate file
        const maxSize = 5 * 1024 * 1024;
        const allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 
            'application/pdf', 'text/plain'
        ];
        
        if (file.size > maxSize) {
            showGreenToast('File size must be less than 5MB', 'danger');
            return;
        }
        
        if (!allowedTypes.includes(file.type)) {
            showGreenToast('Please select a valid file type (JPG, PNG, GIF, PDF, TXT)', 'danger');
            return;
        }
        
        // Update upload area
        uploadArea.innerHTML = `
            <div class="text-center">
                <div class="mb-2">✅</div>
                <div class="fw-bold text-green">${file.name}</div>
                <div class="text-muted small">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
                <button type="button" class="btn btn-sm btn-outline-green mt-2" onclick="clearFileSelection(this)">Change File</button>
            </div>
        `;
        
        showGreenToast('File selected successfully!', 'success');
    }
}

function clearFileSelection(button) {
    const fileInput = document.getElementById('file');
    const uploadArea = button.closest('.file-upload-area');
    
    fileInput.value = '';
    uploadArea.innerHTML = `
        <div class="text-center">
            <div class="mb-2">📁</div>
            <div class="fw-bold text-green">Click to upload or drag and drop</div>
            <div class="text-muted small">Max file size: 5MB</div>
        </div>
    `;
}

function initAdminFeatures() {
    // Search functionality
    const searchInput = document.getElementById('searchTickets');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                    // Highlight matching text
                    highlightText(row, searchTerm);
                } else {
                    row.style.display = 'none';
                }
            });
            
            updateResultsCount(visibleCount);
        });
    }
    
    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('table tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const rowStatus = row.querySelector('.badge').textContent;
                if (status === 'all' || rowStatus === status) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            updateResultsCount(visibleCount);
        });
    }
    
    // Refresh button
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Refreshing...';
            this.disabled = true;
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        });
    }
    
    // Auto-refresh for admin panel
    if (window.location.pathname.includes('admin/index.php')) {
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                const event = new CustomEvent('adminAutoRefresh');
                document.dispatchEvent(event);
            }
        }, 30000);
        
        document.addEventListener('adminAutoRefresh', () => {
            const refreshBtn = document.getElementById('refreshBtn');
            if (refreshBtn && !refreshBtn.disabled) {
                refreshBtn.click();
            }
        });
    }
}

function highlightText(element, searchTerm) {
    if (!searchTerm) return;
    
    const walker = document.createTreeWalker(
        element,
        NodeFilter.SHOW_TEXT,
        null,
        false
    );
    
    let node;
    while (node = walker.nextNode()) {
        const text = node.nodeValue;
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        const newText = text.replace(regex, '<mark class="bg-warning">$1</mark>');
        
        if (newText !== text) {
            const span = document.createElement('span');
            span.innerHTML = newText;
            node.parentNode.replaceChild(span, node);
        }
    }
}

function updateResultsCount(count) {
    let resultsCount = document.getElementById('resultsCount');
    if (!resultsCount) {
        resultsCount = document.createElement('div');
        resultsCount.id = 'resultsCount';
        resultsCount.className = 'text-muted mt-2 small';
        const searchInput = document.getElementById('searchTickets');
        searchInput.parentNode.appendChild(resultsCount);
    }
    resultsCount.textContent = `Found ${count} tickets`;
}

// Green-themed toast notifications
function showGreenToast(message, type = 'success') {
    const typeConfig = {
        success: { icon: '✅', class: 'alert-success' },
        danger: { icon: '❌', class: 'alert-danger' },
        warning: { icon: '⚠️', class: 'alert-warning' },
        info: { icon: 'ℹ️', class: 'alert-info' }
    };
    
    const config = typeConfig[type] || typeConfig.success;
    
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('greenToastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'greenToastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast ${config.class} border-0`;
    toast.innerHTML = `
        <div class="toast-header ${config.class} text-dark border-0">
            <strong class="me-auto">${config.icon} Helport</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            ${message}
        </div>
    `;
    toastContainer.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast, { delay: 4000 });
    bsToast.show();
    
    // Remove toast from DOM after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Utility function for green pulse animation
function pulseGreen(element) {
    element.classList.add('pulse-green');
    setTimeout(() => {
        element.classList.remove('pulse-green');
    }, 2000);
}

<style>
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .custom-toast {
        min-width: 300px;
        max-width: 400px;
        padding: 14px 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideIn 0.3s ease;
        font-size: 14px;
    }

    .custom-toast.hiding {
        animation: slideOut 0.3s ease forwards;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    /* Success - Soft Green */
    .custom-toast.toast-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    /* Error - Soft Red */
    .custom-toast.toast-error {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    /* Warning - Soft Yellow */
    .custom-toast.toast-warning {
        background-color: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
    }

    /* Info - Soft Blue */
    .custom-toast.toast-info {
        background-color: #cce5ff;
        color: #004085;
        border-left: 4px solid #007bff;
    }

    .custom-toast .toast-icon {
        font-size: 18px;
        flex-shrink: 0;
    }

    .custom-toast .toast-message {
        flex: 1;
        font-weight: 500;
    }

    .custom-toast .toast-close {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        opacity: 0.6;
        padding: 0;
        line-height: 1;
        flex-shrink: 0;
    }

    .custom-toast .toast-close:hover {
        opacity: 1;
    }

    .custom-toast.toast-success .toast-close { color: #155724; }
    .custom-toast.toast-error .toast-close { color: #721c24; }
    .custom-toast.toast-warning .toast-close { color: #856404; }
    .custom-toast.toast-info .toast-close { color: #004085; }
</style>

{{-- Toast Container --}}
<div class="toast-container" id="toastContainer"></div>

<script>
    const Toast = {
        container: null,

        init() {
            this.container = document.getElementById('toastContainer');
        },

        show(type, message, duration = 3000) {
            if (!this.container) this.init();

            const icons = {
                success: 'bi-check-circle-fill',
                error: 'bi-exclamation-circle-fill',
                warning: 'bi-exclamation-triangle-fill',
                info: 'bi-info-circle-fill'
            };

            const toast = document.createElement('div');
            toast.className = `custom-toast toast-${type}`;
            toast.innerHTML = `
                <i class="bi ${icons[type]} toast-icon"></i>
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="Toast.close(this)">&times;</button>
            `;

            this.container.appendChild(toast);

            // Auto remove after duration
            setTimeout(() => {
                this.remove(toast);
            }, duration);

            return toast;
        },

        close(btn) {
            const toast = btn.closest('.custom-toast');
            this.remove(toast);
        },

        remove(toast) {
            if (!toast || toast.classList.contains('hiding')) return;
            
            toast.classList.add('hiding');
            setTimeout(() => {
                toast.remove();
            }, 300);
        },

        // Shorthand methods
        success(message, duration) {
            return this.show('success', message, duration);
        },

        error(message, duration) {
            return this.show('error', message, duration);
        },

        warning(message, duration) {
            return this.show('warning', message, duration);
        },

        info(message, duration) {
            return this.show('info', message, duration);
        }
    };

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', () => Toast.init());
</script>
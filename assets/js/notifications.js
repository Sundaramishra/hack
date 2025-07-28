// Notification System
class NotificationManager {
    constructor() {
        this.createContainer();
    }

    createContainer() {
        // Create notification container if it doesn't exist
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }
    }

    show(message, type = 'info', duration = 5000) {
        const container = document.getElementById('notification-container');
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification transform transition-all duration-300 ease-in-out translate-x-full opacity-0 max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden`;
        
        // Set colors based on type
        let iconClass, bgColor, textColor, borderColor;
        switch (type) {
            case 'success':
                iconClass = 'fas fa-check-circle';
                bgColor = 'bg-green-50 dark:bg-green-900/20';
                textColor = 'text-green-800 dark:text-green-200';
                borderColor = 'border-l-4 border-green-400';
                break;
            case 'error':
                iconClass = 'fas fa-exclamation-circle';
                bgColor = 'bg-red-50 dark:bg-red-900/20';
                textColor = 'text-red-800 dark:text-red-200';
                borderColor = 'border-l-4 border-red-400';
                break;
            case 'warning':
                iconClass = 'fas fa-exclamation-triangle';
                bgColor = 'bg-yellow-50 dark:bg-yellow-900/20';
                textColor = 'text-yellow-800 dark:text-yellow-200';
                borderColor = 'border-l-4 border-yellow-400';
                break;
            default:
                iconClass = 'fas fa-info-circle';
                bgColor = 'bg-blue-50 dark:bg-blue-900/20';
                textColor = 'text-blue-800 dark:text-blue-200';
                borderColor = 'border-l-4 border-blue-400';
        }

        notification.innerHTML = `
            <div class="p-4 ${bgColor} ${borderColor}">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="${iconClass} ${textColor} text-lg"></i>
                    </div>
                    <div class="ml-3 w-0 flex-1">
                        <p class="text-sm font-medium ${textColor}">
                            ${message}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button onclick="this.closest('.notification').remove()" 
                                class="inline-flex ${textColor} hover:${textColor.replace('800', '600').replace('200', '100')} focus:outline-none">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
            notification.classList.add('translate-x-0', 'opacity-100');
        }, 100);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.remove(notification);
            }, duration);
        }

        return notification;
    }

    remove(notification) {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 7000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = 6000) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }

    clear() {
        const container = document.getElementById('notification-container');
        if (container) {
            container.innerHTML = '';
        }
    }
}

// Create global notification instance
window.notify = new NotificationManager();

// Helper functions for easy access
window.showSuccess = (message) => notify.success(message);
window.showError = (message) => notify.error(message);
window.showWarning = (message) => notify.warning(message);
window.showInfo = (message) => notify.info(message);
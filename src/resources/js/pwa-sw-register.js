// Service Worker Registration Handler
class ServiceWorkerManager {
  constructor() {
    this.registration = null;
    this.updateCheckInterval = null;
    this.init();
  }

  async init() {
    if (!('serviceWorker' in navigator)) {
      console.log('⚠️ Service Workers not supported');
      return;
    }

    try {
      this.registration = await navigator.serviceWorker.register('/service-worker.js', {
        scope: '/',
        updateViaCache: 'none'
      });

      console.log('✅ Service Worker registered successfully');

      // Handle updates
      this.registration.addEventListener('updatefound', () => {
        this.onUpdateFound();
      });

      // Check for updates periodically
      this.startUpdateCheck();

      // Handle controller change
      navigator.serviceWorker.addEventListener('controllerchange', () => {
        console.log('✅ Service Worker controller changed - page updated');
      });

    } catch (error) {
      console.error('❌ Service Worker registration failed:', error);
    }
  }

  onUpdateFound() {
    const newWorker = this.registration.installing;

    newWorker.addEventListener('statechange', () => {
      if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
        // New service worker available - show update notification
        this.showUpdateNotification();
      }
    });
  }

  startUpdateCheck() {
    // Check for updates every hour
    this.updateCheckInterval = setInterval(async () => {
      try {
        if (this.registration) {
          await this.registration.update();
        }
      } catch (error) {
        console.error('Error checking for SW updates:', error);
      }
    }, 60 * 60 * 1000); // 1 hour
  }

  showUpdateNotification() {
    const container = document.getElementById('pwa-notifications') || this.createNotificationContainer();
    
    const notification = document.createElement('div');
    notification.className = 'pwa-notification pwa-notification-info pwa-animate-in';
    notification.innerHTML = `
      <div class="pwa-notification-content">
        <div class="pwa-notification-title">🔄 Update Tersedia</div>
        <div class="pwa-notification-message">Versi terbaru dari aplikasi telah tersedia.</div>
      </div>
      <div class="pwa-notification-actions">
        <button class="pwa-notification-action pwa-notification-action-cancel" onclick="this.parentElement.parentElement.remove()">
          Nanti
        </button>
        <button class="pwa-notification-action pwa-notification-action-accept" onclick="location.reload()">
          Update Sekarang
        </button>
      </div>
    `;

    container.appendChild(notification);

    // Auto-hide after 10 seconds
    setTimeout(() => {
      if (notification.parentNode) {
        notification.classList.add('pwa-animate-out');
        setTimeout(() => notification.remove(), 300);
      }
    }, 10000);
  }

  createNotificationContainer() {
    const container = document.createElement('div');
    container.id = 'pwa-notifications';
    container.className = 'pwa-notifications-container';
    document.body.appendChild(container);
    return container;
  }

  async unregister() {
    if (this.registration) {
      await this.registration.unregister();
      console.log('Service Worker unregistered');
    }
    if (this.updateCheckInterval) {
      clearInterval(this.updateCheckInterval);
    }
  }

  async skipWaiting() {
    if (this.registration && this.registration.waiting) {
      this.registration.waiting.postMessage({ type: 'SKIP_WAITING' });
    }
  }
}

// Initialize Service Worker Manager
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.swManager = new ServiceWorkerManager();
  });
} else {
  window.swManager = new ServiceWorkerManager();
}

export default ServiceWorkerManager;

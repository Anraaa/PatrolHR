// PWA Install Prompt Handler
class PWAInstallPrompt {
  constructor() {
    this.deferredPrompt = null;
    this.installPromptShown = false;
    this.init();
  }

  init() {
    // Listen for the beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      this.deferredPrompt = e;
      this.showInstallPrompt();
    });

    // Listen for app installed event
    window.addEventListener('appinstalled', () => {
      console.log('✅ PWA installed successfully');
      this.deferredPrompt = null;
      this.dismissInstallPrompt();
      this.showInstallationSuccessNotification();
    });

    // Check if already installed (iOS)
    if (this.isIOSPWA()) {
      console.log('✅ Running as iOS PWA');
    }

    // Monitor online/offline status
    window.addEventListener('online', () => {
      this.showOnlineNotification();
    });

    window.addEventListener('offline', () => {
      this.showOfflineNotification();
    });
  }

  showInstallPrompt() {
    if (this.installPromptShown || !this.deferredPrompt) {
      return;
    }

    // Create install prompt container
    const promptContainer = document.createElement('div');
    promptContainer.id = 'pwa-install-prompt';
    promptContainer.className = 'pwa-install-prompt pwa-animate-in';
    promptContainer.innerHTML = `
      <div class="pwa-install-content">
        <div class="pwa-install-header">
          <button class="pwa-close-btn" aria-label="Close" onclick="document.getElementById('pwa-install-prompt')?.remove()">
            ✕
          </button>
        </div>
        
        <div class="pwa-install-body">
          <div class="pwa-install-icon">📱</div>
          
          <div class="pwa-install-text">
            <h3 class="pwa-install-title">Instal Aplikasi</h3>
            <p class="pwa-install-description">
              Instal Checksheet Patrol di perangkat Anda untuk akses lebih cepat dan pengalaman offline.
            </p>
          </div>

          <div class="pwa-install-features">
            <div class="pwa-feature">
              <span class="pwa-feature-icon">⚡</span>
              <span class="pwa-feature-text">Akses Cepat</span>
            </div>
            <div class="pwa-feature">
              <span class="pwa-feature-icon">📡</span>
              <span class="pwa-feature-text">Kerja Offline</span>
            </div>
            <div class="pwa-feature">
              <span class="pwa-feature-icon">🔔</span>
              <span class="pwa-feature-text">Notifikasi</span>
            </div>
          </div>
        </div>

        <div class="pwa-install-actions">
          <button class="pwa-btn pwa-btn-secondary" onclick="document.getElementById('pwa-install-prompt')?.remove()">
            Nanti
          </button>
          <button class="pwa-btn pwa-btn-primary" id="pwa-install-btn">
            Instal Sekarang
          </button>
        </div>
      </div>
    `;

    document.body.appendChild(promptContainer);
    this.installPromptShown = true;

    // Add install button handler
    document.getElementById('pwa-install-btn').addEventListener('click', () => {
      this.installApp();
    });

    // Auto-hide after 15 seconds if not interacted
    setTimeout(() => {
      const prompt = document.getElementById('pwa-install-prompt');
      if (prompt && prompt.parentNode) {
        prompt.classList.add('pwa-animate-out');
        setTimeout(() => prompt.remove(), 300);
      }
    }, 15000);
  }

  async installApp() {
    if (!this.deferredPrompt) {
      return;
    }

    try {
      this.deferredPrompt.prompt();
      const { outcome } = await this.deferredPrompt.userChoice;
      
      if (outcome === 'accepted') {
        console.log('✅ User accepted install prompt');
      } else {
        console.log('❌ User dismissed install prompt');
      }
      
      this.deferredPrompt = null;
    } catch (err) {
      console.error('❌ Install prompt error:', err);
    }
  }

  dismissInstallPrompt() {
    const prompt = document.getElementById('pwa-install-prompt');
    if (prompt) {
      prompt.classList.add('pwa-animate-out');
      setTimeout(() => prompt.remove(), 300);
    }
    this.installPromptShown = false;
  }

  showInstallationSuccessNotification() {
    this.showNotification({
      title: '✅ Aplikasi Terinstal',
      message: 'Checksheet Patrol telah berhasil diinstal. Anda sekarang bisa mengaksesnya dari home screen!',
      type: 'success',
      duration: 5000
    });
  }

  showOnlineNotification() {
    this.showNotification({
      title: '🌐 Kembali Online',
      message: 'Koneksi internet Anda telah dipulihkan.',
      type: 'success',
      duration: 3000
    });
  }

  showOfflineNotification() {
    this.showNotification({
      title: '📡 Mode Offline',
      message: 'Anda sedang offline. Beberapa fitur mungkin tidak tersedia.',
      type: 'warning',
      duration: 3000
    });
  }

  showNotification(options = {}) {
    const {
      title = 'Notifikasi',
      message = '',
      type = 'info', // info, success, warning, error
      duration = 3000
    } = options;

    const notificationContainer = document.getElementById('pwa-notifications') || this.createNotificationContainer();
    
    const notification = document.createElement('div');
    notification.className = `pwa-notification pwa-notification-${type} pwa-animate-in`;
    notification.innerHTML = `
      <div class="pwa-notification-content">
        <div class="pwa-notification-title">${title}</div>
        <div class="pwa-notification-message">${message}</div>
      </div>
      <button class="pwa-notification-close" aria-label="Close" onclick="this.parentElement.classList.add('pwa-animate-out'); setTimeout(() => this.parentElement.remove(), 300)">
        ✕
      </button>
    `;

    notificationContainer.appendChild(notification);

    if (duration > 0) {
      setTimeout(() => {
        notification.classList.add('pwa-animate-out');
        setTimeout(() => notification.remove(), 300);
      }, duration);
    }
  }

  createNotificationContainer() {
    const container = document.createElement('div');
    container.id = 'pwa-notifications';
    container.className = 'pwa-notifications-container';
    document.body.appendChild(container);
    return container;
  }

  isIOSPWA() {
    return (
      window.navigator.standalone === true ||
      window.matchMedia('(display-mode: standalone)').matches
    );
  }
}

// Initialize PWA install handler
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.pwaInstall = new PWAInstallPrompt();
  });
} else {
  window.pwaInstall = new PWAInstallPrompt();
}

export default PWAInstallPrompt;

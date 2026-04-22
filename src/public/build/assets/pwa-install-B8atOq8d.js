class n{constructor(){this.deferredPrompt=null,this.installPromptShown=!1,this.init()}init(){window.addEventListener("beforeinstallprompt",t=>{t.preventDefault(),this.deferredPrompt=t,this.showInstallPrompt()}),window.addEventListener("appinstalled",()=>{console.log("✅ PWA installed successfully"),this.deferredPrompt=null,this.dismissInstallPrompt(),this.showInstallationSuccessNotification()}),this.isIOSPWA()&&console.log("✅ Running as iOS PWA"),window.addEventListener("online",()=>{this.showOnlineNotification()}),window.addEventListener("offline",()=>{this.showOfflineNotification()})}showInstallPrompt(){if(this.installPromptShown||!this.deferredPrompt)return;const t=document.createElement("div");t.id="pwa-install-prompt",t.className="pwa-install-prompt pwa-animate-in",t.innerHTML=`
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
    `,document.body.appendChild(t),this.installPromptShown=!0,document.getElementById("pwa-install-btn").addEventListener("click",()=>{this.installApp()}),setTimeout(()=>{const a=document.getElementById("pwa-install-prompt");a&&a.parentNode&&(a.classList.add("pwa-animate-out"),setTimeout(()=>a.remove(),300))},15e3)}async installApp(){if(this.deferredPrompt)try{this.deferredPrompt.prompt();const{outcome:t}=await this.deferredPrompt.userChoice;console.log(t==="accepted"?"✅ User accepted install prompt":"❌ User dismissed install prompt"),this.deferredPrompt=null}catch(t){console.error("❌ Install prompt error:",t)}}dismissInstallPrompt(){const t=document.getElementById("pwa-install-prompt");t&&(t.classList.add("pwa-animate-out"),setTimeout(()=>t.remove(),300)),this.installPromptShown=!1}showInstallationSuccessNotification(){this.showNotification({title:"✅ Aplikasi Terinstal",message:"Checksheet Patrol telah berhasil diinstal. Anda sekarang bisa mengaksesnya dari home screen!",type:"success",duration:5e3})}showOnlineNotification(){this.showNotification({title:"🌐 Kembali Online",message:"Koneksi internet Anda telah dipulihkan.",type:"success",duration:3e3})}showOfflineNotification(){this.showNotification({title:"📡 Mode Offline",message:"Anda sedang offline. Beberapa fitur mungkin tidak tersedia.",type:"warning",duration:3e3})}showNotification(t={}){const{title:a="Notifikasi",message:s="",type:o="info",duration:i=3e3}=t,l=document.getElementById("pwa-notifications")||this.createNotificationContainer(),e=document.createElement("div");e.className=`pwa-notification pwa-notification-${o} pwa-animate-in`,e.innerHTML=`
      <div class="pwa-notification-content">
        <div class="pwa-notification-title">${a}</div>
        <div class="pwa-notification-message">${s}</div>
      </div>
      <button class="pwa-notification-close" aria-label="Close" onclick="this.parentElement.classList.add('pwa-animate-out'); setTimeout(() => this.parentElement.remove(), 300)">
        ✕
      </button>
    `,l.appendChild(e),i>0&&setTimeout(()=>{e.classList.add("pwa-animate-out"),setTimeout(()=>e.remove(),300)},i)}createNotificationContainer(){const t=document.createElement("div");return t.id="pwa-notifications",t.className="pwa-notifications-container",document.body.appendChild(t),t}isIOSPWA(){return window.navigator.standalone===!0||window.matchMedia("(display-mode: standalone)").matches}}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>{window.pwaInstall=new n}):window.pwaInstall=new n;

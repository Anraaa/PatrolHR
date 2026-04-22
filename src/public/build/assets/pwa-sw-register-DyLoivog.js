class i{constructor(){this.registration=null,this.updateCheckInterval=null,this.init()}async init(){if(!("serviceWorker"in navigator)){console.log("⚠️ Service Workers not supported");return}try{this.registration=await navigator.serviceWorker.register("/service-worker.js",{scope:"/",updateViaCache:"none"}),console.log("✅ Service Worker registered successfully"),this.registration.addEventListener("updatefound",()=>{this.onUpdateFound()}),this.startUpdateCheck(),navigator.serviceWorker.addEventListener("controllerchange",()=>{console.log("✅ Service Worker controller changed - page updated")})}catch(t){console.error("❌ Service Worker registration failed:",t)}}onUpdateFound(){const t=this.registration.installing;t.addEventListener("statechange",()=>{t.state==="installed"&&navigator.serviceWorker.controller&&this.showUpdateNotification()})}startUpdateCheck(){this.updateCheckInterval=setInterval(async()=>{try{this.registration&&await this.registration.update()}catch(t){console.error("Error checking for SW updates:",t)}},3600*1e3)}showUpdateNotification(){const t=document.getElementById("pwa-notifications")||this.createNotificationContainer(),e=document.createElement("div");e.className="pwa-notification pwa-notification-info pwa-animate-in",e.innerHTML=`
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
    `,t.appendChild(e),setTimeout(()=>{e.parentNode&&(e.classList.add("pwa-animate-out"),setTimeout(()=>e.remove(),300))},1e4)}createNotificationContainer(){const t=document.createElement("div");return t.id="pwa-notifications",t.className="pwa-notifications-container",document.body.appendChild(t),t}async unregister(){this.registration&&(await this.registration.unregister(),console.log("Service Worker unregistered")),this.updateCheckInterval&&clearInterval(this.updateCheckInterval)}async skipWaiting(){this.registration&&this.registration.waiting&&this.registration.waiting.postMessage({type:"SKIP_WAITING"})}}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",()=>{window.swManager=new i}):window.swManager=new i;

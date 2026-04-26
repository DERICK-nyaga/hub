<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>LaunchFlow | Modern Web Starter Kit</title>
  <!-- Preconnect for fast font loading -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, sans-serif;
      background: #fefefe;
      color: #121212;
      line-height: 1.5;
      scroll-behavior: smooth;
    }

    /* dark mode preference */
    @media (prefers-color-scheme: dark) {
      body {
        background: #0c0c0c;
        color: #ededec;
      }
      .card-gradient {
        background: rgba(22, 22, 21, 0.96);
      }
      .nav-link {
        color: #e0e0e0;
      }
      .border-subtle {
        border-color: rgba(255, 255, 255, 0.1);
      }
      .text-muted {
        color: #a1a09a;
      }
      .hero-badge {
        background: #1f1f1f;
        border-color: #2c2c2c;
      }
      .footer {
        border-top-color: #222;
      }
    }

    .container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* navigation */
    .navbar {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      gap: 24px;
      padding: 20px 0 16px;
    }

    .nav-link {
      font-size: 0.9rem;
      font-weight: 500;
      text-decoration: none;
      color: #2c2c2c;
      transition: color 0.2s;
      padding: 6px 0;
      border-bottom: 1px solid transparent;
    }

    .nav-link:hover {
      color: #e54c2a;
    }

    .btn-outline {
      border: 1px solid #e2e2e0;
      padding: 8px 20px;
      border-radius: 40px;
      background: transparent;
      font-weight: 500;
      transition: all 0.2s;
    }

    .btn-outline:hover {
      border-color: #cac8c4;
      background: rgba(0,0,0,0.02);
    }

    .btn-primary {
      background: #1b1b18;
      color: white;
      border: none;
      padding: 10px 28px;
      border-radius: 40px;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      transition: 0.2s;
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-primary:hover {
      background: #000;
      transform: translateY(-1px);
    }

    @media (prefers-color-scheme: dark) {
      .btn-primary {
        background: #ffffff;
        color: #121212;
      }
      .btn-primary:hover {
        background: #e4e4e4;
      }
      .btn-outline {
        border-color: #3a3a38;
        color: #ddd;
      }
    }

    /* hero split layout */
    .hero-grid {
      display: flex;
      flex-direction: column;
      gap: 32px;
      margin: 32px 0 64px;
    }

    @media (min-width: 960px) {
      .hero-grid {
        flex-direction: row;
        align-items: stretch;
        gap: 0;
        margin: 48px 0 80px;
      }
    }

    /* info panel */
    .info-panel {
      flex: 1;
      background: white;
      border-radius: 28px;
      box-shadow: 0 20px 35px -12px rgba(0,0,0,0.05), inset 0 1px 0 rgba(255,255,255,0.6);
      padding: 2rem 1.8rem;
      border: 1px solid #efefec;
      backdrop-filter: blur(2px);
    }

    @media (min-width: 960px) {
      .info-panel {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-right: none;
        padding: 2.5rem 2.2rem;
      }
    }

    @media (prefers-color-scheme: dark) {
      .info-panel {
        background: #161615;
        border-color: #2a2a28;
        box-shadow: 0 20px 35px -12px rgba(0,0,0,0.4);
      }
    }

    /* visual panel (illustration side) */
    .visual-panel {
      flex: 1;
      background: #fff4f0;
      border-radius: 28px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
      border: 1px solid #f0e5df;
      transition: all 0.2s;
    }

    @media (min-width: 960px) {
      .visual-panel {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: none;
      }
    }

    @media (prefers-color-scheme: dark) {
      .visual-panel {
        background: #1d0c0a;
        border-color: #2e1f1c;
      }
    }

    .brand-svg {
      width: 85%;
      max-width: 380px;
      margin: 2rem;
    }

    .checklist {
      list-style: none;
      margin: 28px 0 24px;
    }

    .checklist li {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 20px;
      font-size: 1rem;
      font-weight: 450;
    }

    .bullet {
      width: 26px;
      height: 26px;
      background: #f5f3ef;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: inset 0 0 0 1px #dddcd8, 0 2px 4px rgba(0,0,0,0.02);
    }

    .bullet-inner {
      width: 10px;
      height: 10px;
      background: #bcb9b0;
      border-radius: 50%;
    }

    @media (prefers-color-scheme: dark) {
      .bullet {
        background: #2a2927;
        box-shadow: inset 0 0 0 1px #3e3e3a;
      }
      .bullet-inner {
        background: #6c6a64;
      }
    }

    .link-arrow {
      color: #e54c2a;
      text-decoration: none;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border-bottom: 1px solid transparent;
      transition: 0.2s;
    }

    .link-arrow:hover {
      border-bottom-color: #e54c2a;
      gap: 10px;
    }

    .badge-panel {
      font-size: 0.75rem;
      background: #f0efea;
      padding: 4px 12px;
      border-radius: 40px;
      display: inline-block;
      margin-bottom: 20px;
      font-weight: 500;
    }

    h1 {
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: -0.02em;
      margin-bottom: 12px;
    }

    .lead-text {
      color: #5f5f5c;
      margin-bottom: 24px;
    }

    .stats {
      display: flex;
      gap: 28px;
      margin-top: 28px;
      padding-top: 18px;
      border-top: 1px solid #eae8e3;
    }

    .stat-number {
      font-weight: 800;
      font-size: 1.55rem;
    }

    /* footer */
    .footer-links {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      padding: 32px 0 48px;
      border-top: 1px solid #e2e0db;
      margin-top: 32px;
      font-size: 0.85rem;
    }

    /* animations */
    @keyframes fadeSlideUp {
      0% { opacity: 0; transform: translateY(18px); }
      100% { opacity: 1; transform: translateY(0); }
    }

    .animate-in {
      animation: fadeSlideUp 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
    }

    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }

    .hover-lift {
      transition: transform 0.2s ease, box-shadow 0.2s;
    }
    .hover-lift:hover {
      transform: translateY(-3px);
    }

    @media (max-width: 640px) {
      .container {
        padding: 0 20px;
      }
      .info-panel {
        padding: 1.5rem;
      }
      h1 {
        font-size: 1.8rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Header / Navigation -->
    <header>
      <nav class="navbar">
        <div style="flex:1"></div>
        <a href="#" class="nav-link">Docs</a>
        <a href="#" class="nav-link">Community</a>
        <a href="#" class="nav-link btn-outline">Log in</a>
        <a href="#" class="nav-link btn-primary" style="background:#1b1b18; color:white; border:none;">Get Started</a>
      </nav>
    </header>

    <!-- Hero split layout -->
    <div class="hero-grid">
      <!-- Left content block -->
      <div class="info-panel animate-in">
        <div class="badge-panel">✨ v4.0 — stable release</div>
        <h1>Build faster<br>with modern tools</h1>
        <p class="lead-text">A fresh, developer‑first experience. No boilerplate fatigue, just smart defaults and total control.</p>
        
        <ul class="checklist">
          <li class="animate-in delay-1">
            <span class="bullet"><span class="bullet-inner"></span></span>
            <span><strong class="font-semibold">Smart routing & middleware</strong> – Intuitive and powerful.</span>
          </li>
          <li class="animate-in delay-2">
            <span class="bullet"><span class="bullet-inner"></span></span>
            <span><strong class="font-semibold">Real‑time ecosystem</strong> – WebSocket, queues & caching.</span>
          </li>
          <li class="animate-in delay-3">
            <span class="bullet"><span class="bullet-inner"></span></span>
            <span><strong class="font-semibold">Blazing fast deployment</strong> – Cloud or edge ready.</span>
          </li>
        </ul>
        
        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin: 28px 0 16px;">
          <a href="#" class="btn-primary" style="background:#e54c2a; color:white; box-shadow:0 2px 8px rgba(229,76,42,0.2);">Deploy now →</a>
          <a href="#" class="btn-outline" style="border-radius:40px;">Watch tutorial</a>
        </div>

        <div class="stats">
          <div><span class="stat-number">10k+</span><br><span class="text-muted" style="font-size:0.8rem;">stars on GitHub</span></div>
          <div><span class="stat-number">2.5M</span><br><span class="text-muted" style="font-size:0.8rem;">apps built</span></div>
          <div><span class="stat-number">99.9%</span><br><span class="text-muted" style="font-size:0.8rem;">uptime SLA</span></div>
        </div>
      </div>

      <!-- Right visual panel: abstract "13" style brand graphic + layered glow -->
      <div class="visual-panel animate-in delay-1">
        <div class="brand-svg">
          <!-- Modern abstract logo / 'stack' shape with gradient and depth -->
          <svg viewBox="0 0 440 380" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%; height:auto;">
            <defs>
              <linearGradient id="gradA" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#F53E1C" />
                <stop offset="100%" stop-color="#FF8C5A" />
              </linearGradient>
              <linearGradient id="gradB" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#FAB758" />
                <stop offset="100%" stop-color="#E54C2A" />
              </linearGradient>
            </defs>
            <!-- main shape: stylized 'K' or architectural blocks resembling "13" -->
            <g opacity="0.95">
              <path d="M124 320 L84 120 L156 120 L180 228 L204 120 L276 120 L236 320 L184 320 L204 244 L180 320 L124 320Z" fill="url(#gradA)" />
              <rect x="300" y="120" width="64" height="200" rx="12" fill="url(#gradB)" />
              <rect x="384" y="120" width="32" height="200" rx="8" fill="#E54C2A" fill-opacity="0.7" />
              <circle cx="110" cy="340" r="16" fill="#FFB48A" />
              <circle cx="356" cy="344" r="14" fill="#FAA075" />
            </g>
            <!-- layered accent lines for depth -->
            <path d="M54 260 L98 260 M378 260 L420 260" stroke="#FFC6A5" stroke-width="4" stroke-linecap="round" />
            <path d="M64 200 L112 200 M368 200 L412 200" stroke="#FFC6A5" stroke-width="3" stroke-linecap="round" opacity="0.6"/>
            <!-- decorative floating dots -->
            <circle cx="398" cy="172" r="5" fill="#FFE0C7" />
            <circle cx="66" cy="144" r="5" fill="#FFE0C7" />
          </svg>
        </div>
        <!-- subtle inner shadow overlay -->
        <div style="position: absolute; inset:0; border-radius: inherit; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05), inset 0 0 0 2px rgba(255,255,255,0.3); pointer-events: none;"></div>
      </div>
    </div>

    <!-- Feature highlights section (extra credibility) -->
    <div style="display: flex; flex-wrap: wrap; gap: 32px; justify-content: space-between; margin: 48px 0 32px;">
      <div style="flex:1; min-width: 200px;" class="animate-in delay-2">
        <div style="font-size: 2rem;">⚡</div>
        <h3 style="margin:12px 0 6px;">Edge-ready</h3>
        <p class="text-muted" style="font-size:0.9rem;">Deploy to any serverless environment with zero config.</p>
      </div>
      <div style="flex:1; min-width: 200px;" class="animate-in delay-2">
        <div style="font-size: 2rem;">🛡️</div>
        <h3 style="margin:12px 0 6px;">Security first</h3>
        <p class="text-muted" style="font-size:0.9rem;">Encrypted sessions, CSRF, XSS protection built-in.</p>
      </div>
      <div style="flex:1; min-width: 200px;" class="animate-in delay-3">
        <div style="font-size: 2rem;">🚀</div>
        <h3 style="margin:12px 0 6px;">Instant deploy</h3>
        <p class="text-muted" style="font-size:0.9rem;">One-click from CI/CD to production with versioning.</p>
      </div>
    </div>

    <!-- CTA Section & Deploy info -->
    <div style="background: #F7F5F0; border-radius: 40px; padding: 2rem 2rem; margin: 48px 0 24px; text-align: center;" class="hover-lift">
      @media (prefers-color-scheme: dark) {
        background: #1f1f1c;
      }
      <h2 style="font-size: 1.8rem;">Start building today</h2>
      <p style="max-width: 500px; margin: 12px auto; color: #6b6a66;">No credit card required. Spin up a project in seconds.</p>
      <div style="display: flex; gap: 16px; justify-content: center; margin-top: 24px; flex-wrap: wrap;">
        <a href="#" class="btn-primary" style="background:#121212; color:white;">Launch Dashboard →</a>
        <a href="#" class="btn-outline">Read documentation</a>
      </div>
      <div style="margin-top: 32px; font-size: 0.8rem; font-family: monospace; background: #eae8e3; display: inline-block; padding: 8px 20px; border-radius: 60px;">
        ⚡ $ ./deploy --target=production
      </div>
    </div>

    <!-- footer with changelog & version mimic -->
    <footer class="footer-links">
      <div style="display: flex; gap: 32px; flex-wrap: wrap;">
        <span>© 2026 LaunchFlow, Inc.</span>
        <a href="#" style="text-decoration: none; color: inherit;">Changelog</a>
        <a href="#" style="text-decoration: none; color: inherit;">GitHub</a>
        <a href="#" style="text-decoration: none; color: inherit;">Status</a>
      </div>
      <div style="display: flex; align-items: center; gap: 12px;">
        <span>v4.2.0</span>
        <a href="#" style="display: inline-flex; gap: 5px; align-items: center; text-decoration: none; color: #e54c2a; font-weight: 500;">View release notes
          <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M7.5 7.5V3H3M2.5 9L9 2.5" stroke="currentColor" stroke-width="1.2"/></svg>
        </a>
      </div>
    </footer>
  </div>

  <!-- Simple script to handle dark mode listener and dynamic smoothness -->
  <script>
    // optional: add small interactive console greeting 
    (function() {
      console.log("🚀 Landing page ready — deploy anywhere (static hosting, Vercel, Netlify, Cloudflare)");
      // add any hover effect or class toggle for fun
      const buttons = document.querySelectorAll('.btn-primary');
      buttons.forEach(btn => {
        btn.addEventListener('mouseenter', (e) => {
          if(btn.style.background === 'rgb(27, 27, 24)' || btn.style.background === '#1b1b18') {
            // subtle microinteraction
          }
        });
      });
    })();
  </script>
  <!-- Ensure consistent dark mode based on system (already via CSS prefers) -->
  <style>
    .text-muted {
      color: #6f6e6b;
    }
    @media (prefers-color-scheme: dark) {
      .text-muted {
        color: #aaa8a2;
      }
      .info-panel .badge-panel {
        background: #2c2b29;
        color: #cbcbc4;
      }
      .stats {
        border-top-color: #2a2a28;
      }
      div[style*="background: #F7F5F0"] {
        background: #1d1d1b !important;
      }
      .btn-outline {
        background: transparent;
      }
    }
    .font-semibold {
      font-weight: 600;
    }
  </style>
</body>
</html>
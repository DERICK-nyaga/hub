<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>DNLC Group — Hub Management for Jumia</title>
  <!-- Google Fonts + fallback -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <style>
    /* ----- RESET & BASE (matching original dark/light vibe) ----- */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
      background: #FDFDFC;
      color: #1B1B18;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }

    @media (prefers-color-scheme: dark) {
      body {
        background: #0a0a0a;
        color: #EDEDEC;
      }
      .card-panel {
        background: #161615 !important;
        box-shadow: inset 0 0 0 1px rgba(255, 250, 237, 0.18) !important;
      }
      .graphic-panel {
        background: #0f0e0c !important;
        border-color: #2e2c28 !important;
      }
      .text-muted {
        color: #A1A09A !important;
      }
      .bullet-bg {
        background: #2a2927;
        box-shadow: inset 0 0 0 1px #3E3E3A;
      }
      .bullet-dot {
        background: #6c6a64;
      }
      .border-subtle {
        border-color: #3E3E3A;
      }
      .btn-deploy {
        background: #eeeeec;
        color: #1C1C1A;
        border-color: #eeeeec;
      }
      .btn-deploy:hover {
        background: white;
        border-color: white;
      }
      .link-accent {
        color: #FF4433;
      }
    }

    /* layout utilities (mirror original flex structure) */
    .flex {
      display: flex;
    }
    .flex-col {
      flex-direction: column;
    }
    .flex-col-reverse {
      flex-direction: column-reverse;
    }
    .items-center {
      align-items: center;
    }
    .justify-end {
      justify-content: flex-end;
    }
    .justify-center {
      justify-content: center;
    }
    .gap-4 {
      gap: 1rem;
    }
    .gap-3 {
      gap: 0.75rem;
    }
    .gap-2 {
      gap: 0.5rem;
    }
    .w-full {
      width: 100%;
    }
    .max-w-\[335px\] {
      max-width: 335px;
    }
    .lg\:max-w-4xl {
      max-width: 56rem;
    }
    .lg\:flex-row {
      flex-direction: row;
    }
    .bg-white {
      background: white;
    }
    .rounded-lg {
      border-radius: 0.5rem;
    }
    .rounded-bl-lg {
      border-bottom-left-radius: 0.5rem;
    }
    .rounded-br-lg {
      border-bottom-right-radius: 0.5rem;
    }
    .lg\:rounded-tl-lg {
      border-top-left-radius: 0.5rem;
    }
    .lg\:rounded-br-none {
      border-bottom-right-radius: 0;
    }
    .shadow-inner-border {
      box-shadow: inset 0px 0px 0px 1px rgba(26, 26, 0, 0.16);
    }
    .relative {
      position: relative;
    }
    .overflow-hidden {
      overflow: hidden;
    }
    .p-6 {
      padding: 1.5rem;
    }
    .lg\:p-20 {
      padding: 5rem;
    }
    .lg\:pb-10 {
      padding-bottom: 2.5rem;
    }
    .py-2 {
      padding-top: 0.5rem;
      padding-bottom: 0.5rem;
    }
    .px-5 {
      padding-left: 1.25rem;
      padding-right: 1.25rem;
    }
    .py-1\.5 {
      padding-top: 0.375rem;
      padding-bottom: 0.375rem;
    }
    .mb-1 {
      margin-bottom: 0.25rem;
    }
    .mb-2 {
      margin-bottom: 0.5rem;
    }
    .mb-4 {
      margin-bottom: 1rem;
    }
    .mt-6 {
      margin-top: 1.5rem;
    }
    .ml-1 {
      margin-left: 0.25rem;
    }
    .text-sm {
      font-size: 0.875rem;
    }
    .text-\[13px\] {
      font-size: 13px;
    }
    .leading-\[20px\] {
      line-height: 20px;
    }
    .font-medium {
      font-weight: 500;
    }
    .font-semibold {
      font-weight: 600;
    }
    .underline {
      text-decoration: underline;
    }
    .underline-offset-4 {
      text-underline-offset: 4px;
    }
    .inline-flex {
      display: inline-flex;
    }
    .items-center {
      align-items: center;
    }
    .space-x-1 > :not(:last-child) {
      margin-right: 0.25rem;
    }
    .border {
      border: 1px solid;
    }
    .border-black {
      border-color: #000;
    }
    .border-\[\#19140035\] {
      border-color: #19140035;
    }
    .rounded-sm {
      border-radius: 0.25rem;
    }
    .bg-\[\#1b1b18\] {
      background: #1b1b18;
    }
    .text-white {
      color: white;
    }
    .text-\[\#706f6c\] {
      color: #706f6c;
    }
    .text-\[\#f53003\] {
      color: #f53003;
    }
    /* checklist bullet styles (matching original) */
    .checklist-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.5rem 0;
      position: relative;
    }
    .checklist-item::before {
      content: '';
      position: absolute;
      left: 0.4rem;
      top: 50%;
      bottom: 0;
      border-left: 1px solid #e3e3e0;
      transform: translateY(0);
    }
    .dark .checklist-item::before {
      border-color: #3E3E3A;
    }
    .checklist-item:first-child::before {
      top: 0;
    }
    .checklist-item:last-child::before {
      bottom: 50%;
    }
    .bullet-circle {
      background: #FDFDFC;
      border-radius: 999px;
      width: 1.75rem;
      height: 1.75rem;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0px 0px 1px 0px rgba(0,0,0,0.03), 0px 1px 2px 0px rgba(0,0,0,0.06);
      border: 1px solid #e3e3e0;
      position: relative;
      z-index: 2;
      background: white;
    }
    .dark .bullet-circle {
      background: #161615;
      border-color: #3E3E3A;
    }
    .bullet-dot {
      width: 0.6rem;
      height: 0.6rem;
      background: #dbdbd7;
      border-radius: 50%;
    }
    .dark .bullet-dot {
      background: #3E3E3A;
    }
    /* layout adjustments */
    @media (min-width: 1024px) {
      .lg\:grow {
        flex-grow: 1;
      }
      .lg\:w-\[438px\] {
        width: 438px;
      }
      .lg\:-ml-px {
        margin-left: -1px;
      }
      .lg\:rounded-r-lg {
        border-top-right-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
      }
      .lg\:rounded-t-none {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
      }
    }
    .container-center {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      transition: opacity 0.75s;
    }
    header nav a {
      transition: all 0.2s;
    }
    .inline-arrow {
      width: 10px;
      height: 11px;
    }
    .hover-lift:hover {
      transform: translateY(-1px);
    }

    /* Company logo custom styling */
    .dnlc-logo {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      background: radial-gradient(ellipse at 30% 40%, rgba(229,76,42,0.08), transparent);
    }
    .logo-mark {
      width: 180px;
      height: auto;
      margin-bottom: 1rem;
    }
    .logo-text-dnlc {
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: -0.02em;
      background: linear-gradient(135deg, #E54C2A 0%, #C83A1A 100%);
      background-clip: text;
      -webkit-background-clip: text;
      color: transparent;
    }
    .dark .logo-text-dnlc {
      background: linear-gradient(135deg, #FF6B4A, #E54C2A);
      background-clip: text;
      -webkit-background-clip: text;
    }
    .logo-badge {
      font-size: 0.7rem;
      font-weight: 500;
      letter-spacing: 0.2em;
      color: #706f6c;
      margin-top: 0.5rem;
    }
    /* abstract hub ornament */
    .hub-ornament {
      width: 100%;
      margin-top: 1rem;
    }
  </style>
</head>
<body class="flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
  <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6">
    <nav class="flex items-center justify-end gap-4">
      <a href="{{ route('login') }}" class="inline-block px-5 py-1.5 text-[#1b1b18] dark:text-[#EDEDEC] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal transition-all">
        Log in
      </a>
      <a href="{{ route('register') }}" class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border border-[#19140035] hover:border-[#1915014a] text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal transition-all">
        Register
      </a>
    </nav>
  </header>

  <div class="flex items-center justify-center w-full transition-opacity opacity-100 lg:grow">
    <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
      <!-- LEFT PANEL: main content with DNLC specific messaging (preserved original workflow & links) -->
      <div class="text-[13px] leading-[20px] flex-1 p-6 pb-6 lg:p-20 lg:pb-10 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-inner-border dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
        <h1 class="mb-1 font-medium text-xl lg:text-2xl">DNLC Group</h1>
        <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">Expert hub management for Jumia sellers & logistics.<br /> Scale your marketplace operations seamlessly.</p>
        
        <!-- Original checklist structure but rebranded for DNLC / Jumia hub workflow (links fully preserved) -->
        <ul class="flex flex-col mb-4 lg:mb-6">
          <li class="checklist-item">
            <span class="bullet-circle"><span class="bullet-dot"></span></span>
            <span>
              Optimize your 
              <a href="https://sellercenter.jumia.com/" target="_blank" class="inline-flex items-center space-x-1 font-medium underline underline-offset-4 text-[#f53003] dark:text-[#FF4433] ml-1">
                <span>Jumia Seller Hub</span>
                <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5"><path d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001" stroke="currentColor" stroke-linecap="square"/></svg>
              </a>
              <span class="ml-1">with data-driven logistics.</span>
            </span>
          </li>
          <li class="checklist-item">
            <span class="bullet-circle"><span class="bullet-dot"></span></span>
            <span>
              Centralized hub performance metrics & 
              <a href="https://laracasts.com" target="_blank" class="inline-flex items-center space-x-1 font-medium underline underline-offset-4 text-[#f53003] dark:text-[#FF4433] ml-1">
                <span>inventory sync</span>
                <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5"><path d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001" stroke="currentColor" stroke-linecap="square"/></svg>
              </a>
            </span>
          </li>
        </ul>
        
        <!-- "Launch Hub Console" button now links to /stations (as per Route::resource definition) -->
        <ul class="flex gap-3 text-sm leading-normal">
          <li>
            <a href="/stations" class="inline-block dark:bg-[#eeeeec] dark:border-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white dark:hover:border-white hover:bg-black hover:border-black px-5 py-1.5 bg-[#1b1b18] rounded-sm border border-black text-white text-sm leading-normal transition-all">
              Launch Hub Console →
            </a>
          </li>
        </ul>

        <p class="mt-6 lg:mt-10 text-[#706f6c] dark:text-[#A1A09A]">
          v2.3.1 · DNLC Hub Manager
          <a href="https://github.com/laravel/laravel/blob/13.x/CHANGELOG.md" target="_blank" class="inline-flex items-center space-x-1 font-medium underline underline-offset-4 text-[#f53003] dark:text-[#FF4433] ml-1">
            <span>What's new</span>
            <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5"><path d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001" stroke="currentColor" stroke-linecap="square"/></svg>
          </a>
        </p>
      </div>

      <!-- RIGHT PANEL: Custom company logo + DNLC brand visual (removed Laravel 13, added DNLC identity) -->
      <div class="bg-[#FAF7F2] dark:bg-[#0F0E0C] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/364] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden flex items-center justify-center">
        <div class="dnlc-logo p-6 text-center">
          <!-- modern DNLC monogram / company logo (custom vector) -->
          <svg class="logo-mark" viewBox="0 0 200 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#E54C2A"/>
                <stop offset="100%" stop-color="#B82D0C"/>
              </linearGradient>
              <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#FF8C5A" stop-opacity="0.9"/>
                <stop offset="100%" stop-color="#E54C2A" stop-opacity="0.3"/>
              </linearGradient>
            </defs>
            <!-- hub / network emblem: abstract D and N linked -->
            <circle cx="100" cy="80" r="58" stroke="url(#ringGrad)" stroke-width="4" fill="none"/>
            <circle cx="100" cy="80" r="44" stroke="#E54C2A" stroke-width="2.5" stroke-dasharray="6 6" fill="none" opacity="0.5"/>
            <path d="M78 80 L66 80 L66 44 L78 44 L78 80Z" fill="url(#logoGrad)"/>
            <path d="M100 44 L100 108 C108 116 124 116 132 108 C140 100 140 80 132 72 C124 64 108 64 100 72 L100 80" stroke="url(#logoGrad)" stroke-width="7" fill="none" stroke-linecap="round"/>
            <path d="M134 80 L158 80 L158 68 L134 68 L134 80Z" fill="url(#logoGrad)"/>
            <circle cx="88" cy="92" r="6" fill="#FFB48A"/>
            <circle cx="112" cy="96" r="4" fill="#FFB48A"/>
            <!-- connection nodes -->
            <path d="M94 72 L106 84 M94 84 L106 72" stroke="#FF8C5A" stroke-width="1.5" stroke-linecap="round" opacity="0.7"/>
          </svg>
          
          <div class="logo-text-dnlc" style="font-size: 1.8rem; font-weight: 700; margin-top: 0.5rem;">DNLC GROUP</div>
          <div class="logo-badge">HUB MANAGEMENT</div>
          
          <!-- additional decorative element: hub network lines -->
          <div class="hub-ornament mt-6">
            <svg width="180" height="40" viewBox="0 0 240 40" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M10 20 L40 20 M70 20 L100 20 M130 20 L160 20 M190 20 L220 20" stroke="#E54C2A" stroke-width="1.5" stroke-dasharray="3 4" opacity="0.5"/>
              <circle cx="40" cy="20" r="3" fill="#E54C2A"/>
              <circle cx="100" cy="20" r="3" fill="#E54C2A"/>
              <circle cx="160" cy="20" r="3" fill="#E54C2A"/>
              <circle cx="220" cy="20" r="3" fill="#E54C2A"/>
              <circle cx="25" cy="20" r="2" fill="#FF9F7A" stroke="#E54C2A" stroke-width="0.5"/>
              <circle cx="85" cy="20" r="2" fill="#FF9F7A" stroke="#E54C2A" stroke-width="0.5"/>
              <circle cx="145" cy="20" r="2" fill="#FF9F7A" stroke="#E54C2A" stroke-width="0.5"/>
              <circle cx="205" cy="20" r="2" fill="#FF9F7A" stroke="#E54C2A" stroke-width="0.5"/>
            </svg>
            <p class="text-[10px] tracking-wider text-[#706f6c] dark:text-[#8a8a85] mt-2">connected logistics · multi‑hub sync</p>
          </div>
        </div>
        <!-- subtle inner shadow overlay (original style) -->
        <div class="absolute inset-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] pointer-events-none"></div>
      </div>
    </main>
  </div>

  <!-- subtle spacer (matching original) -->
  <div class="h-14.5 hidden lg:block"></div>

  <!-- optional small style to maintain link colors consistency & dark mode for new elements -->
  <style>
    @media (prefers-color-scheme: dark) {
      .bg-white, .card-panel {
        background-color: #161615;
      }
      .shadow-inner-border {
        box-shadow: inset 0px 0px 0px 1px rgba(255, 250, 237, 0.15);
      }
      .checklist-item::before {
        border-left-color: #3E3E3A;
      }
      .logo-badge {
        color: #A1A09A;
      }
      .dnlc-logo svg circle[stroke="#E54C2A"] {
        stroke: #FF6B4A;
      }
      .dnlc-logo svg path[stroke="#E54C2A"] {
        stroke: #FF6B4A;
      }
      .logo-text-dnlc {
        background: linear-gradient(135deg, #FF7A55, #FF4C2C);
        background-clip: text;
        -webkit-background-clip: text;
      }
    }
    .transition-all {
      transition-property: all;
      transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
      transition-duration: 0.2s;
    }
    .hover\:border-black:hover {
      border-color: black;
    }
    .dark .hover\:border-white:hover {
      border-color: white;
    }
    /* ensure no leftover 13 graphic styling */
    .graphic-panel svg:first-child {
      display: block;
    }
  </style>
</body>
</html>
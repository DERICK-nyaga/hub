Here is a complete, deployable HTML document that creates a modern landing page for DNLC Group, maintaining the original layout and links while adding a professional brand identity for hub management.
```html
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
        background: #1D0002 !important;
        border-color: #2e1f1c !important;
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
    .brand-icon-svg {
      width: 100%;
      max-width: 380px;
      transition: transform 0.2s;
    }
    .inline-arrow {
      width: 10px;
      height: 11px;
    }
    /* original external link hover effect */
    .hover-lift:hover {
      transform: translateY(-1px);
    }
  </style>
</head>
<body class="flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
  <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6">
    <nav class="flex items-center justify-end gap-4">
      <a href="#" class="inline-block px-5 py-1.5 text-[#1b1b18] dark:text-[#EDEDEC] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal transition-all">
        Log in
      </a>
      <a href="#" class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border border-[#19140035] hover:border-[#1915014a] text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal transition-all">
        Register
      </a>
    </nav>
  </header>

  <div class="flex items-center justify-center w-full transition-opacity opacity-100 lg:grow">
    <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
      <!-- LEFT PANEL: main content with DNLC specific messaging (preserved original workflow) -->
      <div class="text-[13px] leading-[20px] flex-1 p-6 pb-6 lg:p-20 lg:pb-10 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-inner-border dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
        <h1 class="mb-1 font-medium text-xl lg:text-2xl">DNLC Group</h1>
        <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">Expert hub management for Jumia sellers & logistics.<br /> Scale your marketplace operations seamlessly.</p>
        
        <!-- Original list structure but rebranded for DNLC / Jumia hub workflow (maintained original links) -->
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
        
        <!-- "deploy now" style CTA with link from original (cloud.laravel.com replaced with demo Jumia partner link but structurally same) -->
        <ul class="flex gap-3 text-sm leading-normal">
          <li>
            <a href="https://cloud.laravel.com" target="_blank" class="inline-block dark:bg-[#eeeeec] dark:border-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white dark:hover:border-white hover:bg-black hover:border-black px-5 py-1.5 bg-[#1b1b18] rounded-sm border border-black text-white text-sm leading-normal transition-all">
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

      <!-- RIGHT PANEL: Custom illustration + graphic (maintains original "13" aesthetic but enhanced with DNLC / Jumia vibes) -->
      <div class="bg-[#fff2f2] dark:bg-[#1D0002] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/364] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
        <!-- Modern Hub Management abstract graphic (inspired by original laravel/13 brand) -->
        <svg class="w-full text-[#F53003] dark:text-[#F61500] transition-all translate-y-0 opacity-100 max-w-none duration-750" viewBox="0 0 438 104" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-top: 1.5rem;">
          <path d="M17.2036 -3H0V102.197H49.5189V86.7187H17.2036V-3Z" fill="currentColor" />
          <path d="M110.256 41.6337C108.061 38.1275 104.945 35.3731 100.905 33.3681C96.8667 31.3647 92.8016 30.3618 88.7131 30.3618C83.4247 30.3618 78.5885 31.3389 74.201 33.2923C69.8111 35.2456 66.0474 37.928 62.9059 41.3333C59.7643 44.7401 57.3198 48.6726 55.5754 53.1293C53.8287 57.589 52.9572 62.274 52.9572 67.1813C52.9572 72.1925 53.8287 76.8995 55.5754 81.3069C57.3191 85.7173 59.7636 89.6241 62.9059 93.0293C66.0474 96.4361 69.8119 99.1155 74.201 101.069C78.5885 103.022 83.4247 103.999 88.7131 103.999C92.8016 103.999 96.8667 102.997 100.905 100.994C104.945 98.9911 108.061 96.2359 110.256 92.7282V102.195H126.563V32.1642H110.256V41.6337ZM108.76 75.7472C107.762 78.4531 106.366 80.8078 104.572 82.8112C102.776 84.8161 100.606 86.4183 98.0637 87.6206C95.5202 88.823 92.7004 89.4238 89.6103 89.4238C86.5178 89.4238 83.7252 88.823 81.2324 87.6206C78.7388 86.4183 76.5949 84.8161 74.7998 82.8112C73.004 80.8078 71.6319 78.4531 70.6856 75.7472C69.7356 73.0421 69.2644 70.1868 69.2644 67.1821C69.2644 64.1758 69.7356 61.3205 70.6856 58.6154C71.6319 55.9102 73.004 53.5571 74.7998 51.5522C76.5949 49.5495 78.738 47.9451 81.2324 46.7427C83.7252 45.5404 86.5178 44.9396 89.6103 44.9396C92.7012 44.9396 95.5202 45.5404 98.0637 46.7427C100.606 47.9451 102.776 49.5487 104.572 51.5522C106.367 53.5571 107.762 55.9102 108.76 58.6154C109.756 61.3205 110.256 64.1758 110.256 67.1821C110.256 70.1868 109.756 73.0421 108.76 75.7472Z" fill="currentColor" />
          <path d="M242.805 41.6337C240.611 38.1275 237.494 35.3731 233.455 33.3681C229.416 31.3647 225.351 30.3618 221.262 30.3618C215.974 30.3618 211.138 31.3389 206.75 33.2923C202.36 35.2456 198.597 37.928 195.455 41.3333C192.314 44.7401 189.869 48.6726 188.125 53.1293C186.378 57.589 185.507 62.274 185.507 67.1813C185.507 72.1925 186.378 76.8995 188.125 81.3069C189.868 85.7173 192.313 89.6241 195.455 93.0293C198.597 96.4361 202.361 99.1155 206.75 101.069C211.138 103.022 215.974 103.999 221.262 103.999C225.351 103.999 229.416 102.997 233.455 100.994C237.494 98.9911 240.611 96.2359 242.805 92.7282V102.195H259.112V32.1642H242.805V41.6337ZM241.31 75.7472C240.312 78.4531 238.916 80.8078 237.122 82.8112C235.326 84.8161 233.156 86.4183 230.614 87.6206C228.07 88.823 225.251 89.4238 222.16 89.4238C219.068 89.4238 216.275 88.823 213.782 87.6206C211.289 86.4183 209.145 84.8161 207.35 82.8112C205.554 80.8078 204.182 78.4531 203.236 75.7472C202.286 73.0421 201.814 70.1868 201.814 67.1821C201.814 64.1758 202.286 61.3205 203.236 58.6154C204.182 55.9102 205.554 53.5571 207.35 51.5522C209.145 49.5495 211.288 47.9451 213.782 46.7427C216.275 45.5404 219.068 44.9396 222.16 44.9396C225.251 44.9396 228.07 45.5404 230.614 46.7427C233.156 47.9451 235.326 49.5487 237.122 51.5522C238.917 53.5571 240.312 55.9102 241.31 58.6154C242.306 61.3205 242.806 64.1758 242.806 67.1821C242.805 70.1868 242.305 73.0421 241.31 75.7472Z" fill="currentColor" />
          <path d="M438 -3H421.694V102.197H438V-3Z" fill="currentColor" />
          <path d="M139.43 102.197H155.735V48.2834H183.712V32.1665H139.43V102.197Z" fill="currentColor" />
          <path d="M324.49 32.1665L303.995 85.794L283.498 32.1665H266.983L293.748 102.197H314.242L341.006 32.1665H324.49Z" fill="currentColor" />
          <path d="M376.571 30.3656C356.603 30.3656 340.797 46.8497 340.797 67.1828C340.797 89.6597 356.094 104 378.661 104C391.29 104 399.354 99.1488 409.206 88.5848L398.189 80.0226C398.183 80.031 389.874 90.9895 377.468 90.9895C363.048 90.9895 356.977 79.3111 356.977 73.269H411.075C413.917 50.1328 398.775 30.3656 376.571 30.3656ZM357.02 61.0967C357.145 59.7487 359.023 43.3761 376.442 43.3761C393.861 43.3761 395.978 59.7464 396.099 61.0967H357.02Z" fill="currentColor" />
        </svg>
        
        <!-- stylized DNLC "hub" graphic that resembles original "13" but introduces DNLC brand (with original layer approach) -->
        <svg class="w-[438px] max-w-none relative -mt-[6rem] -ml-8 lg:ml-0" viewBox="0 0 440 392" fill="none" xmlns="http://www.w3.org/2000/svg">
          <g class="mix-blend-darken dark:mix-blend-normal transition-all delay-300 opacity-100 text-[#1B1B18] dark:text-black">
            <mask id="hub-mask" maskUnits="userSpaceOnUse" x="0" y="100" width="360" height="300" fill="black">
              <rect fill="white" x="0" y="100" width="360" height="300"/>
              <path d="M220 400C190 400 165 391 145 375C126 359 116 338 116 312H186C186 320 189 327 195 332C201 337 209 340 219 340C229 340 237 337 243 332C249 327 253 320 253 312C253 303 250 296 244 291C238 286 230 283 222 283H185V221H222C229 221 235 218 240 213C245 208 247 202 247 195C247 186 244 180 238 174C233 169 226 166 218 166C211 166 204 168 199 172C194 177 191 182 191 189H124C124 166 133 146 151 131C169 116 192 108 220 108C248 108 270 116 288 130C306 145 315 164 315 188C315 205 310 219 301 230C292 241 280 248 265 252C283 257 297 266 307 278C318 290 323 305 323 323C323 348 313 369 294 385C275 401 250 400 220 400Z"/>
            </mask>
            <path d="M220 400C190 400 165 391 145 375C126 359 116 338 116 312H186C186 320 189 327 195 332C201 337 209 340 219 340C229 340 237 337 243 332C249 327 253 320 253 312C253 303 250 296 244 291C238 286 230 283 222 283H185V221H222C229 221 235 218 240 213C245 208 247 202 247 195C247 186 244 180 238 174C233 169 226 166 218 166C211 166 204 168 199 172C194 177 191 182 191 189H124C124 166 133 146 151 131C169 116 192 108 220 108C248 108 270 116 288 130C306 145 315 164 315 188C315 205 310 219 301 230C292 241 280 248 265 252C283 257 297 266 307 278C318 290 323 305 323 323C323 348 313 369 294 385C275 401 250 400 220 400Z" fill="currentColor"/>
          </g>
          <g class="transition-all delay-400 opacity-100 motion-safe:starting:-translate-x-[26px] text-[#F3BEC7] dark:text-[#4B0600]">
            <path d="M246 400C216 400 192 391 173 375C155 359 145 338 145 312H213C213 320 216 327 222 332C228 337 236 340 246 340C256 340 264 337 270 332C276 327 280 320 280 312C280 303 277 296 271 291C265 286 257 283 249 283H213V221H249C256 221 262 218 267 213C272 208 274 202 274 195C274 186 271 180 264 174C259 169 252 166 244 166C237 166 230 168 225 172C220 177 217 182 217 189H151C151 166 160 146 178 131C196 116 219 108 246 108C273 108 295 116 312 130C329 145 338 164 338 188C338 205 333 219 324 230C315 241 303 248 289 252C306 257 320 266 330 278C340 290 345 305 345 323C345 348 335 369 317 385C298 401 274 400 246 400Z" fill="currentColor"/>
          </g>
        </svg>
        
        <!-- decorative overlay (like original shadow) -->
        <div class="absolute inset-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] pointer-events-none"></div>
      </div>
    </main>
  </div>

  <!-- subtle spacer (matching original) -->
  <div class="h-14.5 hidden lg:block"></div>

  <!-- optional small style to maintain link colors consistency -->
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
  </style>
</body>
</html>
```
<div class="commercial-settings-container">
    <!-- Settings Header -->
    <div class="settings-header-commercial">
        <div class="settings-header-content">
            <div class="settings-icon-wrapper">
                <svg class="settings-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19.4 15.05L18.3 15.65C18.1 15.75 17.9 15.85 17.7 15.95C17.4 16.1 17.1 16.45 17 16.8L16.5 18.3C16.4 18.65 16.1 18.9 15.8 18.9H8.2C7.9 18.9 7.6 18.65 7.5 18.3L7 16.8C6.9 16.45 6.6 16.1 6.3 15.95C6.1 15.85 5.9 15.75 5.7 15.65L4.6 15.05C4.3 14.9 4 14.95 3.8 15.2L2.7 16.8C2.5 17.05 2.2 17.15 1.9 17L0.5 16.3C0.2 16.15 -0.1 15.8 0 15.5L0.5 14C0.6 13.7 0.5 13.4 0.3 13.2L-0.7 11.8C-0.9 11.6 -0.9 11.2 -0.7 11L0.3 9.6C0.5 9.4 0.6 9.1 0.5 8.8L0 7.3C-0.1 7 -0.2 6.6 0.1 6.5L1.5 5.8C1.8 5.65 2 5.35 2 5.05L2.1 3.5C2.1 3.2 2.4 2.95 2.7 2.95H10.3C10.6 2.95 10.9 3.2 11 3.5L11.5 5.05C11.6 5.35 11.9 5.65 12.2 5.8C12.4 5.9 12.6 6 12.8 6.1L13.9 5.5C14.2 5.35 14.5 5.4 14.7 5.65L15.8 7.25C16 7.5 16.3 7.6 16.6 7.45L18 6.75C18.3 6.6 18.6 6.75 18.7 7.05L19.2 8.55C19.3 8.85 19.6 9.1 19.9 9.1H21.4C21.7 9.1 22 9.35 22 9.65V12.35C22 12.65 21.7 12.9 21.4 12.9H19.9C19.6 12.9 19.3 13.15 19.2 13.45L18.7 14.95C18.6 15.25 18.4 15.5 18.2 15.65L19.4 15.05Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="settings-header-text">
                <h2>Display Preferences</h2>
                <p>Customize your viewing experience</p>
            </div>
        </div>
    </div>

    <!-- Theme Section -->
    <div class="settings-section-commercial">
        <div class="section-header">
            <div class="section-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 3V1M12 21V23M4.22 4.22L2.8 2.8M19.78 4.22L21.2 2.8M3 12H1M21 12H23M4.22 19.78L2.8 21.2M19.78 19.78L21.2 21.2M12 17C14.7614 17 17 14.7614 17 12C17 9.23858 14.7614 7 12 7C9.23858 7 7 9.23858 7 12C7 14.7614 9.23858 17 12 17Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3>Theme Preference</h3>
            <span class="section-badge">Visual</span>
        </div>
        
        <div class="theme-grid-commercial">
            <button onclick="setTheme('light')" id="themeLightBtn" class="theme-card-commercial">
                <div class="theme-preview light-preview">
                    <div class="preview-header"></div>
                    <div class="preview-content">
                        <div class="preview-line"></div>
                        <div class="preview-line short"></div>
                    </div>
                </div>
                <div class="theme-info">
                    <i class="fas fa-sun"></i>
                    <span>Light Mode</span>
                    <div class="theme-checkmark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </button>
            
            <button onclick="setTheme('dark')" id="themeDarkBtn" class="theme-card-commercial">
                <div class="theme-preview dark-preview">
                    <div class="preview-header"></div>
                    <div class="preview-content">
                        <div class="preview-line"></div>
                        <div class="preview-line short"></div>
                    </div>
                </div>
                <div class="theme-info">
                    <i class="fas fa-moon"></i>
                    <span>Dark Mode</span>
                    <div class="theme-checkmark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </button>
            
            <button onclick="setTheme('system')" id="themeSystemBtn" class="theme-card-commercial">
                <div class="theme-preview system-preview">
                    <div class="preview-header"></div>
                    <div class="preview-content">
                        <div class="preview-line"></div>
                        <div class="preview-line short"></div>
                    </div>
                </div>
                <div class="theme-info">
                    <i class="fas fa-desktop"></i>
                    <span>System Default</span>
                    <div class="theme-checkmark">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </button>
        </div>
    </div>

    <!-- Brightness Section -->
    <div class="settings-section-commercial">
        <div class="section-header">
            <div class="section-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 18V20M12 4V2M20 12H22M2 12H4M19.07 4.93L17.66 6.34M6.34 17.66L4.93 19.07M17.66 17.66L19.07 19.07M4.93 4.93L6.34 6.34M12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3>Brightness Control</h3>
            <span class="section-badge">Display</span>
        </div>
        
        <div class="brightness-control-commercial">
            <div class="brightness-header">
                <div class="brightness-label">
                    <i class="fas fa-adjust"></i>
                    <span>Screen Brightness</span>
                </div>
                <div class="brightness-value-commercial">
                    <span id="brightnessValue">100</span>
                    <span class="percent-sign">%</span>
                </div>
            </div>
            
            <div class="brightness-slider-container">
                <i class="fas fa-sun low-brightness"></i>
                <input type="range" 
                       id="brightnessSlider"
                       min="30" 
                       max="150" 
                       step="5"
                       value="100"
                       class="brightness-slider-commercial">
                <i class="fas fa-sun high-brightness"></i>
            </div>
            
            <div class="brightness-actions-commercial">
                <button onclick="decreaseBrightness()" class="action-btn-commercial">
                    <i class="fas fa-minus"></i>
                    <span>Decrease</span>
                </button>
                <button onclick="resetBrightness()" class="action-btn-commercial reset-btn">
                    <i class="fas fa-undo-alt"></i>
                    <span>Reset to 100%</span>
                </button>
                <button onclick="increaseBrightness()" class="action-btn-commercial">
                    <span>Increase</span>
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Information Section -->
    <div class="settings-footer-commercial">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-icon-wrapper">
                    <i class="fas fa-save"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">Auto-Save</span>
                    <span class="info-value">Settings saved instantly</span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon-wrapper">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">Account Sync</span>
                    <span class="info-value" id="userStatusText">Loading...</span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon-wrapper">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="info-content">
                    <span class="info-label">Cloud Backup</span>
                    <span class="info-value">Preferences backed up</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.commercial-settings-container {
    background: var(--bg-primary, #ffffff);
    border-radius: 24px;
    overflow: hidden;
}

/* Settings Header */
.settings-header-commercial {
    padding: 28px 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.settings-header-commercial::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: rotate 20s linear infinite;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.settings-header-content {
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    z-index: 1;
}

.settings-icon-wrapper {
    width: 64px;
    height: 64px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.settings-icon {
    width: 32px;
    height: 32px;
    color: white;
}

.settings-header-text h2 {
    color: white;
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
    letter-spacing: -0.5px;
}

.settings-header-text p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 14px;
    margin: 0;
}

/* Settings Sections */
.settings-section-commercial {
    padding: 28px 32px;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}

.section-icon {
    width: 36px;
    height: 36px;
    background: var(--bg-secondary, #f3f4f6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
}

.section-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary, #111827);
    margin: 0;
    flex: 1;
}

.section-badge {
    padding: 4px 12px;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

/* Theme Grid */
.theme-grid-commercial {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.theme-card-commercial {
    background: var(--bg-secondary, #f9fafb);
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
}

.theme-card-commercial:hover {
    transform: translateY(-2px);
    border-color: #667eea;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
}

.theme-card-commercial.active {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.05);
}

.theme-preview {
    height: 100px;
    position: relative;
    overflow: hidden;
}

.light-preview {
    background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 100%);
}

.dark-preview {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
}

.system-preview {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.preview-header {
    height: 20px;
    background: rgba(0, 0, 0, 0.1);
    margin: 12px;
    border-radius: 4px;
}

.preview-content {
    padding: 0 12px 12px 12px;
}

.preview-line {
    height: 6px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
    margin-bottom: 6px;
}

.preview-line.short {
    width: 60%;
}

.theme-info {
    padding: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    position: relative;
    background: var(--bg-primary, #ffffff);
}

.theme-info i {
    font-size: 16px;
    color: #667eea;
}

.theme-info span {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary, #111827);
}

.theme-checkmark {
    position: absolute;
    top: -8px;
    right: 12px;
    width: 20px;
    height: 20px;
    background: #667eea;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.theme-card-commercial.active .theme-checkmark {
    opacity: 1;
}

.theme-checkmark svg {
    color: white;
}

/* Brightness Control */
.brightness-control-commercial {
    background: var(--bg-secondary, #f9fafb);
    border-radius: 20px;
    padding: 24px;
}

.brightness-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 20px;
}

.brightness-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-secondary, #6b7280);
}

.brightness-value-commercial {
    font-size: 32px;
    font-weight: 700;
    color: #667eea;
    line-height: 1;
}

.percent-sign {
    font-size: 16px;
    font-weight: 500;
    color: var(--text-secondary, #6b7280);
}

.brightness-slider-container {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
}

.low-brightness {
    color: #f59e0b;
    font-size: 14px;
}

.high-brightness {
    color: #f59e0b;
    font-size: 20px;
}

.brightness-slider-commercial {
    flex: 1;
    height: 6px;
    border-radius: 3px;
    background: linear-gradient(90deg, #f59e0b 0%, #667eea 100%);
    outline: none;
    -webkit-appearance: none;
}

.brightness-slider-commercial::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 10px;
    background: #667eea;
    cursor: pointer;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.brightness-slider-commercial::-webkit-slider-thumb:hover {
    transform: scale(1.1);
}

.brightness-actions-commercial {
    display: flex;
    gap: 12px;
}

.action-btn-commercial {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--bg-primary, #ffffff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 12px;
    color: var(--text-primary, #111827);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn-commercial:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.05);
    transform: translateY(-1px);
}

.reset-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
}

.reset-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Footer Info Grid */
.settings-footer-commercial {
    padding: 24px 32px;
    background: var(--bg-secondary, #f9fafb);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.info-icon-wrapper {
    width: 40px;
    height: 40px;
    background: var(--bg-primary, #ffffff);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
    font-size: 18px;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.info-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-secondary, #6b7280);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary, #111827);
}

/* Dark Mode Adjustments */
.dark-mode .commercial-settings-container {
    background: #1f2937;
}

.dark-mode .settings-section-commercial {
    border-bottom-color: #374151;
}

.dark-mode .section-icon {
    background: #374151;
}

.dark-mode .brightness-control-commercial {
    background: #111827;
}

.dark-mode .action-btn-commercial {
    background: #1f2937;
    border-color: #374151;
    color: #e5e5e5;
}

.dark-mode .info-icon-wrapper {
    background: #1f2937;
}

/* Responsive */
@media (max-width: 768px) {
    .settings-header-commercial {
        padding: 20px 24px;
    }
    
    .settings-section-commercial {
        padding: 20px 24px;
    }
    
    .settings-footer-commercial {
        padding: 20px 24px;
    }
    
    .theme-grid-commercial {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .brightness-actions-commercial {
        flex-direction: column;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .brightness-value-commercial {
        font-size: 24px;
    }
}

/* Animation */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.settings-section-commercial {
    animation: slideIn 0.3s ease-out;
}
</style>
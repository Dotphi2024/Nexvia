/**
 * NEXVIA Timed Frontend Popup Lead Capture
 * Pure Vanilla JavaScript - No dependencies required
 */
(function () {
    'use strict';

    // Prevent duplicate initializations
    if (window.__NEXVIA_POPUP_INITIALIZED__) return;
    window.__NEXVIA_POPUP_INITIALIZED__ = true;

    var STORAGE_SUBMITTED_KEY = 'nexvia_popup_lead_submitted';
    var STORAGE_DISMISSED_KEY = 'nexvia_popup_lead_dismissed_until';

    // Resolve base API URL (supports both same-domain and cross-domain setups)
    var scriptTag = document.currentScript;
    var scriptOrigin = '';
    if (scriptTag && scriptTag.src) {
        try {
            var urlObj = new URL(scriptTag.src);
            scriptOrigin = urlObj.origin;
        } catch (e) {}
    }

    var apiBaseUrl = window.NEXVIA_API_BASE_URL || scriptOrigin || '';

    // Check if user already submitted lead
    if (localStorage.getItem(STORAGE_SUBMITTED_KEY) === 'true') {
        return;
    }

    // Check if dismissed recently within quiet hours
    var dismissedUntil = localStorage.getItem(STORAGE_DISMISSED_KEY);
    if (dismissedUntil && Date.now() < parseInt(dismissedUntil, 10)) {
        return;
    }

    // Configuration defaults (5 to 10 seconds range)
    var config = Object.assign({
        is_enabled: true,
        delay_seconds: 7,
        title: 'Get Exclusive Offers & Instant Callback',
        subtitle: 'Enter your name and mobile number. Our product specialist will call you with exclusive pricing and product details.',
        button_text: 'Request Callback Now',
        success_message: 'Thank you! We have received your request and will call you shortly.',
        reappear_hours: 24,
    }, window.NEXVIA_POPUP_CONFIG || {});

    // If config was not passed in via Blade, fetch it dynamically from API
    if (!window.NEXVIA_POPUP_CONFIG) {
        fetch(apiBaseUrl + '/api/popup-settings', {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(res) { return res.json(); })
        .then(function(json) {
            if (json && json.data) {
                config = Object.assign(config, json.data);
                initPopup();
            } else {
                initPopup();
            }
        })
        .catch(function(err) {
            console.warn('[Nexvia Popup] Could not load dynamic settings, using defaults.', err);
            initPopup();
        });
    } else {
        initPopup();
    }

    function initPopup() {
        if (!config.is_enabled) return;

        // Calculate delay in milliseconds (default 5 to 10 seconds)
        var delayMs = Math.max(2, parseInt(config.delay_seconds, 10) || 7) * 1000;

        setTimeout(function () {
            // Re-verify localStorage right before display in case another tab submitted
            if (localStorage.getItem(STORAGE_SUBMITTED_KEY) === 'true') return;
            var currentDismissed = localStorage.getItem(STORAGE_DISMISSED_KEY);
            if (currentDismissed && Date.now() < parseInt(currentDismissed, 10)) return;

            renderPopup();
        }, delayMs);
    }

    function renderPopup() {
        // Build DOM structure
        var overlay = document.createElement('div');
        overlay.id = 'nexviaPopupOverlay';
        overlay.className = 'nexvia-popup-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');

        overlay.innerHTML = 
            '<div class="nexvia-popup-card" id="nexviaPopupCard">' +
                '<button type="button" class="nexvia-popup-close" id="nexviaPopupCloseBtn" aria-label="Close popup">&times;</button>' +
                
                // Form Container
                '<div id="nexviaPopupFormContainer">' +
                    '<div class="nexvia-popup-header">' +
                        '<div class="nexvia-popup-badge">' +
                            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"></path></svg>' +
                            '<span>Exclusive Callback</span>' +
                        '</div>' +
                        '<h3 class="nexvia-popup-title" id="nexviaPopupTitle">' + escapeHtml(config.title) + '</h3>' +
                        '<p class="nexvia-popup-subtitle" id="nexviaPopupSubtitle">' + escapeHtml(config.subtitle) + '</p>' +
                    '</div>' +

                    '<form class="nexvia-popup-form" id="nexviaPopupForm" novalidate>' +
                        // Name Field
                        '<div class="nexvia-popup-form-group">' +
                            '<label class="nexvia-popup-label" for="nexviaLeadName">Your Full Name *</label>' +
                            '<div class="nexvia-popup-input-wrapper">' +
                                '<span class="nexvia-popup-icon">' +
                                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>' +
                                '</span>' +
                                '<input type="text" id="nexviaLeadName" class="nexvia-popup-input" placeholder="e.g. Rahul Sharma" autocomplete="name" required />' +
                            '</div>' +
                            '<span class="nexvia-popup-error-msg" id="nexviaNameError">Please enter your name</span>' +
                        '</div>' +

                        // Mobile Field
                        '<div class="nexvia-popup-form-group">' +
                            '<label class="nexvia-popup-label" for="nexviaLeadMobile">Mobile Number *</label>' +
                            '<div class="nexvia-popup-input-wrapper">' +
                                '<span class="nexvia-popup-icon">' +
                                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>' +
                                '</span>' +
                                '<span class="nexvia-popup-prefix">+91</span>' +
                                '<input type="tel" id="nexviaLeadMobile" class="nexvia-popup-input with-prefix" placeholder="98765 43210" maxlength="10" inputmode="numeric" autocomplete="tel" required />' +
                            '</div>' +
                            '<span class="nexvia-popup-error-msg" id="nexviaMobileError">Please enter a valid 10-digit mobile number</span>' +
                        '</div>' +

                        // Email Field (Optional)
                        '<div class="nexvia-popup-form-group">' +
                            '<label class="nexvia-popup-label" for="nexviaLeadEmail">Email Address <span style="font-weight: 400; color: #94a3b8; font-size: 11.5px;">(Optional)</span></label>' +
                            '<div class="nexvia-popup-input-wrapper">' +
                                '<span class="nexvia-popup-icon">' +
                                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>' +
                                '</span>' +
                                '<input type="email" id="nexviaLeadEmail" class="nexvia-popup-input" placeholder="e.g. rahul@example.com" autocomplete="email" />' +
                            '</div>' +
                            '<span class="nexvia-popup-error-msg" id="nexviaEmailError">Please enter a valid email address</span>' +
                        '</div>' +

                        // Submit Button
                        '<button type="submit" class="nexvia-popup-submit-btn" id="nexviaPopupSubmitBtn">' +
                            '<span class="nexvia-popup-spinner" id="nexviaBtnSpinner"></span>' +
                            '<span id="nexviaBtnText">' + escapeHtml(config.button_text) + '</span>' +
                        '</button>' +

                        '<div class="nexvia-popup-footer">' +
                            '<span>🔒 Your information is secure. No spam, only genuine assistance.</span>' +
                        '</div>' +
                    '</form>' +
                '</div>' +

                // Success Message Card
                '<div class="nexvia-popup-success" id="nexviaPopupSuccessContainer">' +
                    '<div class="nexvia-popup-success-icon">' +
                        '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' +
                    '</div>' +
                    '<h4 class="nexvia-popup-success-title">Request Received!</h4>' +
                    '<p class="nexvia-popup-success-desc" id="nexviaSuccessMsgText">' + escapeHtml(config.success_message) + '</p>' +
                '</div>' +
            '</div>';

        document.body.appendChild(overlay);

        // Force reflow and add animation class
        window.getComputedStyle(overlay).opacity;
        overlay.classList.add('active');

        // Focus first field
        var nameInput = document.getElementById('nexviaLeadName');
        if (nameInput) setTimeout(function() { nameInput.focus(); }, 350);

        // Attach event listeners
        attachEvents(overlay);
    }

    function attachEvents(overlay) {
        var closeBtn = document.getElementById('nexviaPopupCloseBtn');
        var form = document.getElementById('nexviaPopupForm');
        var nameInput = document.getElementById('nexviaLeadName');
        var mobileInput = document.getElementById('nexviaLeadMobile');
        var emailInput = document.getElementById('nexviaLeadEmail');
        var nameError = document.getElementById('nexviaNameError');
        var mobileError = document.getElementById('nexviaMobileError');
        var emailError = document.getElementById('nexviaEmailError');
        var submitBtn = document.getElementById('nexviaPopupSubmitBtn');
        var btnSpinner = document.getElementById('nexviaBtnSpinner');
        var btnText = document.getElementById('nexviaBtnText');

        // Close on close button click
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                dismissPopup(overlay);
            });
        }

        // Close on backdrop click (outside card)
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                dismissPopup(overlay);
            }
        });

        // Close on ESC key
        var onKeyDown = function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                dismissPopup(overlay);
                document.removeEventListener('keydown', onKeyDown);
            }
        };
        document.addEventListener('keydown', onKeyDown);

        // Restrict mobile input to digits only
        if (mobileInput) {
            mobileInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
                if (mobileError) mobileError.classList.remove('visible');
                this.classList.remove('is-invalid');
            });
        }

        if (nameInput) {
            nameInput.addEventListener('input', function () {
                if (nameError) nameError.classList.remove('visible');
                this.classList.remove('is-invalid');
            });
        }

        if (emailInput) {
            emailInput.addEventListener('input', function () {
                if (emailError) emailError.classList.remove('visible');
                this.classList.remove('is-invalid');
            });
        }

        // Form Submit
        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                var nameVal = (nameInput.value || '').trim();
                var mobileVal = (mobileInput.value || '').trim();
                var emailVal = (emailInput && emailInput.value) ? emailInput.value.trim() : '';

                var hasError = false;

                if (!nameVal || nameVal.length < 2) {
                    nameInput.classList.add('is-invalid');
                    nameError.textContent = 'Please enter your name (at least 2 letters)';
                    nameError.classList.add('visible');
                    hasError = true;
                }

                var mobileRegex = /^[6-9]\d{9}$/;
                if (!mobileVal || !mobileRegex.test(mobileVal)) {
                    mobileInput.classList.add('is-invalid');
                    mobileError.textContent = 'Please enter a valid 10-digit mobile number';
                    mobileError.classList.add('visible');
                    hasError = true;
                }

                // Optional email validation: only validate format if user entered something
                if (emailVal) {
                    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(emailVal)) {
                        emailInput.classList.add('is-invalid');
                        emailError.textContent = 'Please enter a valid email address';
                        emailError.classList.add('visible');
                        hasError = true;
                    }
                }

                if (hasError) return;

                // Loading State
                submitBtn.disabled = true;
                btnSpinner.style.display = 'inline-block';
                btnText.textContent = 'Submitting...';

                var payload = {
                    name: nameVal,
                    mobile: mobileVal,
                    email: emailVal || null,
                    source_url: window.location.href
                };

                fetch(apiBaseUrl + '/api/popup-leads', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { ok: res.ok, status: res.status, data: data };
                    });
                })
                .then(function (res) {
                    if (res.ok && res.data && res.data.status !== false) {
                        // Mark as submitted so popup never reappears for this user
                        localStorage.setItem(STORAGE_SUBMITTED_KEY, 'true');

                        // Show Success State
                        document.getElementById('nexviaPopupFormContainer').style.display = 'none';
                        var successContainer = document.getElementById('nexviaPopupSuccessContainer');
                        if (successContainer) {
                            if (res.data.message) {
                                document.getElementById('nexviaSuccessMsgText').textContent = res.data.message;
                            }
                            successContainer.style.display = 'block';
                        }

                        // Auto-close after 3 seconds
                        setTimeout(function () {
                            dismissPopup(overlay, false);
                        }, 3200);
                    } else {
                        var errMsg = (res.data && res.data.message) ? res.data.message : 'Submission failed. Please check your details.';
                        mobileError.textContent = errMsg;
                        mobileError.classList.add('visible');
                        submitBtn.disabled = false;
                        btnSpinner.style.display = 'none';
                        btnText.textContent = config.button_text;
                    }
                })
                .catch(function (err) {
                    console.error('[Nexvia Popup Error]', err);
                    mobileError.textContent = 'Connection error. Please try again.';
                    mobileError.classList.add('visible');
                    submitBtn.disabled = false;
                    btnSpinner.style.display = 'none';
                    btnText.textContent = config.button_text;
                });
            });
        }
    }

    function dismissPopup(overlay, setQuietPeriod) {
        if (setQuietPeriod !== false) {
            // Quiet period: don't show popup again for reappear_hours (default 24h)
            var hours = parseInt(config.reappear_hours, 10) || 24;
            var quietUntil = Date.now() + (hours * 60 * 60 * 1000);
            localStorage.setItem(STORAGE_DISMISSED_KEY, quietUntil.toString());
        }

        overlay.classList.remove('active');
        setTimeout(function () {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 400);
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
})();

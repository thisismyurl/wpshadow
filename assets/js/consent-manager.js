/**
 * Cookie Consent Manager
 *
 * Manages cookie consent preferences and banner display.
 * No external dependencies - pure vanilla JavaScript.
 *
 * @package WPShadow\CoreSupport
 */

(function() {
	'use strict';

	// Consent manager object
	var ConsentManager = {
		
		// Check if consent has been given (accepted or custom)
		hasConsent: function() {
			var status = localStorage.getItem('wpshadow_cookie_consent');
			return status === 'accepted' || status === 'custom';
		},

		// Check if consent has been rejected
		hasRejected: function() {
			return localStorage.getItem('wpshadow_cookie_consent') === 'rejected';
		},

		// Get specific consent preferences
		getPreferences: function() {
			var prefs = localStorage.getItem('wpshadow_cookie_preferences');
			if (!prefs) {
				return {
					essential: true,
					analytics: false,
					marketing: false
				};
			}
			try {
				return JSON.parse(prefs);
			} catch (e) {
				return {
					essential: true,
					analytics: false,
					marketing: false
				};
			}
		},

		// Set consent status
		setConsent: function(status) {
			localStorage.setItem('wpshadow_cookie_consent', status);
			
			// Set cookie for server-side detection with secure flag if HTTPS
			var expiryDate = new Date();
			expiryDate.setFullYear(expiryDate.getFullYear() + 1);
			var cookieStr = 'wpshadow_cookie_consent=' + status + '; expires=' + expiryDate.toUTCString() + '; path=/; SameSite=Lax';
			
			// Add Secure flag if available from localized data or detect HTTPS
			if ((window.wpsConsentData && window.wpsConsentData.isSecure) || window.location.protocol === 'https:') {
				cookieStr += '; Secure';
			}
			
			document.cookie = cookieStr;
		},

		// Save specific preferences
		savePreferences: function(prefs) {
			localStorage.setItem('wpshadow_cookie_preferences', JSON.stringify(prefs));
			
			// Set cookie for server-side detection with secure flag if HTTPS
			var expiryDate = new Date();
			expiryDate.setFullYear(expiryDate.getFullYear() + 1);
			var cookieStr = 'wpshadow_cookie_preferences=' + encodeURIComponent(JSON.stringify(prefs)) + '; expires=' + expiryDate.toUTCString() + '; path=/; SameSite=Lax';
			
			// Add Secure flag if available from localized data or detect HTTPS
			if ((window.wpsConsentData && window.wpsConsentData.isSecure) || window.location.protocol === 'https:') {
				cookieStr += '; Secure';
			}
			
			document.cookie = cookieStr;
		},

		// Accept all cookies
		acceptAll: function() {
			this.setConsent('accepted');
			this.savePreferences({
				essential: true,
				analytics: true,
				marketing: true
			});
			this.hideBanner();
			this.reloadIfNeeded();
		},

		// Reject non-essential cookies
		rejectAll: function() {
			this.setConsent('rejected');
			this.savePreferences({
				essential: true,
				analytics: false,
				marketing: false
			});
			this.hideBanner();
			this.reloadIfNeeded();
		},

		// Save custom preferences
		saveCustomPreferences: function() {
			var analytics = document.getElementById('wps-consent-analytics');
			var marketing = document.getElementById('wps-consent-marketing');

			var prefs = {
				essential: true,
				analytics: analytics ? analytics.checked : false,
				marketing: marketing ? marketing.checked : false
			};

			this.savePreferences(prefs);
			this.setConsent('custom');
			this.hideBanner();
			this.reloadIfNeeded();
		},

		// Hide the consent banner
		hideBanner: function() {
			var banner = document.getElementById('wps-consent-banner');
			if (banner) {
				banner.style.display = 'none';
				banner.classList.add('wps-consent-hidden');
			}
		},

		// Show the consent banner
		showBanner: function() {
			var banner = document.getElementById('wps-consent-banner');
			if (banner) {
				banner.style.display = 'block';
				banner.classList.remove('wps-consent-hidden');
			}
		},

		// Toggle preferences panel
		togglePreferences: function() {
			var panel = document.getElementById('wps-consent-preferences');
			if (panel) {
				var isVisible = panel.style.display !== 'none';
				panel.style.display = isVisible ? 'none' : 'block';

				// If showing preferences, load current settings
				if (!isVisible) {
					var prefs = this.getPreferences();
					var analyticsCheckbox = document.getElementById('wps-consent-analytics');
					var marketingCheckbox = document.getElementById('wps-consent-marketing');

					if (analyticsCheckbox) analyticsCheckbox.checked = prefs.analytics;
					if (marketingCheckbox) marketingCheckbox.checked = prefs.marketing;
				}
			}
		},

		// Reload page if there were blocked scripts
		reloadIfNeeded: function() {
			// Check if any scripts were blocked
			if (window.wpsBlockedScripts && window.wpsBlockedScripts.length > 0) {
				console.log('[WPS Consent] Reloading page to apply consent preferences...');
				// Small delay to let user see the acceptance
				setTimeout(function() {
					window.location.reload();
				}, 500);
			}
		},

		// Initialize the consent manager
		init: function() {
			// Check if consent decision already made (including custom)
			var consentStatus = localStorage.getItem('wpshadow_cookie_consent');
			if (consentStatus === 'accepted' || consentStatus === 'custom' || consentStatus === 'rejected') {
				this.hideBanner();
				return;
			}

			// Show the banner
			this.showBanner();

			// Set up event listeners
			var acceptBtn = document.getElementById('wps-consent-accept');
			var rejectBtn = document.getElementById('wps-consent-reject');
			var manageBtn = document.getElementById('wps-consent-manage');
			var savePrefsBtn = document.getElementById('wps-consent-save-prefs');

			if (acceptBtn) {
				acceptBtn.addEventListener('click', this.acceptAll.bind(this));
			}

			if (rejectBtn) {
				rejectBtn.addEventListener('click', this.rejectAll.bind(this));
			}

			if (manageBtn) {
				manageBtn.addEventListener('click', this.togglePreferences.bind(this));
			}

			if (savePrefsBtn) {
				savePrefsBtn.addEventListener('click', this.saveCustomPreferences.bind(this));
			}

			// Log blocked scripts if any
			if (window.wpsBlockedScripts && window.wpsBlockedScripts.length > 0) {
				console.log('[WPS Consent] Blocked items:', window.wpsBlockedScripts);
			}
		}
	};

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function() {
			ConsentManager.init();
		});
	} else {
		ConsentManager.init();
	}

	// Expose consent manager globally for debugging and external use
	window.wpsConsentManager = ConsentManager;

})();

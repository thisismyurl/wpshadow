<?php
/**
 * Feature: Cookie Consent Checks
 *
 * Flags third-party scripts that set cookies before consent,
 * provides local banner and auto-block rules (no remote CDNs).
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Consent_Checks
 *
 * Monitors third-party scripts for cookie usage and provides
 * a consent management system without external dependencies.
 */
final class WPSHADOW_Feature_Consent_Checks extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'consent-checks',
			'name'               => __( 'Cookie Consent Management', 'plugin-wpshadow' ),
			'description'        => __( 'Scans for tracking cookies, blocks nonessential scripts until visitors choose, and records consent so you can align with privacy rules. Provides an easy banner, lets visitors update choices, and keeps a clear audit trail. Helps protect visitor privacy while keeping analytics, ads, and embeds under your control with minimal setup.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'widget_label'       => __( 'Privacy & Compliance', 'plugin-wpshadow' ),
				'widget_description' => __( 'Tools for GDPR and privacy compliance', 'plugin-wpshadow' ),
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Hook early in head to inject blocking script before any other scripts load.
		add_action( 'wp_head', array( $this, 'inject_consent_checker' ), 1 );

		// Enqueue consent banner styles and scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_consent_assets' ), 1 );

		// Add consent banner to footer.
		add_action( 'wp_footer', array( $this, 'render_consent_banner' ), 1 );

		// Add settings page integration.
		add_filter( 'wpshadow_feature_settings_consent-checks', array( $this, 'render_settings' ) );
	}

	/**
	 * Inject early script to monitor and block cookies before consent.
	 *
	 * This runs before any other scripts to intercept cookie operations.
	 *
	 * @return void
	 */
	public function inject_consent_checker(): void {
		$blocked_patterns      = $this->get_blocked_patterns();
		$blocked_patterns_json = wp_json_encode( $blocked_patterns );
		?>
		<script id="wps-consent-checker">
		(function() {
			'use strict';
			
			// Check for existing consent (accepted or custom)
			var consentStatus = localStorage.getItem('wpshadow_cookie_consent');
			var hasConsent = consentStatus === 'accepted' || consentStatus === 'custom';
			
			if (hasConsent) {
				return; // User has already consented, allow all cookies
			}
			
			// Store blocked patterns
			var blockedPatterns = <?php echo $blocked_patterns_json; ?>;
			
			// Track blocked scripts for reporting
			window.wpsBlockedScripts = window.wpsBlockedScripts || [];
			
			// Override document.cookie setter to monitor and block unauthorized cookies
			var originalCookieDescriptor = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') || 
											Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');
			
			if (originalCookieDescriptor && originalCookieDescriptor.configurable) {
				Object.defineProperty(document, 'cookie', {
					get: function() {
						return originalCookieDescriptor.get.call(document);
					},
					set: function(value) {
						// Extract cookie name
						var cookieName = value.split('=')[0].trim();
						
						// Check if cookie matches blocked patterns
						var isBlocked = false;
						for (var i = 0; i < blockedPatterns.length; i++) {
							try {
								if (new RegExp(blockedPatterns[i], 'i').test(cookieName)) {
									isBlocked = true;
									break;
								}
							} catch (e) {
								// Invalid regex pattern, skip it
								console.warn('[WPS Consent] Invalid pattern:', blockedPatterns[i]);
							}
						}
						
						if (isBlocked) {
							console.warn('[WPS Consent] Blocked cookie before consent:', cookieName);
							window.wpsBlockedScripts.push({
								type: 'cookie',
								name: cookieName,
								timestamp: Date.now()
							});
							return; // Block the cookie
						}
						
						// Allow essential cookies (WordPress, session, etc.)
						if (cookieName.indexOf('wordpress_') === 0 || 
							cookieName.indexOf('wp-') === 0 || 
							cookieName === 'PHPSESSID') {
							return originalCookieDescriptor.set.call(document, value);
						}
						
						// Block all other cookies until consent
						console.warn('[WPS Consent] Blocked non-essential cookie before consent:', cookieName);
						window.wpsBlockedScripts.push({
							type: 'cookie',
							name: cookieName,
							timestamp: Date.now()
						});
					},
					configurable: true
				});
			}
		})();
		</script>
		<?php
	}

	/**
	 * Enqueue consent banner assets.
	 *
	 * @return void
	 */
	public function enqueue_consent_assets(): void {
		// Don't show banner in admin or if already consented.
		if ( is_admin() ) {
			return;
		}

		$plugin_url = defined( 'WPSHADOW_URL' ) ? WPSHADOW_URL : plugin_dir_url( dirname( __DIR__ ) );

		wp_enqueue_style(
			'wps-consent-banner',
			$plugin_url . 'assets/css/consent-banner.css',
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
			'wps-consent-manager',
			$plugin_url . 'assets/js/consent-manager.js',
			array(),
			'1.0.0',
			true
		);

		wp_localize_script(
			'wps-consent-manager',
			'wpsConsentData',
			array(
				'cookieDomain' => $this->get_cookie_domain(),
				'isSecure'     => is_ssl(),
				'i18n'         => array(
					'accepted' => __( 'Cookie preferences saved', 'plugin-wpshadow' ),
					'rejected' => __( 'Cookies blocked', 'plugin-wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render the consent banner HTML.
	 *
	 * @return void
	 */
	public function render_consent_banner(): void {
		// Don't show in admin.
		if ( is_admin() ) {
			return;
		}

		$banner_text = $this->get_setting( 'wpshadow_consent_banner_text', $this->get_default_banner_text( ) );
		$privacy_url = get_privacy_policy_url();
		?>
		<div id="wps-consent-banner" class="wps-consent-banner" style="display:none;" aria-live="polite" role="dialog" aria-labelledby="wps-consent-title">
			<div class="wps-consent-content">
				<h3 id="wps-consent-title"><?php echo esc_html__( 'Cookie Consent', 'plugin-wpshadow' ); ?></h3>
				<p><?php echo wp_kses_post( $banner_text ); ?></p>
				<?php if ( $privacy_url ) : ?>
					<p class="wps-consent-privacy-link">
						<a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener">
							<?php echo esc_html__( 'View Privacy Policy', 'plugin-wpshadow' ); ?>
						</a>
					</p>
				<?php endif; ?>
				<div class="wps-consent-actions">
					<button type="button" class="wps-consent-btn wps-consent-accept" id="wps-consent-accept">
						<?php echo esc_html__( 'Accept All Cookies', 'plugin-wpshadow' ); ?>
					</button>
					<button type="button" class="wps-consent-btn wps-consent-reject" id="wps-consent-reject">
						<?php echo esc_html__( 'Reject Non-Essential', 'plugin-wpshadow' ); ?>
					</button>
					<button type="button" class="wps-consent-btn wps-consent-manage" id="wps-consent-manage">
						<?php echo esc_html__( 'Manage Preferences', 'plugin-wpshadow' ); ?>
					</button>
				</div>
				<div id="wps-consent-preferences" class="wps-consent-preferences" style="display:none;">
					<h4><?php echo esc_html__( 'Cookie Preferences', 'plugin-wpshadow' ); ?></h4>
					<label>
						<input type="checkbox" checked disabled />
						<strong><?php echo esc_html__( 'Essential Cookies', 'plugin-wpshadow' ); ?></strong>
						<span class="wps-consent-desc"><?php echo esc_html__( 'Required for the website to function properly.', 'plugin-wpshadow' ); ?></span>
					</label>
					<label>
						<input type="checkbox" id="wps-consent-analytics" />
						<strong><?php echo esc_html__( 'Analytics Cookies', 'plugin-wpshadow' ); ?></strong>
						<span class="wps-consent-desc"><?php echo esc_html__( 'Help us understand how visitors use our website.', 'plugin-wpshadow' ); ?></span>
					</label>
					<label>
						<input type="checkbox" id="wps-consent-marketing" />
						<strong><?php echo esc_html__( 'Marketing Cookies', 'plugin-wpshadow' ); ?></strong>
						<span class="wps-consent-desc"><?php echo esc_html__( 'Used to track visitors across websites for marketing purposes.', 'plugin-wpshadow' ); ?></span>
					</label>
					<button type="button" class="wps-consent-btn wps-consent-save" id="wps-consent-save-prefs">
						<?php echo esc_html__( 'Save Preferences', 'plugin-wpshadow' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get blocked cookie patterns.
	 *
	 * @return array List of regex patterns for cookies to block.
	 */
	private function get_blocked_patterns(): array {
		$default_patterns = array(
			'^_ga',           // Google Analytics
			'^_gid',          // Google Analytics
			'^_gat',          // Google Analytics
			'^__utm',         // Google Analytics (legacy)
			'^_fbp',          // Facebook Pixel
			'^fr$',           // Facebook
			'^IDE$',          // DoubleClick
			'^_gcl_',         // Google Ads
			'^DSID$',         // DoubleClick
			'^NID$',          // Google
			'^ANID$',         // Google
			'^test_cookie$',  // DoubleClick
		);

		$custom_patterns = (array) $this->get_setting( 'wpshadow_consent_blocked_patterns', array( ) );
		$patterns        = array_merge( $default_patterns, $custom_patterns );

		/**
		 * Filter the list of blocked cookie patterns.
		 *
		 * @param array $patterns List of regex patterns.
		 */
		return apply_filters( 'wpshadow_consent_blocked_patterns', $patterns );
	}

	/**
	 * Get the cookie domain for consent cookie.
	 *
	 * @return string Cookie domain.
	 */
	private function get_cookie_domain(): string {
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );

		// Strip www. prefix if present for better cookie sharing across subdomains.
		if ( strpos( $domain, 'www.' ) === 0 ) {
			$domain = substr( $domain, 4 );
		}

		return $domain;
	}

	/**
	 * Get default banner text.
	 *
	 * @return string Default banner text.
	 */
	private function get_default_banner_text(): string {
		return __( 'This website uses cookies to improve your experience. We use essential cookies to make our site work. With your consent, we may also use non-essential cookies to improve user experience and analyze website traffic.', 'plugin-wpshadow' );
	}

	/**
	 * Render settings for this feature.
	 *
	 * @return void
	 */
	public function render_settings(): void {
		$banner_text          = $this->get_setting( 'wpshadow_consent_banner_text', $this->get_default_banner_text( ) );
		$custom_patterns      = $this->get_setting( 'wpshadow_consent_blocked_patterns', array( ) );
		$custom_patterns_text = is_array( $custom_patterns ) ? implode( "\n", $custom_patterns ) : '';
		?>
		<h3><?php echo esc_html__( 'Cookie Consent Settings', 'plugin-wpshadow' ); ?></h3>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="wpshadow_consent_banner_text"><?php echo esc_html__( 'Banner Text', 'plugin-wpshadow' ); ?></label>
				</th>
				<td>
					<textarea 
						id="wpshadow_consent_banner_text" 
						name="wpshadow_consent_banner_text" 
						rows="4" 
						cols="50" 
						class="large-text"
					><?php echo esc_textarea( $banner_text ); ?></textarea>
					<p class="description">
						<?php echo esc_html__( 'The message displayed to visitors in the consent banner.', 'plugin-wpshadow' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="wpshadow_consent_blocked_patterns"><?php echo esc_html__( 'Custom Blocked Patterns', 'plugin-wpshadow' ); ?></label>
				</th>
				<td>
					<textarea 
						id="wpshadow_consent_blocked_patterns" 
						name="wpshadow_consent_blocked_patterns" 
						rows="8" 
						cols="50" 
						class="large-text"
						placeholder="^custom_cookie_prefix&#10;^third_party_"
					><?php echo esc_textarea( $custom_patterns_text ); ?></textarea>
					<p class="description">
						<?php echo esc_html__( 'One regex pattern per line. These patterns will be blocked until consent is given. Default patterns include Google Analytics, Facebook Pixel, and DoubleClick.', 'plugin-wpshadow' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}
}

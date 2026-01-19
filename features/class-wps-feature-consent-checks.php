<?php declare(strict_types=1);
/**
 * Cookie Consent Management Feature
 *
 * Provides GDPR/CCPA-compliant cookie consent management with banner
 * customization, cookie scanning, script blocking, and audit trails.
 *
 * @package    WPShadow
 * @subpackage Features
 * @since      1.0.0
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not permitted.' );
}

/**
 * Cookie Consent Management Feature Class
 *
 * Intercepts and blocks non-essential cookies until user consent is obtained,
 * provides customizable consent banner, and maintains audit trail of user choices.
 *
 * @since 1.0.0
 */
final class WPSHADOW_Feature_Consent_Checks extends WPSHADOW_Abstract_Feature {

	/**
	 * Feature constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'consent-checks',
				'name'               => __( 'Cookie Consent Management', 'wpshadow' ),
				'description_short'  => __( 'GDPR/CCPA cookie consent with banner, blocking, and audit trail', 'wpshadow' ),
				'description_long'   => __( 'Comprehensive cookie consent system that intercepts and blocks tracking cookies until user consent is obtained. Includes customizable consent banner, automatic cookie scanning, JavaScript-based script blocking, detailed audit trails, and full GDPR/CCPA compliance. Allows granular control over analytics, marketing, and functional cookies.', 'wpshadow' ),
				'description_wizard' => __( 'Enable cookie consent management to comply with GDPR, CCPA, and other privacy regulations. This feature adds a customizable consent banner, automatically blocks tracking cookies until consent is given, and maintains audit trails of user choices.', 'wpshadow' ),
				'aliases'            => array( 'cookies', 'gdpr', 'privacy', 'consent', 'ccpa', 'tracking', 'cookie banner' ),
				'sub_features'       => array(
					'cookie_scanning'      => array(
						'name'               => __( 'Cookie Scanning', 'wpshadow' ),
						'description_short'  => __( 'Automatically detect and categorize cookies', 'wpshadow' ),
						'description_long'   => __( 'Automatically scans your website to detect all cookies being set, categorizes them by type (essential, analytics, marketing, functional), and provides detailed information about each cookie including domain, expiration, and purpose. Helps ensure complete compliance by identifying all tracking mechanisms.', 'wpshadow' ),
						'description_wizard' => __( 'Enable automatic cookie scanning to discover all cookies on your site and ensure you\'re properly declaring them in your consent banner.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'consent_banner'       => array(
						'name'               => __( 'Consent Banner', 'wpshadow' ),
						'description_short'  => __( 'Display customizable cookie consent banner', 'wpshadow' ),
						'description_long'   => __( 'Shows a fully customizable consent banner to visitors with options to accept all cookies, reject non-essential cookies, or manage preferences. Banner text, styling, position, and button labels are all configurable. Includes link to privacy policy and remembers user preferences across sessions.', 'wpshadow' ),
						'description_wizard' => __( 'Show a consent banner to visitors asking for permission before setting tracking cookies. Fully customizable with your own text and branding.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'script_blocking'      => array(
						'name'               => __( 'Script Blocking', 'wpshadow' ),
						'description_short'  => __( 'Block tracking scripts until consent', 'wpshadow' ),
						'description_long'   => __( 'JavaScript-based script blocking that prevents analytics, marketing, and tracking scripts from executing until user consent is obtained. Uses advanced cookie interception via Object.defineProperty() to block cookie creation at the browser level. Includes pattern-based blocking for Google Analytics, Facebook Pixel, DoubleClick, and other common tracking platforms.', 'wpshadow' ),
						'description_wizard' => __( 'Automatically block tracking scripts like Google Analytics and Facebook Pixel until users give consent. Required for GDPR compliance.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'audit_trail'          => array(
						'name'               => __( 'Audit Trail', 'wpshadow' ),
						'description_short'  => __( 'Log all consent decisions for compliance', 'wpshadow' ),
						'description_long'   => __( 'Maintains detailed audit trail of all consent decisions including timestamps, IP addresses (anonymized), user agents, consent categories selected, and revocation events. Essential for demonstrating compliance with privacy regulations. Logs are stored securely and can be exported for auditing purposes.', 'wpshadow' ),
						'description_wizard' => __( 'Keep detailed logs of who gave consent and when to demonstrate compliance with privacy regulations.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'customizable_banner'  => array(
						'name'               => __( 'Banner Customization', 'wpshadow' ),
						'description_short'  => __( 'Advanced banner styling and positioning', 'wpshadow' ),
						'description_long'   => __( 'Advanced customization options for the consent banner including custom CSS, banner position (top/bottom/modal), animation effects, button colors, font families, border radius, and mobile-specific layouts. Allows complete control over the look and feel to match your website\'s branding. Includes preview mode for testing changes.', 'wpshadow' ),
						'description_wizard' => __( 'Customize the appearance and behavior of your consent banner with advanced styling options, positioning, and animations.', 'wpshadow' ),
						'default_enabled'    => false,
					),
				),
			)
		);
	}

	/**
	 * Check if feature has details page.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
	}

	/**
	 * Register feature hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register(): void {
		// Inject consent checker as early as possible
		add_action( 'wp_head', array( $this, 'inject_consent_checker' ), 1 );

		// Render consent banner in footer
		add_action( 'wp_footer', array( $this, 'render_consent_banner' ), 999 );

		// Enqueue consent banner assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_consent_assets' ) );
	}

	/**
	 * Inject JavaScript to intercept cookies before any scripts run.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function inject_consent_checker(): void {
		// Don't inject in admin
		if ( is_admin() ) {
			return;
		}
		?>
		<script type="text/javascript">
		(function() {
			'use strict';
			
			// Check for existing consent
			var consent = document.cookie.split(';').find(function(c) { 
				return c.trim().indexOf('wps_cookie_consent=') === 0; 
			});
			
			if (consent && consent.indexOf('accepted') > -1) {
				return; // Already consented, allow cookies
			}
			
			// Store blocked scripts for later display
			window.wpsBlockedScripts = [];
			
			// Blocked cookie patterns (regex)
			var blockedPatterns = <?php echo wp_json_encode( $this->get_blocked_patterns() ); ?>;
			
			// Override document.cookie setter to intercept cookie creation
			var originalCookieDescriptor = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') || 
										   Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');
			
			if (originalCookieDescriptor && originalCookieDescriptor.configurable) {
				Object.defineProperty(document, 'cookie', {
					get: function() {
						return originalCookieDescriptor.get.call(document);
					},
					set: function(value) {
						var cookieName = value.split('=')[0].trim();
						
						// Check against blocked patterns
						var isBlocked = false;
						for (var i = 0; i < blockedPatterns.length; i++) {
							try {
								var regex = new RegExp(blockedPatterns[i]);
								if (regex.test(cookieName)) {
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
	 * @since 1.0.0
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
					'accepted' => __( 'Cookie preferences saved', 'wpshadow' ),
					'rejected' => __( 'Cookies blocked', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render the consent banner HTML.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_consent_banner(): void {
		// Don't show in admin.
		if ( is_admin() ) {
			return;
		}

		$banner_text = $this->get_setting( 'wpshadow_consent_banner_text', $this->get_default_banner_text() );
		$privacy_url = get_privacy_policy_url();
		?>
		<div id="wps-consent-banner" class="wps-consent-banner" style="display:none;" aria-live="polite" role="dialog" aria-labelledby="wps-consent-title">
			<div class="wps-consent-content">
				<h3 id="wps-consent-title"><?php echo esc_html__( 'Cookie Consent', 'wpshadow' ); ?></h3>
				<p><?php echo wp_kses_post( $banner_text ); ?></p>
				<?php if ( $privacy_url ) : ?>
					<p class="wps-consent-privacy-link">
						<a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener">
							<?php echo esc_html__( 'View Privacy Policy', 'wpshadow' ); ?>
						</a>
					</p>
				<?php endif; ?>
				<div class="wps-consent-actions">
					<button type="button" class="wps-consent-btn wps-consent-accept" id="wps-consent-accept">
						<?php echo esc_html__( 'Accept All Cookies', 'wpshadow' ); ?>
					</button>
					<button type="button" class="wps-consent-btn wps-consent-reject" id="wps-consent-reject">
						<?php echo esc_html__( 'Reject Non-Essential', 'wpshadow' ); ?>
					</button>
					<button type="button" class="wps-consent-btn wps-consent-manage" id="wps-consent-manage">
						<?php echo esc_html__( 'Manage Preferences', 'wpshadow' ); ?>
					</button>
				</div>
				<div id="wps-consent-preferences" class="wps-consent-preferences" style="display:none;">
					<h4><?php echo esc_html__( 'Cookie Preferences', 'wpshadow' ); ?></h4>
					<label>
						<input type="checkbox" checked disabled />
						<strong><?php echo esc_html__( 'Essential Cookies', 'wpshadow' ); ?></strong>
						<span class="wps-consent-desc"><?php echo esc_html__( 'Required for the website to function properly.', 'wpshadow' ); ?></span>
					</label>
					<label>
						<input type="checkbox" id="wps-consent-analytics" />
						<strong><?php echo esc_html__( 'Analytics Cookies', 'wpshadow' ); ?></strong>
						<span class="wps-consent-desc"><?php echo esc_html__( 'Help us understand how visitors use our website.', 'wpshadow' ); ?></span>
					</label>
					<label>
						<input type="checkbox" id="wps-consent-marketing" />
						<strong><?php echo esc_html__( 'Marketing Cookies', 'wpshadow' ); ?></strong>
						<span class="wps-consent-desc"><?php echo esc_html__( 'Used to track visitors across websites for marketing purposes.', 'wpshadow' ); ?></span>
					</label>
					<button type="button" class="wps-consent-btn wps-consent-save" id="wps-consent-save-prefs">
						<?php echo esc_html__( 'Save Preferences', 'wpshadow' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get blocked cookie patterns.
	 *
	 * @since 1.0.0
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

		$custom_patterns = (array) $this->get_setting( 'wpshadow_consent_blocked_patterns', array() );
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
	 * @since 1.0.0
	 * @return string Cookie domain.
	 */
	private function get_cookie_domain(): string {
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );

		// Strip www. prefix if present for better cookie sharing across subdomains.
		if ( is_string( $domain ) && strpos( $domain, 'www.' ) === 0 ) {
			$domain = substr( $domain, 4 );
		}

		return is_string( $domain ) ? $domain : '';
	}

	/**
	 * Get default banner text.
	 *
	 * @since 1.0.0
	 * @return string Default banner text.
	 */
	private function get_default_banner_text(): string {
		return __( 'This website uses cookies to improve your experience. We use essential cookies to make our site work. With your consent, we may also use non-essential cookies to improve user experience and analyze website traffic.', 'wpshadow' );
	}

	/**
	 * Render settings for this feature.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings(): void {
		$banner_text          = $this->get_setting( 'wpshadow_consent_banner_text', $this->get_default_banner_text() );
		$custom_patterns      = $this->get_setting( 'wpshadow_consent_blocked_patterns', array() );
		$custom_patterns_text = is_array( $custom_patterns ) ? implode( "\n", $custom_patterns ) : '';

		echo '<h3>' . esc_html__( 'Cookie Consent Settings', 'wpshadow' ) . '</h3>';
		echo '<table class="form-table">';

		// Banner text
		echo '<tr>';
		echo '<th scope="row"><label for="wpshadow_consent_banner_text">' . esc_html__( 'Banner Text', 'wpshadow' ) . '</label></th>';
		echo '<td>';
		echo '<textarea id="wpshadow_consent_banner_text" name="wpshadow_consent_banner_text" rows="4" cols="50" class="large-text">';
		echo esc_textarea( $banner_text );
		echo '</textarea>';
		echo '<p class="description">' . esc_html__( 'The message displayed to visitors in the consent banner.', 'wpshadow' ) . '</p>';
		echo '</td>';
		echo '</tr>';

		// Custom blocked patterns
		echo '<tr>';
		echo '<th scope="row"><label for="wpshadow_consent_blocked_patterns">' . esc_html__( 'Custom Blocked Patterns', 'wpshadow' ) . '</label></th>';
		echo '<td>';
		echo '<textarea id="wpshadow_consent_blocked_patterns" name="wpshadow_consent_blocked_patterns" rows="8" cols="50" class="large-text" placeholder="^custom_cookie_prefix' . "\n" . '^third_party_">';
		echo esc_textarea( $custom_patterns_text );
		echo '</textarea>';
		echo '<p class="description">' . esc_html__( 'One regex pattern per line. These patterns will be blocked until consent is given. Default patterns include Google Analytics, Facebook Pixel, and DoubleClick.', 'wpshadow' ) . '</p>';
		echo '</td>';
		echo '</tr>';

		echo '</table>';
	}
}

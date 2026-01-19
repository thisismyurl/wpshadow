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
				'name'               => __( 'Cookie Privacy Manager', 'wpshadow' ),
				'description_short'  => __( 'Ask visitors for permission before tracking their activity', 'wpshadow' ),
				'description_long'   => __( 'Shows visitors a friendly message asking if they agree to have their activity tracked. Automatically stops tracking tools until people say yes, keeping you compliant with privacy laws. The message box can be customized to match your website design, and you can track who agreed and when for your records.', 'wpshadow' ),
				'description_wizard' => __( 'Show a message asking visitors if they\'re okay with cookies being used to track their activity. Required by law in many countries to respect people\'s privacy choices.', 'wpshadow' ),
				'aliases'            => array( 'cookies', 'gdpr', 'privacy', 'consent', 'ccpa', 'tracking', 'cookie banner' ),
				'sub_features'       => array(
					'cookie_scanning'      => array(
						'name'               => __( 'Find All Cookies', 'wpshadow' ),
						'description_short'  => __( 'Discover what tracking tools your site uses', 'wpshadow' ),
						'description_long'   => __( 'Automatically checks your website to find all the tracking tools being used. Sorts them into groups (required for site to work, visitor statistics, advertising) and tells you what each one does. This helps make sure you\'re being honest with visitors about all the tracking happening on your site.', 'wpshadow' ),
						'description_wizard' => __( 'Let the plugin find all the cookies on your site automatically, so you know exactly what to tell visitors about.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'consent_banner'       => array(
						'name'               => __( 'Permission Message', 'wpshadow' ),
						'description_short'  => __( 'Show visitors a customizable message asking for permission', 'wpshadow' ),
						'description_long'   => __( 'Displays a message box to visitors with options to allow all tracking, decline extra tracking, or choose what they\'re comfortable with. You can change the words, colors, position on page, and button labels to match your site. Includes a link to your privacy page and remembers each visitor\'s choice.', 'wpshadow' ),
						'description_wizard' => __( 'Add a message box asking visitors if they\'re okay with tracking. You can customize the words and design to match your site.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'script_blocking'      => array(
						'name'               => __( 'Stop Tracking Until Permission', 'wpshadow' ),
						'description_short'  => __( 'Prevent tracking until visitors agree', 'wpshadow' ),
						'description_long'   => __( 'Automatically stops visitor statistics tools and advertising trackers from working until someone agrees. Blocks popular tracking services like Google Analytics and Facebook from collecting information. This is required by privacy laws in many places to give people control over their information.', 'wpshadow' ),
						'description_wizard' => __( 'Stop all tracking tools from working until visitors say it\'s okay. Required by law to respect people\'s privacy.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'audit_trail'          => array(
						'name'               => __( 'Keep Records', 'wpshadow' ),
						'description_short'  => __( 'Save proof that you asked for permission', 'wpshadow' ),
						'description_long'   => __( 'Keeps detailed records of when visitors gave permission, including the date, time, and what they agreed to. Useful if you ever need to prove you followed privacy laws. The information is stored safely and can be downloaded if needed for legal purposes.', 'wpshadow' ),
						'description_wizard' => __( 'Save records showing when visitors gave permission, in case you need proof for privacy law compliance.', 'wpshadow' ),
						'default_enabled'    => false,
					),
					'customizable_banner'  => array(
						'name'               => __( 'Design Your Message', 'wpshadow' ),
						'description_short'  => __( 'Make the message box match your website style', 'wpshadow' ),
						'description_long'   => __( 'Extra options to make the permission message look exactly how you want. Change colors, fonts, position on screen, add animations, and adjust for mobile phones. Complete control to match your website\'s look and feel. Includes a preview so you can test changes before visitors see them.', 'wpshadow' ),
						'description_wizard' => __( 'Advanced options to make the permission message match your website\'s colors, fonts, and style perfectly.', 'wpshadow' ),
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

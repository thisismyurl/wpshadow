<?php declare(strict_types=1);
/**
 * Feature: Cookie Consent Management
 *
 * Monitors third-party scripts for cookie usage, provides local consent banner
 * with no external dependencies.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Consent_Checks extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'consent-checks',
			'name'        => __( 'Cookie Consent Management', 'wpshadow' ),
			'description' => __( 'Provide GDPR compliance with consent banner and cookie blocking.', 'wpshadow' ),
			'sub_features' => array(
				'cookie_scanning'      => __( 'Cookie Scanning', 'wpshadow' ),
				'consent_banner'       => __( 'Consent Banner', 'wpshadow' ),
				'script_blocking'      => __( 'Auto-Block Scripts', 'wpshadow' ),
				'audit_trail'          => __( 'Consent Audit Trail', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'cookie_scanning'      => true,
			'consent_banner'       => true,
			'script_blocking'      => true,
			'audit_trail'          => false,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Inject consent checking script early
		if ( $this->is_sub_feature_enabled( 'script_blocking', true ) ) {
			add_action( 'wp_head', array( $this, 'inject_consent_blocker' ), 1 );
		}

		// Render consent banner
		if ( $this->is_sub_feature_enabled( 'consent_banner', true ) ) {
			add_action( 'wp_footer', array( $this, 'render_consent_banner' ), 1 );
		}

		// Audit trail logging
		if ( $this->is_sub_feature_enabled( 'audit_trail', false ) ) {
			add_action( 'wp_footer', array( $this, 'log_consent_audit' ), 999 );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Inject early consent blocker script.
	 */
	public function inject_consent_blocker(): void {
		$blocked_cookies = wp_json_encode( $this->get_blocked_cookie_patterns() );
		?>
		<script id="wps-consent-blocker">
		(function() {
			'use strict';
			window.wpsBlockedScripts = [];
			var consentStatus = localStorage.getItem('wpshadow_cookie_consent') || 'pending';
			if (consentStatus === 'accepted') return;
			
			var blockedPatterns = <?php echo $blocked_cookies; ?>;
			var originalDescriptor = Object.getOwnPropertyDescriptor(Document.prototype, 'cookie');
			
			if (originalDescriptor && originalDescriptor.configurable) {
				Object.defineProperty(document, 'cookie', {
					get: function() {
						return originalDescriptor.get.call(document);
					},
					set: function(value) {
						var cookieName = value.split('=')[0].trim();
						for (var i = 0; i < blockedPatterns.length; i++) {
							try {
								if (new RegExp(blockedPatterns[i], 'i').test(cookieName)) {
									window.wpsBlockedScripts.push({type: 'cookie', name: cookieName, time: Date.now()});
									return;
								}
							} catch(e) {}
						}
						if (cookieName.indexOf('wordpress_') === 0 || cookieName.indexOf('wp-') === 0 || cookieName === 'PHPSESSID') {
							originalDescriptor.set.call(document, value);
						}
					},
					configurable: true
				});
			}
		})();
		</script>
		<?php
	}

	/**
	 * Render consent banner HTML.
	 */
	public function render_consent_banner(): void {
		if ( is_admin() ) {
			return;
		}
		
		$banner_text = $this->get_setting( 'banner_text', $this->get_default_banner_text() );
		$privacy_url = get_privacy_policy_url();
		?>
		<div id="wps-consent-banner" class="wps-consent-banner" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#222;color:#fff;padding:20px;text-align:center;font-family:sans-serif;">
			<div style="max-width:800px;margin:0 auto;">
				<p><?php echo wp_kses_post( $banner_text ); ?></p>
				<?php if ( $privacy_url ) : ?>
					<p><a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener" style="color:#0073aa;"><?php esc_html_e( 'Privacy Policy', 'wpshadow' ); ?></a></p>
				<?php endif; ?>
				<div>
					<button id="wps-consent-accept" style="margin:5px;padding:8px 16px;background:#0073aa;color:#fff;border:none;cursor:pointer;"><?php esc_html_e( 'Accept All', 'wpshadow' ); ?></button>
					<button id="wps-consent-reject" style="margin:5px;padding:8px 16px;background:#666;color:#fff;border:none;cursor:pointer;"><?php esc_html_e( 'Reject', 'wpshadow' ); ?></button>
				</div>
			</div>
		</div>
		<script>
		(function() {
			var banner = document.getElementById('wps-consent-banner');
			var consent = localStorage.getItem('wpshadow_cookie_consent');
			
			if (consent === null) {
				banner.style.display = 'block';
			}
			
			document.getElementById('wps-consent-accept').addEventListener('click', function() {
				localStorage.setItem('wpshadow_cookie_consent', 'accepted');
				banner.style.display = 'none';
			});
			
			document.getElementById('wps-consent-reject').addEventListener('click', function() {
				localStorage.setItem('wpshadow_cookie_consent', 'rejected');
				banner.style.display = 'none';
			});
		})();
		</script>
		<?php
	}

	/**
	 * Log consent audit trail.
	 */
	public function log_consent_audit(): void {
		?>
		<script>
		(function() {
			if (window.wpsBlockedScripts && window.wpsBlockedScripts.length > 0) {
				var audit = {
					timestamp: new Date().toISOString(),
					blockedCount: window.wpsBlockedScripts.length,
					consent: localStorage.getItem('wpshadow_cookie_consent')
				};
				var logs = JSON.parse(localStorage.getItem('wpshadow_consent_logs') || '[]');
				logs.push(audit);
				if (logs.length > 100) logs.shift();
				localStorage.setItem('wpshadow_consent_logs', JSON.stringify(logs));
			}
		})();
		</script>
		<?php
	}

	/**
	 * Get blocked cookie patterns.
	 */
	private function get_blocked_cookie_patterns(): array {
		return array(
			'^_ga',        // Google Analytics
			'^_gid',       // Google Analytics
			'^_gat',       // Google Analytics
			'^__utm',      // Google Analytics legacy
			'^_fbp',       // Facebook Pixel
			'^fr$',        // Facebook
			'^IDE$',       // DoubleClick
			'^_gcl_',      // Google Ads
			'^DSID$',      // DoubleClick
			'^NID$',       // Google
			'^ANID$',      // Google
		);
	}

	/**
	 * Get default banner text.
	 */
	private function get_default_banner_text(): string {
		return __( 'This website uses cookies to improve your experience. We use essential cookies for site function and, with your consent, may use non-essential cookies for analytics and marketing.', 'wpshadow' );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['consent_checks'] = array(
			'label'  => __( 'Cookie Consent', 'wpshadow' ),
			'test'   => array( $this, 'test_consent' ),
		);

		return $tests;
	}

	public function test_consent(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Cookie Consent Management', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Cookie consent management can help ensure GDPR compliance.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'consent_checks',
			);
		}

		$enabled_count = 0;
		$subs = array( 'cookie_scanning', 'consent_banner', 'script_blocking', 'audit_trail' );
		foreach ( $subs as $sub ) {
			if ( $this->is_sub_feature_enabled( $sub, false ) ) {
				$enabled_count++;
			}
		}

		return array(
			'label'       => __( 'Cookie Consent Management', 'wpshadow' ),
			'status'      => $enabled_count >= 2 ? 'good' : 'recommended',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf( __( '%d of 4 consent sub-features enabled.', 'wpshadow' ), $enabled_count ),
			'actions'     => '',
			'test'        => 'consent_checks',
		);
	}
}

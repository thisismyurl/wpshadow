<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Iframe_Busting extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'iframe-busting',
				'name'               => __( 'Iframe Busting (Clickjacking Protection)', 'wpshadow' ),
				'description_short'  => __( 'Stop hackers from hiding your site in a malicious frame to trick your visitors.', 'wpshadow' ),
				'description_long'   => __( 'Protect your website from clickjacking attacks where malicious actors try to trick your visitors by loading your site inside an invisible iframe. This feature deploys multiple layers of defense including modern Content-Security-Policy headers, legacy X-Frame-Options headers for older browsers, and JavaScript frame-busting code as a final fallback. You can configure whether to completely block all framing, allow same-origin framing only, or whitelist specific trusted domains.', 'wpshadow' ),
				'description_wizard' => __( 'Clickjacking is when hackers load your site in an invisible frame over a fake page to trick visitors into clicking things they didn\'t intend to. This can lead to stolen logins, unwanted actions, or data breaches. Enable this feature to block all framing attempts with industry-standard security headers and fallback protection for older browsers.', 'wpshadow' ),
				'description'        => __( 'Prevent clickjacking attacks with multi-layer frame protection.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-shield-alt',
				'category'           => 'security',
				'priority'           => 5,
				'aliases'            => array(
					'clickjacking',
					'clickjacking protection',
					'iframe protection',
					'frame busting',
					'x-frame-options',
					'csp header',
					'embedding protection',
				),
				'sub_features'       => array(
					'csp_header'         => array(
						'name'                => __( 'Content-Security-Policy Header', 'wpshadow' ),
						'description_short'   => __( 'Modern browser frame-ancestors directive protection.', 'wpshadow' ),
						'description_long'    => __( 'Adds the Content-Security-Policy frame-ancestors directive, the modern standard for controlling who can embed your site in frames. This is supported by all current browsers and provides the most flexible and powerful protection. The CSP header can block all framing, allow same-origin only, or permit specific trusted domains through a whitelist.', 'wpshadow' ),
						'description_wizard'  => __( 'CSP headers are the modern way browsers block clickjacking. All current browsers support this, making it the strongest defense against framing attacks. This should always be enabled for maximum protection.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'xfo_header'         => array(
						'name'                => __( 'X-Frame-Options Header', 'wpshadow' ),
						'description_short'   => __( 'Legacy browser clickjacking protection header.', 'wpshadow' ),
						'description_long'    => __( 'Adds the X-Frame-Options header for older browser compatibility. While superseded by CSP frame-ancestors in modern browsers, this header provides essential protection for visitors using older browsers like Internet Explorer 11 or older versions of Safari. Supports DENY (block all framing) and SAMEORIGIN (allow same-domain framing) modes.', 'wpshadow' ),
						'description_wizard'  => __( 'Old browsers don\'t understand modern CSP headers. The X-Frame-Options header ensures visitors with older browsers are still protected. Keep this enabled unless you\'re certain all visitors use current browsers.', 'wpshadow' ),
						'default_enabled'     => true,
					),
					'js_framebuster'     => array(
						'name'                => __( 'JavaScript Frame-Buster', 'wpshadow' ),
						'description_short'   => __( 'JavaScript fallback frame-breaking code.', 'wpshadow' ),
						'description_long'    => __( 'Injects JavaScript code that detects if your site is loaded in a frame and breaks out to the top window. This is a fallback protection layer for extremely old browsers that don\'t support security headers, or for situations where headers are stripped by proxies. The script is lightweight and loads before any other page content for maximum effectiveness.', 'wpshadow' ),
						'description_wizard'  => __( 'Some very old browsers or unusual network configurations might not respect security headers. JavaScript frame-busting provides a last line of defense by detecting and breaking out of frames directly in the browser. Adds minimal overhead.', 'wpshadow' ),
						'default_enabled'     => true,
					),
				),
			)
		);

		$this->log_activity( 'feature_initialized', 'Iframe Busting feature initialized', 'info' );
	}

	public function has_details_page(): bool {
		return true;
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( get_option( 'wpshadow_iframe-busting_csp_header', true ) || get_option( 'wpshadow_iframe-busting_xfo_header', true ) ) {
			add_action( 'send_headers', array( $this, 'add_security_headers' ) );
		}

		if ( get_option( 'wpshadow_iframe-busting_js_framebuster', true ) ) {
			add_action( 'wp_head', array( $this, 'add_frame_buster_script' ), 1 );
			add_action( 'admin_head', array( $this, 'add_frame_buster_script' ), 1 );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	public function add_security_headers(): void {

		if ( headers_sent() ) {
			return;
		}

		$policy = 'sameorigin'; 

		if ( get_option( 'wpshadow_iframe-busting_csp_header', true ) ) {
			$csp_value = $this->get_csp_value( $policy );
			if ( ! empty( $csp_value ) ) {
				header( 'Content-Security-Policy: ' . $csp_value, true );
			}
		}

		if ( get_option( 'wpshadow_iframe-busting_xfo_header', true ) ) {
			$xfo_value = $this->get_xfo_value( $policy );
			if ( ! empty( $xfo_value ) ) {
				header( 'X-Frame-Options: ' . $xfo_value, true );
			}
		}
	}

	private function get_csp_value( string $policy ): string {
		switch ( $policy ) {
			case 'deny':
				return "frame-ancestors 'none'";

			case 'sameorigin':
				return "frame-ancestors 'self'";

			default:
				return "frame-ancestors 'self'";
		}
	}

	private function get_xfo_value( string $policy ): string {
		switch ( $policy ) {
			case 'deny':
				return 'DENY';

			case 'sameorigin':
				return 'SAMEORIGIN';

			default:
				return 'SAMEORIGIN';
		}
	}

	public function add_frame_buster_script(): void {
		?>
		<script type="text/javascript">

		(function() {
			if (top !== self) {
				top.location.href = self.location.href;
			}
		})();
		</script>
		<?php
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_iframe_busting'] = array(
			'label' => __( 'Clickjacking Protection', 'wpshadow' ),
			'test'  => array( $this, 'test_iframe_busting' ),
		);

		return $tests;
	}

	public function test_iframe_busting(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Clickjacking Protection', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'orange',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Clickjacking protection is not enabled. Your site could be vulnerable to framing attacks where malicious actors embed your site in invisible iframes to trick visitors.', 'wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_iframe_busting',
			);
		}

		$protection_layers = 0;

		if ( get_option( 'wpshadow_iframe-busting_csp_header', true ) ) {
			++$protection_layers;
		}
		if ( get_option( 'wpshadow_iframe-busting_xfo_header', true ) ) {
			++$protection_layers;
		}
		if ( get_option( 'wpshadow_iframe-busting_js_framebuster', true ) ) {
			++$protection_layers;
		}

		return array(
			'label'       => __( 'Clickjacking Protection', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Security', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(

					__( 'Clickjacking protection is active with %d protection layers enabled, preventing your site from being embedded in malicious iframes.', 'wpshadow' ),
					$protection_layers
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_iframe_busting',
		);
	}
}

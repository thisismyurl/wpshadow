<?php
/**
 * WCAG 2.2.1 Timing Adjustable Diagnostic
 *
 * Validates that time limits can be extended or disabled.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Timing Adjustable Diagnostic Class
 *
 * Checks for adjustable time limits (WCAG 2.2.1 Level A).
 *
 * @since 0.6093.1200
 */
class Diagnostic_WCAG_Timing_Adjustable extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-timing-adjustable';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Timing Adjustable (WCAG 2.2.1)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that time limits can be turned off, adjusted, or extended';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check WordPress login session timeout.
		$timeout = apply_filters( 'auth_cookie_expiration', 172800 ); // Default 2 days.

		if ( $timeout < 3600 ) {
			$issues[] = sprintf(
				/* translators: %d: timeout in minutes */
				__( 'Login session timeout is very short (%d minutes). Users with disabilities may need more time', 'wpshadow' ),
				floor( $timeout / 60 )
			);
		}

		// Check theme header for meta refresh (auto-redirect).
		$theme_header = get_template_directory() . '/header.php';
		if ( file_exists( $theme_header ) ) {
			$content = file_get_contents( $theme_header );

			if ( preg_match( '/<meta[^>]*http-equiv=["\']refresh["\']/', $content ) ) {
				$issues[] = __( 'Theme uses meta refresh for auto-redirect. Users should control timing or be able to disable it', 'wpshadow' );
			}
		}

		// Check for JavaScript-based redirects in theme.
		$js_files = array();
		$theme_js = get_template_directory() . '/js';

		if ( is_dir( $theme_js ) ) {
			$files = glob( $theme_js . '/*.js' );
			if ( is_array( $files ) ) {
				$js_files = array_merge( $js_files, $files );
			}
		}

		// Also check common JS locations.
		$common_js_locations = array(
			get_template_directory() . '/assets/js',
			get_template_directory() . '/dist/js',
		);

		foreach ( $common_js_locations as $location ) {
			if ( is_dir( $location ) ) {
				$files = glob( $location . '/*.js' );
				if ( is_array( $files ) ) {
					$js_files = array_merge( $js_files, $files );
				}
			}
		}

		$has_timeout_redirect = false;
		foreach ( $js_files as $js_file ) {
			if ( ! file_exists( $js_file ) ) {
				continue;
			}

			$content = file_get_contents( $js_file );

			// Check for setTimeout redirects.
			if ( preg_match( '/setTimeout.*window\.location|setTimeout.*location\.href/', $content ) ) {
				$has_timeout_redirect = true;
				break;
			}
		}

		if ( $has_timeout_redirect ) {
			$issues[] = __( 'JavaScript-based automatic redirects detected. Provide controls to disable or extend timing', 'wpshadow' );
		}

		// Check for carousel/slider plugins with autoplay.
		$active_plugins   = get_option( 'active_plugins', array() );
		$carousel_plugins = array(
			'slider-revolution',
			'layerslider',
			'smart-slider',
			'metaslider',
			'soliloquy',
		);

		$has_carousel = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $carousel_plugins as $carousel_plugin ) {
				if ( strpos( $plugin, $carousel_plugin ) !== false ) {
					$has_carousel = true;
					break 2;
				}
			}
		}

		if ( $has_carousel ) {
			$issues[] = __( 'Carousel/slider plugin detected. Ensure it has pause controls and doesn\'t auto-advance indefinitely', 'wpshadow' );
		}

		// Check for countdown timer plugins.
		$countdown_plugins = array(
			'countdown',
			'timer',
		);

		$has_countdown = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $countdown_plugins as $countdown_plugin ) {
				if ( strpos( $plugin, $countdown_plugin ) !== false ) {
					$has_countdown = true;
					break 2;
				}
			}
		}

		if ( $has_countdown ) {
			$issues[] = __( 'Countdown timer plugin detected. Users should be able to extend time limits or disable them', 'wpshadow' );
		}

		// Check for e-commerce session timeouts.
		if ( class_exists( 'WooCommerce' ) ) {
			// WooCommerce cart session timeout.
			$cart_timeout = get_option( 'woocommerce_cart_session_timeout', 172800 );
			if ( $cart_timeout < 3600 ) {
				$issues[] = __( 'WooCommerce cart expires quickly. Users with disabilities need more time to complete checkout', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Time limits are like parking meters that expire too quickly. Some users need extra time to read, understand, or interact with content. People with cognitive disabilities, reading difficulties, or motor impairments often need 2-10 times longer than average users. Auto-advancing carousels, session timeouts, and timed redirects can lock them out before they finish. It\'s like being given a test but having it snatched away before you\'re done—frustrating and unfair.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-timing-adjustable?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

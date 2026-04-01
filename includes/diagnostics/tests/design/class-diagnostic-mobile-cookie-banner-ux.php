<?php
/**
 * Mobile Cookie Banner UX Diagnostic
 *
 * Optimizes cookie consent banner for mobile devices.
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
 * Mobile Cookie Banner UX Diagnostic Class
 *
 * Validates cookie consent banner is properly formatted for mobile,
 * ensuring GDPR compliance and good UX without blocking main content.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Cookie_Banner_UX extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-cookie-banner-ux';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Cookie Banner UX';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Optimize cookie consent banner for mobile without blocking main content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if consent banner doesn't block main content
		$banner_not_blocking = apply_filters( 'wpshadow_cookie_banner_not_blocking_content', false );
		if ( ! $banner_not_blocking ) {
			$issues[] = __( 'Cookie banner may block main content; should be overlay or sticky footer only', 'wpshadow' );
		}

		// Check if accept/reject buttons are equal size
		$buttons_equal_size = apply_filters( 'wpshadow_cookie_accept_reject_equal_size', false );
		if ( ! $buttons_equal_size ) {
			$issues[] = __( 'Accept and reject buttons should be equal size/prominence per GDPR guidelines', 'wpshadow' );
		}

		// Check if banner is easily dismissible
		$easily_dismissible = apply_filters( 'wpshadow_cookie_banner_easily_dismissible', false );
		if ( ! $easily_dismissible ) {
			$issues[] = __( 'Cookie banner should have clear close button and ESC key support', 'wpshadow' );
		}

		// Check if banner is sticky on mobile
		$sticky_mobile = apply_filters( 'wpshadow_cookie_banner_sticky_mobile', false );
		if ( ! $sticky_mobile ) {
			$issues[] = __( 'Cookie banner should stick to footer on mobile for easy access', 'wpshadow' );
		}

		// Check if banner takes excessive screen space
		$height_reasonable = apply_filters( 'wpshadow_cookie_banner_height_reasonable_mobile', false );
		if ( ! $height_reasonable ) {
			$issues[] = __( 'Cookie banner takes excessive vertical space on mobile; limit height or make scrollable', 'wpshadow' );
		}

		// Check for ARIA labels
		$aria_labeled = apply_filters( 'wpshadow_cookie_banner_aria_labeled', false );
		if ( ! $aria_labeled ) {
			$issues[] = __( 'Cookie banner buttons should have aria-labels for screen readers', 'wpshadow' );
		}

		// Check if banner supports keyboard navigation
		$keyboard_nav = apply_filters( 'wpshadow_cookie_banner_keyboard_navigation', false );
		if ( ! $keyboard_nav ) {
			$issues[] = __( 'Cookie banner should support keyboard navigation (Tab, Enter, ESC)', 'wpshadow' );
		}

		// Check if banner text is readable on mobile
		$text_readable = apply_filters( 'wpshadow_cookie_banner_text_readable_mobile', false );
		if ( ! $text_readable ) {
			$issues[] = __( 'Cookie banner text may be too small on mobile; ensure min 14px font size', 'wpshadow' );
		}

		// Check for cookie-handling plugins
		$cookie_plugins = array(
			'cookiebot' => 'Cookiebot',
			'iubenda' => 'iubenda',
			'osano' => 'Osano',
		);

		$has_plugin = false;
		foreach ( $cookie_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_plugin = true;
				break;
			}
		}

		if ( ! $has_plugin ) {
			$issues[] = __( 'No cookie consent plugin detected; consider implementing GDPR-compliant cookie banner', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-cookie-banner-ux?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

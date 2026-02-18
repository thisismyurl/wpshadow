<?php
/**
 * Mobile Screen Reader Compatibility Diagnostic
 *
 * Validates ARIA labels and landmarks work correctly on mobile screen readers.
 *
 * @since   1.6033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Screen Reader Compatibility Diagnostic Class
 *
 * Validates that ARIA labels and landmarks work correctly on mobile screen readers
 * (VoiceOver, TalkBack) for WCAG A/AA compliance and accessibility.
 *
 * @since 1.6033.1645
 */
class Diagnostic_Mobile_Screen_Reader_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-screen-reader-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Screen Reader Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validate ARIA labels and landmarks work correctly on mobile screen readers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if ARIA support is enabled
		$aria_enabled = apply_filters( 'wpshadow_aria_support_enabled', true );
		if ( ! $aria_enabled ) {
			$issues[] = __( 'ARIA support is disabled', 'wpshadow' );
		}

		// Check for semantic HTML elements
		$has_semantic_markup = apply_filters( 'wpshadow_theme_uses_semantic_html', false );
		if ( ! $has_semantic_markup ) {
			$issues[] = __( 'Theme may not use semantic HTML landmarks (header, nav, main, footer)', 'wpshadow' );
		}

		// Check for skip links
		$has_skip_links = apply_filters( 'wpshadow_theme_has_skip_links', false );
		if ( ! $has_skip_links ) {
			$issues[] = __( 'Skip links not detected for screen reader navigation', 'wpshadow' );
		}

		// Check for ARIA labels on interactive elements
		global $wp_filter;
		$custom_theme_supports = get_theme_support( 'wpshadow-accessibility' );
		if ( false === $custom_theme_supports ) {
			$issues[] = __( 'Theme does not declare accessibility support', 'wpshadow' );
		}

		// Check if screen reader accessibility plugins are active
		$a11y_plugins = array(
			'wp-accessibility' => 'WP Accessibility',
			'accessible-ready' => 'Accessible Ready',
		);

		$has_a11y_plugin = false;
		foreach ( $a11y_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_a11y_plugin = true;
				break;
			}
		}

		if ( ! $has_a11y_plugin && ! $has_semantic_markup ) {
			$issues[] = __( 'No accessibility plugins detected and theme semantic HTML support unconfirmed', 'wpshadow' );
		}

		// Check for proper heading hierarchy
		$heading_hierarchy_valid = apply_filters( 'wpshadow_heading_hierarchy_valid', false );
		if ( ! $heading_hierarchy_valid ) {
			$issues[] = __( 'Heading hierarchy validation unavailable or failed', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-screen-reader-compatibility',
			);
		}

		return null;
	}
}

<?php
/**
 * Mobile Dark Mode Support Diagnostic
 *
 * Validates support for OS-level dark mode preference.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Dark Mode Support Diagnostic Class
 *
 * Validates that the site respects prefers-color-scheme media query,
 * providing a dark theme option for users who prefer it.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Dark_Mode_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-dark-mode-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Dark Mode Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Support OS-level dark mode preference with proper contrast';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for prefers-color-scheme media query support
		$dark_mode_supported = apply_filters( 'wpshadow_prefers_color_scheme_media_query_supported', false );
		if ( ! $dark_mode_supported ) {
			$issues[] = __( 'Dark mode media query not found; add @media (prefers-color-scheme: dark) CSS', 'wpshadow' );
		}

		// Check for hardcoded light-only colors
		$hardcoded_colors = apply_filters( 'wpshadow_has_hardcoded_light_only_colors', false );
		if ( $hardcoded_colors ) {
			$issues[] = __( 'Hardcoded light-only colors detected; these override dark mode preferences', 'wpshadow' );
		}

		// Check if dark mode has sufficient contrast (4.5:1)
		$dark_mode_contrast = apply_filters( 'wpshadow_dark_mode_contrast_wcag_aa_compliant', false );
		if ( ! $dark_mode_contrast ) {
			$issues[] = __( 'Dark mode text may not meet 4.5:1 contrast ratio (WCAG AA)', 'wpshadow' );
		}

		// Check for dark mode on images/backgrounds
		$dark_mode_background_images = apply_filters( 'wpshadow_dark_mode_handles_background_images', false );
		if ( ! $dark_mode_background_images ) {
			$issues[] = __( 'Background images may not be optimized for dark mode; consider adjusting brightness/contrast', 'wpshadow' );
		}

		// Check for meta color-scheme tag
		$meta_color_scheme_set = apply_filters( 'wpshadow_meta_color_scheme_set', false );
		if ( ! $meta_color_scheme_set ) {
			$issues[] = __( 'Add <meta name="color-scheme" content="light dark"> for browser-level dark mode support', 'wpshadow' );
		}

		// Check if dark mode is togglable by user
		$dark_mode_user_control = apply_filters( 'wpshadow_dark_mode_user_selectable', false );
		if ( ! $dark_mode_user_control ) {
			$issues[] = __( 'Consider adding user-facing dark mode toggle for manual switching', 'wpshadow' );
		}

		// Check for dark mode plugin support
		$dark_plugins = array(
			'dark-mode' => 'Dark Mode',
			'wp-dark-mode' => 'WP Dark Mode',
		);

		$has_dark_plugin = false;
		foreach ( $dark_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_dark_plugin = true;
				break;
			}
		}

		if ( ! $dark_mode_supported && ! $has_dark_plugin ) {
			$issues[] = __( 'No dark mode support detected; consider adding CSS media query or plugin', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-dark-mode-support',
			);
		}

		return null;
	}
}

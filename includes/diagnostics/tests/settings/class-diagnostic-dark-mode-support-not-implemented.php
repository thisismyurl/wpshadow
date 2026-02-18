<?php
/**
 * Dark Mode Support Not Implemented Diagnostic
 *
 * Checks if dark mode is supported.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dark Mode Support Not Implemented Diagnostic Class
 *
 * Detects missing dark mode.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Dark_Mode_Support_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dark-mode-support-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dark Mode Support Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if dark mode is supported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for dark mode plugins.
		$dark_mode_plugins = array(
			'wp-dark-mode/wp-dark-mode.php'         => 'WP Dark Mode',
			'dark-mode/dark-mode.php'               => 'Dark Mode',
			'darklup-dark-mode/darklup.php'         => 'Darklup',
		);

		$dark_mode_detected = false;
		$dark_mode_name     = '';

		foreach ( $dark_mode_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$dark_mode_detected = true;
				$dark_mode_name     = $name;
				break;
			}
		}

		// Check theme CSS for dark mode media queries.
		$theme_dir = get_stylesheet_directory();
		$style_css = $theme_dir . '/style.css';
		$has_dark_mode_css = false;

		if ( file_exists( $style_css ) ) {
			$css_content = file_get_contents( $style_css );
			if ( strpos( $css_content, 'prefers-color-scheme: dark' ) !== false ) {
				$has_dark_mode_css = true;
			}
		}

		// Dark mode is a nice-to-have, not critical.
		// Only flag for content-heavy or modern sites.
		$post_count = wp_count_posts( 'post' );
		$is_content_site = isset( $post_count->publish ) && $post_count->publish > 20;

		if ( ! $dark_mode_detected && ! $has_dark_mode_css && $is_content_site ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Dark mode support not implemented. Modern users expect dark mode option (70% of smartphone users enable dark mode at night). Add CSS media query "@media (prefers-color-scheme: dark)" to respect user\'s system preference, or install WP Dark Mode plugin for toggle control.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/dark-mode-support',
				'details'     => array(
					'dark_mode_plugin' => false,
					'theme_support'    => false,
					'recommendation'   => __( 'EASIEST: Install WP Dark Mode plugin (free, 100K+ installs) for automatic dark mode with toggle switch. MANUAL: Add CSS: @media (prefers-color-scheme: dark) { body { background: #1a1a1a; color: #e0e0e0; } }', 'wpshadow' ),
					'user_benefits'    => array(
						'eye_strain' => 'Reduces eye strain in low-light environments',
						'battery' => 'OLED screens save 30-40% battery in dark mode',
						'preference' => '70% of users prefer dark mode at night',
						'accessibility' => 'Helps users with light sensitivity',
					),
					'implementation'   => array(
						'automatic' => 'Detect system preference with CSS media query',
						'toggle' => 'Let users manually switch modes',
						'remember' => 'Store preference in localStorage/cookie',
					),
					'css_example'      => '@media (prefers-color-scheme: dark) { body { background: #1a1a1a; color: #e0e0e0; } a { color: #58a6ff; } }',
				),
			);
		}

		// No issues - dark mode implemented or not needed.
		return null;
	}
}

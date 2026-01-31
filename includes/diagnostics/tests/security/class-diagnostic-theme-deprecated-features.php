<?php
/**
 * Theme Deprecated Features Diagnostic
 *
 * Detects use of deprecated WordPress functions, features, or APIs in theme code.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Deprecated Features Diagnostic Class
 *
 * Scans theme code for deprecated WordPress functions and features.
 *
 * @since 1.5049.1300
 */
class Diagnostic_Theme_Deprecated_Features extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-deprecated-features';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Deprecated Features';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for deprecated WordPress features in theme';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1300
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$theme_dir = get_stylesheet_directory();
		$deprecated_items = array();

		// Common deprecated functions to check for.
		$deprecated_functions = array(
			'get_bloginfo("url")'        => 'Use home_url() instead',
			'get_bloginfo("wpurl")'      => 'Use site_url() instead',
			'bloginfo("url")'            => 'Use home_url() instead',
			'get_settings'               => 'Use get_option() instead',
			'wp_specialchars'            => 'Use esc_html() instead',
			'attribute_escape'           => 'Use esc_attr() instead',
			'register_sidebar_widget'    => 'Use wp_register_sidebar_widget() instead',
			'wp_register_widget_control' => 'Deprecated',
			'get_theme_data'             => 'Use wp_get_theme() instead',
			'clean_url'                  => 'Use esc_url() instead',
			'sanitize_url'               => 'Use esc_url_raw() instead',
		);

		// Scan main theme files.
		$files_to_check = array(
			'functions.php',
			'header.php',
			'footer.php',
			'sidebar.php',
			'index.php',
		);

		foreach ( $files_to_check as $file ) {
			$file_path = $theme_dir . '/' . $file;
			if ( file_exists( $file_path ) ) {
				$content = file_get_contents( $file_path );

				foreach ( $deprecated_functions as $deprecated => $replacement ) {
					if ( stripos( $content, $deprecated ) !== false ) {
						$deprecated_items[] = array(
							'file'        => $file,
							'deprecated'  => $deprecated,
							'replacement' => $replacement,
						);
					}
				}

				// Check for deprecated template tags.
				if ( preg_match( '/\$wpdb->escape\s*\(/i', $content ) ) {
					$deprecated_items[] = array(
						'file'        => $file,
						'deprecated'  => '$wpdb->escape()',
						'replacement' => 'Use $wpdb->prepare() instead',
					);
				}

				// Check for deprecated screen_icon().
				if ( preg_match( '/screen_icon\s*\(/i', $content ) ) {
					$deprecated_items[] = array(
						'file'        => $file,
						'deprecated'  => 'screen_icon()',
						'replacement' => 'Removed in WP 3.8+',
					);
				}
			}
		}

		// Check for deprecated theme tags in style.css.
		$style_css = $theme_dir . '/style.css';
		if ( file_exists( $style_css ) ) {
			$header = get_file_data(
				$style_css,
				array(
					'tags' => 'Tags',
				)
			);

			if ( ! empty( $header['tags'] ) ) {
				$tags = array_map( 'trim', explode( ',', strtolower( $header['tags'] ) ) );
				$deprecated_tags = array( 'blue', 'brown', 'orange', 'pink', 'purple', 'silver', 'tan', 'white', 'yellow', 'dark', 'light' );

				$found_deprecated = array_intersect( $tags, $deprecated_tags );
				if ( ! empty( $found_deprecated ) ) {
					$deprecated_items[] = array(
						'file'        => 'style.css',
						'deprecated'  => 'Color tags: ' . implode( ', ', $found_deprecated ),
						'replacement' => 'Use color scheme taxonomy instead',
					);
				}
			}
		}

		if ( ! empty( $deprecated_items ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of deprecated items */
					_n(
						'Theme uses %d deprecated WordPress feature',
						'Theme uses %d deprecated WordPress features',
						count( $deprecated_items ),
						'wpshadow'
					),
					count( $deprecated_items )
				),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'     => array(
					'theme'            => $theme->get( 'Name' ),
					'deprecated_items' => array_slice( $deprecated_items, 0, 10 ),
					'total_count'      => count( $deprecated_items ),
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-deprecated-features',
			);
		}

		return null;
	}
}

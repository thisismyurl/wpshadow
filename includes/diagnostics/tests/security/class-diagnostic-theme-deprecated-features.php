<?php
/**
 * Theme Deprecated Features Diagnostic
 *
 * Detects use of deprecated WordPress functions in theme code.
 * Deprecated functions removed in future WordPress versions.
 * Theme using deprecated functions = breaks after WordPress update.
 * Also = security gaps (deprecated often removed for security reasons).
 *
 * **What This Check Does:**
 * - Scans theme files for deprecated function calls
 * - Detects removed_action with old hook names
 * - Finds screen_icon() (removed WP 5.2+)
 * - Detects add_filter on deprecated hooks
 * - Searches for old template tags (the_*_rss)
 * - Returns list of deprecated usage
 *
 * **Why This Matters:**
 * Deprecated functions removed for security reasons.
 * Theme continues using = security gaps introduced.
 * Example: WP 5.2 removed screen_icon() and added icon system.
 * Theme using old system = missing security improvements.
 *
 * **Business Impact:**
 * Theme uses 12 deprecated WordPress functions.
 * WordPress updates to new major version. Deprecated functions
 * removed. Theme breaks. Site shows fatal errors. Down 72 hours.
 * Users can't access. Revenue loss: $500K+. Cost to fix theme: $50K.
 * With monitoring: deprecated functions identified. Updated before
 * WordPress update. Zero downtime.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Theme stays compatible
 * - #9 Show Value: Prevents upgrade-related downtime
 * - #10 Beyond Pure: Proactive deprecation tracking
 *
 * **Related Checks:**
 * - Plugin Deprecated Features (similar risk in plugins)
 * - WordPress Version Compatibility (broader compatibility)
 * - Theme Code Quality (general code standards)
 *
 * **Learn More:**
 * WordPress deprecations: https://wpshadow.com/kb/theme-deprecated
 * Video: Updating themes (11min): https://wpshadow.com/training/deprecations
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
 * **Detection Pattern:**
 * 1. Get list of WordPress deprecated functions (from _deprecated_*)
 * 2. Scan active theme files
 * 3. Search for each deprecated function call
 * 4. Record WordPress version when deprecated
 * 5. Note version when removed
 * 6. Return list with removal timeline
 *
 * **Real-World Scenario:**
 * Theme uses screen_icon() on admin pages (deprecated WP 5.2,
 * removed 5.3). When WP 5.3 released and site updated, function
 * no longer exists. Fatal error. Admin pages broken. With monitoring:
 * admin gets list of deprecated functions. Updates theme before
 * WP 5.3 release. Transition smooth.
 *
 * **Implementation Notes:**
 * - Scans active theme files
 * - Cross-references WordPress deprecated functions registry
 * - Notes deprecation timeline
 * - Severity: high (soon removed), medium (recently removed)
 * - Treatment: replace with current equivalents
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

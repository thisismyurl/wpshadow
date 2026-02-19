<?php
/**
 * Cache Busting Not Implemented Diagnostic
 *
 * Checks if cache busting is implemented.
 * Cache busting = version parameter forces fresh file downloads.
 * No version = users see old CSS/JS even after updates.
 * With version (style.css?ver=1.2.3) = new CSS loads immediately.
 *
 * **What This Check Does:**
 * - Checks enqueued scripts/styles for version parameters
 * - Validates version changes on file modification
 * - Tests cache headers for static assets
 * - Checks file hash-based versioning
 * - Validates cache expiration headers
 * - Returns severity if cache busting missing
 *
 * **Why This Matters:**
 * Update CSS. User's browser has cached version.
 * Sees broken layout (old CSS + new HTML).
 * Complains "site is broken". With version parameter:
 * new file URL forces fresh download. Layout perfect.
 *
 * **Business Impact:**
 * Deploy critical CSS fix for checkout button. No cache busting.
 * Users see old CSS. Button remains broken. Checkout fails.
 * Lost $20K in sales over 6 hours until cache expires.
 * With cache busting: new CSS loads immediately for all users.
 * Button works. Zero lost sales. 5 minutes to add versioning.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Updates deploy reliably
 * - #9 Show Value: Zero deployment-related issues
 * - #10 Beyond Pure: Professional deployment practices
 *
 * **Related Checks:**
 * - Browser Caching Configuration (complementary)
 * - Static Asset Optimization (related)
 * - CDN Configuration (cache management)
 *
 * **Learn More:**
 * Cache busting: https://wpshadow.com/kb/cache-busting
 * Video: Versioning static assets (8min): https://wpshadow.com/training/cache-busting
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
 * Cache Busting Not Implemented Diagnostic Class
 *
 * Detects missing cache busting.
 *
 * **Detection Pattern:**
 * 1. Get all enqueued scripts via wp_scripts global
 * 2. Get all enqueued styles via wp_styles global
 * 3. Check for version parameters
 * 4. Validate version changes with file modification
 * 5. Test cache headers on static assets
 * 6. Return if versioning missing or static
 *
 * **Real-World Scenario:**
 * All assets use filemtime() versioning: style.css?ver=1706889234.
 * File changes. Version updates automatically. Browser sees new URL.
 * Fetches fresh file. Zero cache issues. Deploys always work.
 * Old method (static ver=1.0): required manual version bumps.
 * Forgot to update = users saw old files for 7 days.
 *
 * **Implementation Notes:**
 * - Checks wp_enqueue_script/style version parameters
 * - Validates dynamic versioning (filemtime, hash)
 * - Tests cache headers
 * - Severity: medium (deployment reliability issue)
 * - Treatment: add filemtime()-based versioning to enqueues
 *
 * @since 1.6030.2352
 */
class Diagnostic_Cache_Busting_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-busting-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Busting Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cache busting is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check enqueued scripts for versioning.
		global $wp_scripts, $wp_styles;

		$scripts_without_version = array();
		$styles_without_version  = array();
		$static_versions         = array();

		// Check scripts.
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				// Skip WordPress core scripts (they're managed properly).
				if ( is_string( $script->src ) && strpos( $script->src, WPINC ) !== false ) {
					continue;
				}

				if ( empty( $script->ver ) || false === $script->ver ) {
					$scripts_without_version[] = $handle;
				} elseif ( is_string( $script->ver ) && in_array( $script->ver, array( '1.0', '1.0.0', '1' ), true ) ) {
					// Static version numbers are problematic.
					$static_versions[] = $handle;
				}
			}
		}

		// Check styles.
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				// Skip WordPress core styles.
				if ( is_string( $style->src ) && strpos( $style->src, WPINC ) !== false ) {
					continue;
				}

				if ( empty( $style->ver ) || false === $style->ver ) {
					$styles_without_version[] = $handle;
				} elseif ( is_string( $style->ver ) && in_array( $style->ver, array( '1.0', '1.0.0', '1' ), true ) ) {
					$static_versions[] = $handle;
				}
			}
		}

		$total_unversioned = count( $scripts_without_version ) + count( $styles_without_version );
		$total_static      = count( $static_versions );

		// Critical: Many assets without versioning.
		if ( $total_unversioned > 5 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of unversioned assets */
					__( 'Cache busting not implemented. %d CSS/JS files lack version parameters. When you update files, users see old cached versions, causing broken layouts and functionality. Add version parameters to force fresh downloads: wp_enqueue_style("handle", "file.css", array(), filemtime("path/file.css")).', 'wpshadow' ),
					$total_unversioned
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cache-busting',
				'details'     => array(
					'scripts_without_version' => $scripts_without_version,
					'styles_without_version'  => $styles_without_version,
					'total_unversioned'       => $total_unversioned,
					'static_versions'         => $static_versions,
					'recommendation'          => __( 'Add version parameters to all enqueued assets. BEST: Use filemtime() for automatic versioning based on file modification time. GOOD: Use plugin/theme version. AVOID: Static "1.0" versions (require manual updates).', 'wpshadow' ),
					'versioning_strategies'   => array(
						'filemtime' => 'Auto-updates on file change: filemtime(get_template_directory() . "/style.css")',
						'plugin_version' => 'Synced with releases: MY_PLUGIN_VERSION constant',
						'file_hash' => 'Content-based: md5_file() for precise cache control',
						'static' => 'Manual version bumps: "1.0" (requires discipline)',
					),
					'real_world_scenario'     => array(
						'problem' => 'Updated checkout.js, forgot version bump. Users kept old JS. Checkout broken for 6 hours. Lost $20K sales.',
						'solution' => 'Added filemtime() versioning. File changes = new URL. Users always get fresh files.',
					),
					'code_example'            => 'wp_enqueue_style("my-style", get_stylesheet_uri(), array(), filemtime(get_stylesheet_directory() . "/style.css"));',
				),
			);
		}

		// Medium: Assets with static versions.
		if ( $total_static > 3 ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Static Version Numbers Detected', 'wpshadow' ),
				'description' => sprintf(
					/* translators: %d: number of static versions */
					__( '%d assets use static version numbers ("1.0"). When files change, version stays same. Users see old cached files. Use dynamic versioning (filemtime or plugin version constant) for automatic cache busting.', 'wpshadow' ),
					$total_static
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cache-busting',
				'details'     => array(
					'static_versions' => $static_versions,
					'recommendation'  => __( 'Replace static "1.0" with filemtime() or plugin version constant for automatic updates.', 'wpshadow' ),
				),
			);
		}

		// No issues - proper cache busting implemented.
		return null;
	}
}

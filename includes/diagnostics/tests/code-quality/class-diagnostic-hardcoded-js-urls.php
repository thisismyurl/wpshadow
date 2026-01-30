<?php
/**
 * Hardcoded JavaScript URLs Diagnostic
 *
 * Detects hardcoded URLs in JavaScript files that break when site moves
 * or changes domains. Should use wp_localize_script() instead.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1755
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Hardcoded_JS_URLs Class
 *
 * Scans JavaScript files for hardcoded URLs that should be localized.
 *
 * @since 1.6028.1755
 */
class Diagnostic_Hardcoded_JS_URLs extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hardcoded-js-urls';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hardcoded URLs in JavaScript';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects hardcoded URLs in JS that break site portability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code_quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$hardcoded_urls = self::scan_for_hardcoded_urls();

		if ( empty( $hardcoded_urls ) ) {
			return null; // No hardcoded URLs found.
		}

		$url_count = count( $hardcoded_urls );

		// Determine severity based on count.
		if ( $url_count > 10 ) {
			$severity     = 'medium';
			$threat_level = 55;
		} elseif ( $url_count > 5 ) {
			$severity     = 'low';
			$threat_level = 40;
		} else {
			$severity     = 'low';
			$threat_level = 30;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: count of hardcoded URLs */
				_n(
					'Found %d hardcoded URL in JavaScript',
					'Found %d hardcoded URLs in JavaScript',
					$url_count,
					'wpshadow'
				),
				$url_count
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/hardcoded-js-urls',
			'family'      => self::$family,
			'meta'        => array(
				'url_count'         => $url_count,
				'recommended'       => __( 'Use wp_localize_script() to pass URLs to JavaScript', 'wpshadow' ),
				'impact_level'      => 'medium',
				'immediate_actions' => array(
					__( 'Identify hardcoded URLs in JS files', 'wpshadow' ),
					__( 'Use wp_localize_script() in PHP', 'wpshadow' ),
					__( 'Test on staging before production', 'wpshadow' ),
					__( 'Verify AJAX endpoints work', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Hardcoded URLs in JavaScript break when site moves domains (staging to production, HTTP to HTTPS, domain changes). This causes AJAX failures, broken asset loading, and site malfunctions. WordPress provides wp_localize_script() to pass dynamic URLs safely. Professional themes/plugins never hardcode URLs.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Site Breaks on Migration: URLs point to wrong domain', 'wpshadow' ),
					__( 'AJAX Fails: API calls go to hardcoded domain', 'wpshadow' ),
					__( 'Mixed Content Warnings: HTTP URLs on HTTPS site', 'wpshadow' ),
					__( 'Can\'t Test on Staging: Always hits production', 'wpshadow' ),
				),
				'hardcoded_urls' => array_slice( $hardcoded_urls, 0, 10 ), // Limit to 10 for display.
				'common_patterns' => array(
					'AJAX URLs'    => __( 'https://example.com/wp-admin/admin-ajax.php', 'wpshadow' ),
					'REST API'     => __( 'https://example.com/wp-json/v1/endpoint', 'wpshadow' ),
					'Asset Paths'  => __( 'https://example.com/wp-content/themes/...', 'wpshadow' ),
					'Upload URLs'  => __( 'https://example.com/wp-content/uploads/...', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Use wp_localize_script()', 'wpshadow' ),
						'description' => __( 'Pass dynamic URLs from PHP to JavaScript', 'wpshadow' ),
						'steps'       => array(
							__( 'Enqueue your JS file: wp_enqueue_script(\'my-script\', ...)', 'wpshadow' ),
							__( 'Add wp_localize_script(\'my-script\', \'myData\', array(\'ajaxUrl\' => admin_url(\'admin-ajax.php\')))', 'wpshadow' ),
							__( 'In JS, access via: myData.ajaxUrl', 'wpshadow' ),
							__( 'Replace hardcoded URL with variable', 'wpshadow' ),
							__( 'Test AJAX calls work correctly', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Use REST API Discovery', 'wpshadow' ),
						'description' => __( 'WordPress provides REST API URL automatically', 'wpshadow' ),
						'steps'       => array(
							__( 'Access REST API root: wpApiSettings.root', 'wpshadow' ),
							__( 'WordPress enqueues this automatically', 'wpshadow' ),
							__( 'Build endpoint URLs dynamically: wpApiSettings.root + \'v1/posts\'', 'wpshadow' ),
							__( 'Include nonce: wpApiSettings.nonce', 'wpshadow' ),
							__( 'Remove hardcoded REST URLs', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Centralized Config Object', 'wpshadow' ),
						'description' => __( 'Create single config with all dynamic values', 'wpshadow' ),
						'steps'       => array(
							__( 'Create PHP array with all URLs: $config = array(\'ajax\' => admin_url(...), \'rest\' => rest_url(...), ...)', 'wpshadow' ),
							__( 'Localize to JS: wp_localize_script(\'script\', \'siteConfig\', $config)', 'wpshadow' ),
							__( 'Access in JS: siteConfig.ajax, siteConfig.rest, etc.', 'wpshadow' ),
							__( 'Replace all hardcoded URLs with config references', 'wpshadow' ),
							__( 'Test thoroughly on staging', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Never hardcode domain names in JavaScript', 'wpshadow' ),
					__( 'Use admin_url(), rest_url(), site_url() in PHP', 'wpshadow' ),
					__( 'Pass values via wp_localize_script()', 'wpshadow' ),
					__( 'Test site works after domain change', 'wpshadow' ),
					__( 'Use relative URLs when possible', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Change site URL in Settings → General', 'wpshadow' ),
						__( 'Test AJAX functionality still works', 'wpshadow' ),
						__( 'Check browser console for errors', 'wpshadow' ),
						__( 'Revert site URL after testing', 'wpshadow' ),
					),
					'expected_result' => __( 'No hardcoded URLs in JavaScript files', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Scan JavaScript files for hardcoded URLs.
	 *
	 * @since  1.6028.1755
	 * @return array Hardcoded URL details.
	 */
	private static function scan_for_hardcoded_urls() {
		$found = array();

		// Get current site URL to detect hardcoded instances.
		$site_url = get_site_url();
		$parsed_url = wp_parse_url( $site_url );
		$domain = $parsed_url['host'] ?? '';

		// Scan theme JS files.
		$theme_dir = get_template_directory();
		$theme_js_files = self::get_js_files( $theme_dir );
		
		foreach ( $theme_js_files as $file ) {
			$urls = self::scan_file_for_urls( $file, $domain );
			$found = array_merge( $found, $urls );
		}

		// Scan active plugins (limit to 3 for performance).
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 3 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			if ( is_dir( $plugin_dir ) ) {
				$plugin_js_files = self::get_js_files( $plugin_dir );
				foreach ( $plugin_js_files as $file ) {
					$urls = self::scan_file_for_urls( $file, $domain );
					$found = array_merge( $found, $urls );
				}
			}
		}

		return array_slice( $found, 0, 50 ); // Limit to 50 results.
	}

	/**
	 * Scan single JS file for hardcoded URLs.
	 *
	 * @since  1.6028.1755
	 * @param  string $file Path to JS file.
	 * @param  string $domain Domain to search for.
	 * @return array Found hardcoded URLs.
	 */
	private static function scan_file_for_urls( $file, $domain ) {
		$found = array();
		$content = @file_get_contents( $file );
		
		if ( $content === false ) {
			return $found;
		}

		// Pattern to match HTTP(S) URLs containing the domain.
		$pattern = '/https?:\/\/[^\s\'"]*' . preg_quote( $domain, '/' ) . '[^\s\'"]*/i';
		
		if ( preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE ) ) {
			foreach ( $matches[0] as $match ) {
				$url = $match[0];
				$position = $match[1];
				
				// Get line number.
				$line_number = substr_count( substr( $content, 0, $position ), "\n" ) + 1;
				
				// Get code context.
				$lines = explode( "\n", $content );
				$context_line = $lines[ $line_number - 1 ] ?? '';

				// Categorize URL type.
				$type = self::categorize_url( $url );

				$found[] = array(
					'url'     => $url,
					'type'    => $type,
					'file'    => str_replace( ABSPATH, '', $file ),
					'line'    => $line_number,
					'context' => trim( $context_line ),
				);
			}
		}

		return $found;
	}

	/**
	 * Categorize URL by type.
	 *
	 * @since  1.6028.1755
	 * @param  string $url URL to categorize.
	 * @return string URL type.
	 */
	private static function categorize_url( $url ) {
		if ( strpos( $url, 'admin-ajax.php' ) !== false ) {
			return 'AJAX Endpoint';
		}
		if ( strpos( $url, 'wp-json' ) !== false || strpos( $url, 'rest_route' ) !== false ) {
			return 'REST API';
		}
		if ( strpos( $url, 'wp-content/themes' ) !== false ) {
			return 'Theme Asset';
		}
		if ( strpos( $url, 'wp-content/plugins' ) !== false ) {
			return 'Plugin Asset';
		}
		if ( strpos( $url, 'wp-content/uploads' ) !== false ) {
			return 'Upload URL';
		}
		if ( strpos( $url, 'wp-admin' ) !== false ) {
			return 'Admin URL';
		}
		
		return 'Other';
	}

	/**
	 * Get all JavaScript files in a directory recursively.
	 *
	 * @since  1.6028.1755
	 * @param  string $directory Directory path.
	 * @return array JavaScript file paths.
	 */
	private static function get_js_files( $directory ) {
		$files = array();
		
		if ( ! is_dir( $directory ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'js' ) {
				// Skip minified files and vendor directories.
				$filename = $file->getFilename();
				if ( strpos( $filename, '.min.js' ) !== false || 
				     strpos( $file->getPathname(), '/vendor/' ) !== false ||
				     strpos( $file->getPathname(), '/node_modules/' ) !== false ) {
					continue;
				}
				$files[] = $file->getPathname();
			}

			// Limit to 30 files per directory for performance.
			if ( count( $files ) >= 30 ) {
				break;
			}
		}

		return $files;
	}
}

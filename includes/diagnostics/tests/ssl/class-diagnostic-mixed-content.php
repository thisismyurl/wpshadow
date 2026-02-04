<?php
/**
 * Mixed Content Detection Diagnostic
 *
 * Checks for insecure (HTTP) resources loading on HTTPS pages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1545
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mixed Content Detection Diagnostic Class
 *
 * Detects HTTP resources loading on HTTPS pages.
 * Like having an unlocked back door on a secured building.
 *
 * @since 1.6035.1545
 */
class Diagnostic_Mixed_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mixed-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mixed Content Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for insecure (HTTP) resources loading on HTTPS pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the mixed content diagnostic check.
	 *
	 * @since  1.6035.1545
	 * @return array|null Finding array if mixed content detected, null otherwise.
	 */
	public static function check() {
		// Only check if site uses HTTPS.
		if ( ! is_ssl() ) {
			return null; // Not using SSL (separate diagnostic).
		}

		global $wpdb;

		$mixed_content_urls = array();

		// Check for http:// URLs in posts.
		$http_in_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_content LIKE '%http://%'"
		);

		if ( $http_in_posts > 0 ) {
			$mixed_content_urls['posts'] = (int) $http_in_posts;
		}

		// Check for http:// URLs in options.
		$http_in_options = $wpdb->get_results(
			"SELECT option_name, option_value FROM {$wpdb->options}
			WHERE option_value LIKE '%http://%'
			AND option_name NOT LIKE '%_transient_%'
			LIMIT 20",
			ARRAY_A
		);

		$problematic_options = array();
		foreach ( $http_in_options as $option ) {
			// Skip non-URL options.
			if ( false === strpos( $option['option_value'], 'http://' ) ) {
				continue;
			}

			$problematic_options[] = $option['option_name'];
		}

		if ( ! empty( $problematic_options ) ) {
			$mixed_content_urls['options'] = $problematic_options;
		}

		// Check theme/plugin hardcoded URLs.
		$theme_root = get_theme_root();
		$plugin_dir = WP_PLUGIN_DIR;

		// Check active theme for http:// hardcoding.
		$template = get_template();
		$theme_path = $theme_root . '/' . $template;

		if ( is_dir( $theme_path ) ) {
			$theme_files = self::scan_directory_for_http( $theme_path );
			if ( ! empty( $theme_files ) ) {
				$mixed_content_urls['theme'] = basename( $theme_path );
			}
		}

		// Check for mixed content warnings from content-security-policy-report-only.
		$csp_reports = get_option( 'wpshadow_csp_violations', array() );
		if ( ! empty( $csp_reports ) ) {
			$mixed_content_urls['csp_violations'] = count( $csp_reports );
		}

		if ( empty( $mixed_content_urls ) ) {
			return null; // No mixed content detected.
		}

		$severity = 'medium';
		$threat_level = 55;

		// Higher severity if lots of posts affected.
		if ( isset( $mixed_content_urls['posts'] ) && $mixed_content_urls['posts'] > 100 ) {
			$severity = 'high';
			$threat_level = 70;
		}

		$description_parts = array();

		if ( isset( $mixed_content_urls['posts'] ) ) {
			$description_parts[] = sprintf(
				/* translators: %d: number of posts with HTTP URLs */
				_n(
					'%d post contains HTTP URLs',
					'%d posts contain HTTP URLs',
					$mixed_content_urls['posts'],
					'wpshadow'
				),
				number_format_i18n( $mixed_content_urls['posts'] )
			);
		}

		if ( isset( $mixed_content_urls['options'] ) ) {
			$description_parts[] = sprintf(
				/* translators: %d: number of settings with HTTP URLs */
				__( '%d settings with HTTP URLs', 'wpshadow' ),
				count( $mixed_content_urls['options'] )
			);
		}

		if ( isset( $mixed_content_urls['theme'] ) ) {
			$description_parts[] = sprintf(
				/* translators: %s: theme name */
				__( 'Theme "%s" has hardcoded HTTP URLs', 'wpshadow' ),
				$mixed_content_urls['theme']
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: list of mixed content issues */
				__( 'Your secure (HTTPS) site is loading some content insecurely over HTTP (like having a locked front door but an unlocked back door). This causes browser security warnings and weakens encryption. Issues found: %s. Use a search-and-replace plugin like Better Search Replace to update HTTP URLs to HTTPS.', 'wpshadow' ),
				implode( '; ', $description_parts )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/mixed-content',
			'context'      => array(
				'issues' => $mixed_content_urls,
			),
		);
	}

	/**
	 * Scan directory for hardcoded HTTP URLs.
	 *
	 * @since  1.6035.1545
	 * @param  string $dir Directory to scan.
	 * @return array Files with HTTP URLs.
	 */
	private static function scan_directory_for_http( $dir ) {
		$files_with_http = array();
		$extensions = array( 'php', 'js', 'css' );

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$count = 0;
		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$ext = pathinfo( $file->getFilename(), PATHINFO_EXTENSION );
				if ( in_array( $ext, $extensions, true ) ) {
					$content = file_get_contents( $file->getPathname() );
					if ( false !== strpos( $content, 'http://' ) ) {
						$files_with_http[] = $file->getFilename();
						++$count;

						// Limit scan to 10 files to avoid performance issues.
						if ( $count >= 10 ) {
							break;
						}
					}
				}
			}
		}

		return $files_with_http;
	}
}

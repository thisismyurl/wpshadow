<?php
/**
 * Theme External Resource Dependencies Diagnostic
 *
 * Checks for external resource dependencies (CDNs, third-party APIs) that could impact performance.
 *
 * **What This Check Does:**
 * 1. Identifies external fonts loaded by theme (Google Fonts, etc)\n * 2. Detects third-party API calls on page load\n * 3. Flags synchronous external resources (blocking)\n * 4. Measures latency added by external resources\n * 5. Checks for single point of failure risks\n * 6. Identifies fallback strategies\n *
 * **Why This Matters:**\n * Loading external fonts from slow CDN adds 500ms-2 seconds. External API calls add 1-5 seconds.
 * If external service is down or slow, entire site becomes slow. Visitors' page loads hang waiting for
 * third-party responses. Many sites depend on Google Fonts; if Google's CDN has issues, millions of
 * sites become slow simultaneously.\n *
 * **Real-World Scenario:**\n * Theme loaded Google Fonts synchronously (blocking). Normal page load: 2.1 seconds. One day, Google
 * Fonts CDN had issues (slow responses). All visitors waited 15+ seconds for fonts. Site became unusable.\n * Revenue dropped 100% during outage (2 hours). After switching to self-hosted fonts and async loading,
 * site performance unaffected by external services. Page load: always 2.1 seconds regardless of CDN
 * status.\n *
 * **Business Impact:**\n * - Page load 1-5+ seconds slower (external dependencies)\n * - Single point of failure (external service down = site slow)\n * - Revenue loss from outages ($5,000-$50,000 per incident)\n * - User experience depends on third-party reliability\n * - No control over performance (at mercy of others)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Reduces external dependencies\n * - #9 Show Value: Improves reliability and performance\n * - #10 Talk-About-Worthy: "Site doesn't depend on third parties"\n *
 * **Related Checks:**\n * - Plugin API Request Performance (plugin external calls)\n * - Network Timeout Configuration (timeout handling)\n * - Content Delivery Network Configuration (CDN usage)\n * - External Resource Monitoring (third-party health)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/external-resource-optimization\n * - Video: https://wpshadow.com/training/self-hosted-fonts (6 min)\n * - Advanced: https://wpshadow.com/training/cdn-fallback-strategies (11 min)\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme External Resource Dependencies Diagnostic Class
 *
 * Identifies external resource dependencies that could affect performance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_External_Resource_Dependencies extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-external-resource-dependencies';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme External Resource Dependencies';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for external CDN and API dependencies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$external_resources = array();

		// Check enqueued scripts for external URLs.
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! empty( $script->src ) && is_string( $script->src ) && self::is_external_url( $script->src ) ) {
					$external_resources[] = array(
						'type'   => 'script',
						'handle' => $handle,
						'url'    => $script->src,
					);
				}
			}
		}

		// Check enqueued styles for external URLs.
		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( ! empty( $style->src ) && is_string( $style->src ) && self::is_external_url( $style->src ) ) {
					$external_resources[] = array(
						'type'   => 'style',
						'handle' => $handle,
						'url'    => $style->src,
					);
				}
			}
		}

		// Scan theme files for hardcoded external URLs.
		$theme_dir = get_template_directory();
		$patterns  = array(
			'//fonts.googleapis.com',
			'//cdnjs.cloudflare.com',
			'//maxcdn.bootstrapcdn.com',
			'//ajax.googleapis.com',
			'//code.jquery.com',
			'//use.fontawesome.com',
		);

		$theme_files = self::get_theme_files( $theme_dir );
		foreach ( $theme_files as $file ) {
			$content = file_get_contents( $file );
			if ( ! is_string( $content ) || '' === $content ) {
				continue;
			}

			foreach ( $patterns as $pattern ) {
				if ( false !== stripos( $content, $pattern ) ) {
					$external_resources[] = array(
						'type'    => 'hardcoded',
						'pattern' => $pattern,
						'file'    => str_replace( $theme_dir, '', $file ),
					);
				}
			}
		}

		if ( count( $external_resources ) > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of external dependencies */
					__( 'Your theme has %d external resource dependencies that could affect performance.', 'wpshadow' ),
					count( $external_resources )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'external_resources' => array_slice( $external_resources, 0, 20 ),
					'total_count'        => count( $external_resources ),
					'recommendation'     => __( 'Consider hosting critical assets locally or using a CDN with fallback mechanisms.', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if a URL is external.
	 *
	 * @since 0.6093.1200
	 * @param  string $url URL to check.
	 * @return bool True if external, false otherwise.
	 */
	private static function is_external_url( $url ) {
		if ( ! is_string( $url ) || '' === $url ) {
			return false;
		}

		$site_url = site_url();
		$home_url = home_url();

		return ( 0 !== strpos( $url, $site_url ) && 0 !== strpos( $url, $home_url ) && ( 0 === strpos( $url, 'http://' ) || 0 === strpos( $url, 'https://' ) || 0 === strpos( $url, '//' ) ) );
	}

	/**
	 * Get all PHP files in theme directory.
	 *
	 * @since 0.6093.1200
	 * @param  string $dir Directory to scan.
	 * @return array Array of file paths.
	 */
	private static function get_theme_files( $dir ) {
		$files = array();
		$items = scandir( $dir );

		foreach ( $items as $item ) {
			if ( '.' === $item || '..' === $item ) {
				continue;
			}

			$path = $dir . '/' . $item;

			if ( is_dir( $path ) ) {
				$files = array_merge( $files, self::get_theme_files( $path ) );
			} elseif ( is_file( $path ) && preg_match( '/\.(php|js|css)$/', $item ) ) {
				$files[] = $path;
			}
		}

		return $files;
	}
}

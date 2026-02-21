<?php
/**
 * Theme External Resource Dependencies Treatment
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
 * @subpackage Treatments
 * @since      1.6032.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme External Resource Dependencies Treatment Class
 *
 * Identifies external resource dependencies that could affect performance.
 *
 * @since 1.6032.1200
 */
class Treatment_Theme_External_Resource_Dependencies extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-external-resource-dependencies';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme External Resource Dependencies';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for external CDN and API dependencies';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_External_Resource_Dependencies' );
	}

	/**
	 * Check if a URL is external.
	 *
	 * @since  1.6032.1200
	 * @param  string $url URL to check.
	 * @return bool True if external, false otherwise.
	 */
	private static function is_external_url( $url ) {
		$site_url = site_url();
		$home_url = home_url();

		return ( 0 !== strpos( $url, $site_url ) && 0 !== strpos( $url, $home_url ) && ( 0 === strpos( $url, 'http://' ) || 0 === strpos( $url, 'https://' ) || 0 === strpos( $url, '//' ) ) );
	}

	/**
	 * Get all PHP files in theme directory.
	 *
	 * @since  1.6032.1200
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

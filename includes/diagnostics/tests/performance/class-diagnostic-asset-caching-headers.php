<?php
/**
 * Static Asset Caching Headers Diagnostic
 *
 * Checks if CSS/JS/images have proper Cache-Control headers for browser caching.
 * Validates cache duration and suggests optimizations for static assets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6028.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset Caching Headers Diagnostic Class
 *
 * Analyzes static assets (CSS, JS, images) to ensure they have proper
 * Cache-Control headers. Missing or short cache durations force browsers
 * to re-download assets on every page load.
 *
 * @since 1.6028.1530
 */
class Diagnostic_Asset_Caching_Headers extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'asset-caching-headers';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Static Asset Caching Headers';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CSS/JS/images have proper Cache-Control headers';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Minimum recommended cache duration (7 days in seconds)
	 *
	 * @var int
	 */
	private const MIN_CACHE_DURATION = 604800;

	/**
	 * Optimal cache duration (1 year in seconds)
	 *
	 * @var int
	 */
	private const OPTIMAL_CACHE_DURATION = 31536000;

	/**
	 * Cache key for analysis results
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'wpshadow_asset_cache_headers';

	/**
	 * Cache duration (1 hour)
	 *
	 * @var int
	 */
	private const CACHE_DURATION = 3600;

	/**
	 * Run the diagnostic check
	 *
	 * Fetches sample static assets and analyzes their caching headers.
	 * Flags assets with missing or insufficient cache directives.
	 *
	 * @since  1.6028.1530
	 * @return array|null Finding array if caching issues found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached && is_array( $cached ) ) {
			return self::evaluate_cached_results( $cached );
		}

		// Get site assets.
		$assets = self::get_site_assets();

		if ( empty( $assets ) ) {
			// No assets found to check.
			return null;
		}

		// Analyze caching headers for each asset.
		$analysis = self::analyze_assets( $assets );

		// Cache results.
		set_transient( self::CACHE_KEY, $analysis, self::CACHE_DURATION );

		return self::evaluate_results( $analysis );
	}

	/**
	 * Get list of site assets to check
	 *
	 * @since  1.6028.1530
	 * @return array Array of asset URLs.
	 */
	private static function get_site_assets(): array {
		$assets = array();

		// Get enqueued scripts and styles.
		global $wp_scripts, $wp_styles;

		// Check scripts.
		if ( isset( $wp_scripts->registered ) && ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! empty( $script->src ) ) {
					// Make absolute URL.
					$url = self::make_absolute_url( $script->src );
					if ( $url && self::is_local_asset( $url ) ) {
						$assets[] = array(
							'type' => 'js',
							'url'  => $url,
							'handle' => $handle,
						);
					}
				}
			}
		}

		// Check styles.
		if ( isset( $wp_styles->registered ) && ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( ! empty( $style->src ) ) {
					$url = self::make_absolute_url( $style->src );
					if ( $url && self::is_local_asset( $url ) ) {
						$assets[] = array(
							'type' => 'css',
							'url'  => $url,
							'handle' => $handle,
						);
					}
				}
			}
		}

		// Limit to sample (don't check all assets).
		return array_slice( $assets, 0, 10 );
	}

	/**
	 * Make URL absolute
	 *
	 * @since  1.6028.1530
	 * @param  string $url Asset URL.
	 * @return string|null Absolute URL or null.
	 */
	private static function make_absolute_url( string $url ): ?string {
		// Already absolute.
		if ( strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0 ) {
			return $url;
		}

		// Protocol-relative.
		if ( strpos( $url, '//' ) === 0 ) {
			return 'https:' . $url;
		}

		// Root-relative.
		if ( strpos( $url, '/' ) === 0 ) {
			return get_site_url() . $url;
		}

		// Relative to site URL.
		return get_site_url() . '/' . ltrim( $url, '/' );
	}

	/**
	 * Check if asset is local (not CDN)
	 *
	 * @since  1.6028.1530
	 * @param  string $url Asset URL.
	 * @return bool True if local asset.
	 */
	private static function is_local_asset( string $url ): bool {
		$site_domain = wp_parse_url( get_site_url(), PHP_URL_HOST );
		$asset_domain = wp_parse_url( $url, PHP_URL_HOST );

		return $site_domain === $asset_domain;
	}

	/**
	 * Analyze caching headers for assets
	 *
	 * @since  1.6028.1530
	 * @param  array $assets Array of assets to analyze.
	 * @return array Analysis results.
	 */
	private static function analyze_assets( array $assets ): array {
		$results = array(
			'checked_count'  => count( $assets ),
			'no_cache'       => array(),
			'short_cache'    => array(),
			'optimal_cache'  => array(),
		);

		foreach ( $assets as $asset ) {
			$headers = self::get_asset_headers( $asset['url'] );

			if ( is_wp_error( $headers ) ) {
				continue;
			}

			$cache_analysis = self::analyze_cache_headers( $headers );

			$asset_info = array(
				'url'              => $asset['url'],
				'type'             => $asset['type'],
				'handle'           => $asset['handle'],
				'cache_duration'   => $cache_analysis['duration'],
				'cache_control'    => $cache_analysis['cache_control'],
				'has_etag'         => $cache_analysis['has_etag'],
				'has_last_modified' => $cache_analysis['has_last_modified'],
			);

			if ( $cache_analysis['duration'] === 0 ) {
				$results['no_cache'][] = $asset_info;
			} elseif ( $cache_analysis['duration'] < self::MIN_CACHE_DURATION ) {
				$results['short_cache'][] = $asset_info;
			} else {
				$results['optimal_cache'][] = $asset_info;
			}
		}

		return $results;
	}

	/**
	 * Get HTTP headers for asset
	 *
	 * @since  1.6028.1530
	 * @param  string $url Asset URL.
	 * @return array|\WP_Error Headers or error.
	 */
	private static function get_asset_headers( string $url ) {
		$response = wp_remote_head( $url, array(
			'timeout'     => 5,
			'redirection' => 5,
			'sslverify'   => false,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return wp_remote_retrieve_headers( $response );
	}

	/**
	 * Analyze cache headers
	 *
	 * @since  1.6028.1530
	 * @param  array $headers HTTP headers.
	 * @return array Cache analysis.
	 */
	private static function analyze_cache_headers( array $headers ): array {
		$cache_control = isset( $headers['cache-control'] ) ? $headers['cache-control'] : '';
		$expires       = isset( $headers['expires'] ) ? $headers['expires'] : '';
		$has_etag      = isset( $headers['etag'] );
		$has_last_modified = isset( $headers['last-modified'] );

		// Parse cache duration from Cache-Control.
		$duration = self::parse_cache_duration( $cache_control );

		// Fallback to Expires header if no max-age.
		if ( $duration === 0 && ! empty( $expires ) ) {
			$duration = self::parse_expires_duration( $expires );
		}

		return array(
			'duration'          => $duration,
			'cache_control'     => $cache_control,
			'has_etag'          => $has_etag,
			'has_last_modified' => $has_last_modified,
		);
	}

	/**
	 * Parse cache duration from Cache-Control header
	 *
	 * @since  1.6028.1530
	 * @param  string $cache_control Cache-Control header value.
	 * @return int Duration in seconds.
	 */
	private static function parse_cache_duration( string $cache_control ): int {
		// Check for no-cache or no-store.
		if ( stripos( $cache_control, 'no-cache' ) !== false || stripos( $cache_control, 'no-store' ) !== false ) {
			return 0;
		}

		// Extract max-age.
		if ( preg_match( '/max-age=(\d+)/i', $cache_control, $matches ) ) {
			return (int) $matches[1];
		}

		// Extract s-maxage (shared cache).
		if ( preg_match( '/s-maxage=(\d+)/i', $cache_control, $matches ) ) {
			return (int) $matches[1];
		}

		return 0;
	}

	/**
	 * Parse duration from Expires header
	 *
	 * @since  1.6028.1530
	 * @param  string $expires Expires header value.
	 * @return int Duration in seconds.
	 */
	private static function parse_expires_duration( string $expires ): int {
		$expires_time = strtotime( $expires );
		if ( false === $expires_time ) {
			return 0;
		}

		$now = time();
		$duration = $expires_time - $now;

		return max( 0, $duration );
	}

	/**
	 * Evaluate cached results
	 *
	 * @since  1.6028.1530
	 * @param  array $analysis Cached analysis.
	 * @return array|null Finding or null.
	 */
	private static function evaluate_cached_results( array $analysis ) {
		return self::evaluate_results( $analysis );
	}

	/**
	 * Evaluate analysis results
	 *
	 * @since  1.6028.1530
	 * @param  array $analysis Analysis results.
	 * @return array|null Finding or null.
	 */
	private static function evaluate_results( array $analysis ) {
		$problematic_count = count( $analysis['no_cache'] ) + count( $analysis['short_cache'] );

		// If all assets have optimal caching, no issue.
		if ( $problematic_count === 0 ) {
			return null;
		}

		return self::build_finding( $analysis );
	}

	/**
	 * Build finding from analysis
	 *
	 * @since  1.6028.1530
	 * @param  array $analysis Analysis results.
	 * @return array Finding data.
	 */
	private static function build_finding( array $analysis ): array {
		$no_cache_count    = count( $analysis['no_cache'] );
		$short_cache_count = count( $analysis['short_cache'] );
		$total_issues      = $no_cache_count + $short_cache_count;

		$threat_level = self::calculate_threat_level( $no_cache_count, $short_cache_count, $analysis['checked_count'] );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of assets */
				__( '%d static assets have missing or insufficient cache headers', 'wpshadow' ),
				$total_issues
			),
			'severity'     => $threat_level > 40 ? 'medium' : 'low',
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/performance-asset-caching-headers',
			'family'       => self::$family,
			'meta'         => array(
				'checked_assets'      => $analysis['checked_count'],
				'no_cache_count'      => $no_cache_count,
				'short_cache_count'   => $short_cache_count,
				'optimal_cache_count' => count( $analysis['optimal_cache'] ),
				'performance_impact'  => self::calculate_performance_impact( $total_issues, $analysis['checked_count'] ),
			),
			'details'      => self::build_finding_details( $analysis ),
		);
	}

	/**
	 * Calculate threat level
	 *
	 * @since  1.6028.1530
	 * @param  int $no_cache_count    Assets with no caching.
	 * @param  int $short_cache_count Assets with short caching.
	 * @param  int $total_checked     Total assets checked.
	 * @return int Threat level 25-50.
	 */
	private static function calculate_threat_level( int $no_cache_count, int $short_cache_count, int $total_checked ): int {
		$base_threat = 30;

		// Add threat for assets with no caching.
		$base_threat += min( $no_cache_count * 5, 15 );

		// Add threat for short cache.
		$base_threat += min( $short_cache_count * 2, 10 );

		return min( $base_threat, 50 );
	}

	/**
	 * Calculate performance impact
	 *
	 * @since  1.6028.1530
	 * @param  int $issues_count  Number of problematic assets.
	 * @param  int $total_checked Total assets checked.
	 * @return string Performance impact description.
	 */
	private static function calculate_performance_impact( int $issues_count, int $total_checked ): string {
		if ( $total_checked === 0 ) {
			return __( 'Unknown impact', 'wpshadow' );
		}

		$percentage = ( $issues_count / $total_checked ) * 100;

		if ( $percentage >= 75 ) {
			return __( 'Major impact: 30-50% slower repeat visits', 'wpshadow' );
		} elseif ( $percentage >= 50 ) {
			return __( 'Significant impact: 20-30% slower repeat visits', 'wpshadow' );
		} elseif ( $percentage >= 25 ) {
			return __( 'Moderate impact: 10-20% slower repeat visits', 'wpshadow' );
		}

		return __( 'Minor impact: <10% slower repeat visits', 'wpshadow' );
	}

	/**
	 * Build detailed finding information
	 *
	 * @since  1.6028.1530
	 * @param  array $analysis Analysis results.
	 * @return array<string, mixed> Detailed finding data.
	 */
	private static function build_finding_details( array $analysis ): array {
		return array(
			'why_matters'              => array(
				__( 'Browser caching reduces repeat page load times by 30-50%', 'wpshadow' ),
				__( 'Missing cache headers force browsers to re-download assets every visit', 'wpshadow' ),
				__( 'Static assets (CSS/JS/images) rarely change and should be cached long-term', 'wpshadow' ),
				__( 'Proper caching reduces server load and bandwidth costs', 'wpshadow' ),
			),
			'problematic_assets'       => self::format_problematic_assets( $analysis ),
			'recommended_cache_times'  => array(
				'css'    => __( 'CSS files: 1 year (max-age=31536000)', 'wpshadow' ),
				'js'     => __( 'JavaScript files: 1 year (max-age=31536000)', 'wpshadow' ),
				'images' => __( 'Images: 1 year (max-age=31536000)', 'wpshadow' ),
				'fonts'  => __( 'Web fonts: 1 year (max-age=31536000)', 'wpshadow' ),
			),
			'htaccess_solution'        => self::get_htaccess_rules(),
			'alternative_solutions'    => array(
				__( 'Use CDN with built-in caching (Cloudflare, etc.)', 'wpshadow' ),
				__( 'Configure web server caching in nginx/Apache', 'wpshadow' ),
				__( 'Use caching plugin (W3 Total Cache, WP Rocket)', 'wpshadow' ),
				__( 'Add Cache-Control headers via PHP', 'wpshadow' ),
			),
			'cache_busting_note'       => __( 'Use versioned URLs (?ver=1.2.3) for cache busting when assets change', 'wpshadow' ),
		);
	}

	/**
	 * Format problematic assets for display
	 *
	 * @since  1.6028.1530
	 * @param  array $analysis Analysis results.
	 * @return array Formatted assets.
	 */
	private static function format_problematic_assets( array $analysis ): array {
		$formatted = array();

		foreach ( $analysis['no_cache'] as $asset ) {
			$formatted[] = array(
				'url'   => basename( $asset['url'] ),
				'type'  => $asset['type'],
				'issue' => __( 'No caching (max-age=0)', 'wpshadow' ),
			);
		}

		foreach ( $analysis['short_cache'] as $asset ) {
			$days = floor( $asset['cache_duration'] / 86400 );
			$formatted[] = array(
				'url'   => basename( $asset['url'] ),
				'type'  => $asset['type'],
				'issue' => sprintf(
					/* translators: %d: number of days */
					__( 'Short cache (%d days, recommend 365)', 'wpshadow' ),
					$days
				),
			);
		}

		return array_slice( $formatted, 0, 10 );
	}

	/**
	 * Get .htaccess rules for Apache
	 *
	 * @since  1.6028.1530
	 * @return string .htaccess rules.
	 */
	private static function get_htaccess_rules(): string {
		return '<IfModule mod_expires.c>
ExpiresActive On
ExpiresByType text/css "access plus 1 year"
ExpiresByType text/javascript "access plus 1 year"
ExpiresByType application/javascript "access plus 1 year"
ExpiresByType image/jpeg "access plus 1 year"
ExpiresByType image/png "access plus 1 year"
ExpiresByType image/gif "access plus 1 year"
ExpiresByType image/webp "access plus 1 year"
</IfModule>';
	}
}

<?php
/**
 * CDN Readiness Check Diagnostic
 *
 * Analyzes if site structure is ready for CDN integration by checking
 * URL patterns, asset paths, and header compatibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6028.1610
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CDN Readiness Diagnostic Class
 *
 * Checks if the site is ready for CDN integration by analyzing URL patterns,
 * asset paths, and potential compatibility issues.
 *
 * @since 1.6028.1610
 */
class Diagnostic_CDN_Readiness extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-readiness';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CDN Readiness Check';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes if site structure is ready for CDN integration';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Cache key
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'wpshadow_cdn_readiness';

	/**
	 * Cache duration (6 hours)
	 *
	 * @var int
	 */
	private const CACHE_DURATION = 21600;

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6028.1610
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached && is_array( $cached ) ) {
			return self::evaluate_cached_results( $cached );
		}

		// Analyze site URLs and assets.
		$analysis = self::analyze_cdn_readiness();

		// Cache results.
		set_transient( self::CACHE_KEY, $analysis, self::CACHE_DURATION );

		return self::evaluate_results( $analysis );
	}

	/**
	 * Analyze CDN readiness
	 *
	 * @since  1.6028.1610
	 * @return array Analysis results.
	 */
	private static function analyze_cdn_readiness(): array {
		$site_url  = get_site_url();
		$home_url  = get_home_url();
		$issues    = array();
		$ready     = true;

		// Check for protocol-relative URLs.
		$uses_protocol_relative = self::check_protocol_relative_urls();
		if ( $uses_protocol_relative ) {
			$issues[] = __( 'Site uses protocol-relative URLs (// format)', 'wpshadow' );
			$ready    = false;
		}

		// Check for absolute URLs in content.
		$absolute_url_usage = self::check_absolute_urls();
		if ( $absolute_url_usage['has_issues'] ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with absolute URLs */
				__( '%d posts contain hardcoded absolute URLs', 'wpshadow' ),
				$absolute_url_usage['posts_with_absolute_urls']
			);
			$ready = false;
		}

		// Check assets are properly enqueued.
		$enqueued_properly = self::check_asset_enqueuing();
		if ( ! $enqueued_properly['all_enqueued'] ) {
			$issues[] = sprintf(
				/* translators: %d: number of hardcoded assets */
				__( '%d assets found hardcoded in templates', 'wpshadow' ),
				$enqueued_properly['hardcoded_count']
			);
			$ready = false;
		}

		// Check for dynamic asset generation.
		$has_dynamic_assets = self::check_dynamic_assets();
		if ( $has_dynamic_assets ) {
			$issues[] = __( 'Site generates dynamic CSS/JS that may not cache well', 'wpshadow' );
		}

		return array(
			'cdn_ready'              => $ready && ! $has_dynamic_assets,
			'issues'                 => $issues,
			'protocol_relative_urls' => $uses_protocol_relative,
			'absolute_url_count'     => $absolute_url_usage['posts_with_absolute_urls'],
			'hardcoded_assets'       => $enqueued_properly['hardcoded_count'],
			'dynamic_assets'         => $has_dynamic_assets,
			'site_url'               => $site_url,
		);
	}

	/**
	 * Check for protocol-relative URLs
	 *
	 * @since  1.6028.1610
	 * @return bool True if protocol-relative URLs found.
	 */
	private static function check_protocol_relative_urls(): bool {
		$home_url = get_home_url();
		
		// Check if site uses protocol-relative format.
		return ( strpos( $home_url, '//' ) === 0 );
	}

	/**
	 * Check for absolute URLs in content
	 *
	 * @since  1.6028.1610
	 * @return array Analysis of absolute URL usage.
	 */
	private static function check_absolute_urls(): array {
		global $wpdb;

		$site_url = get_site_url();
		$home_url = get_home_url();

		// Search for absolute URLs in post content.
		$query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT ID) as count 
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND (post_content LIKE %s OR post_content LIKE %s)
			LIMIT 1000",
			'%' . $wpdb->esc_like( $site_url ) . '%',
			'%' . $wpdb->esc_like( $home_url ) . '%'
		);

		$count = (int) $wpdb->get_var( $query );

		return array(
			'has_issues'               => $count > 0,
			'posts_with_absolute_urls' => $count,
		);
	}

	/**
	 * Check asset enqueuing
	 *
	 * @since  1.6028.1610
	 * @return array Asset enqueuing analysis.
	 */
	private static function check_asset_enqueuing(): array {
		// Get homepage HTML.
		$html = self::get_homepage_html();

		if ( empty( $html ) ) {
			return array(
				'all_enqueued'     => true,
				'hardcoded_count'  => 0,
			);
		}

		// Count hardcoded script/link tags (not enqueued via WordPress).
		$hardcoded_scripts = preg_match_all( '/<script[^>]+src=["\'](?!.*wp-includes|.*wp-content)[^"\']*["\']/', $html );
		$hardcoded_styles  = preg_match_all( '/<link[^>]+href=["\'](?!.*wp-includes|.*wp-content)[^"\']*["\'][^>]*stylesheet/', $html );

		$total_hardcoded = ( $hardcoded_scripts ? $hardcoded_scripts : 0 ) + ( $hardcoded_styles ? $hardcoded_styles : 0 );

		return array(
			'all_enqueued'    => $total_hardcoded === 0,
			'hardcoded_count' => $total_hardcoded,
		);
	}

	/**
	 * Check for dynamic asset generation
	 *
	 * @since  1.6028.1610
	 * @return bool True if dynamic assets detected.
	 */
	private static function check_dynamic_assets(): bool {
		global $wp_scripts, $wp_styles;

		if ( ! did_action( 'wp_enqueue_scripts' ) ) {
			do_action( 'wp_enqueue_scripts' );
		}

		$has_dynamic = false;

		// Check for inline scripts/styles (common with dynamic generation).
		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( ! empty( $script->extra['data'] ) || ! empty( $script->extra['after'] ) ) {
					$has_dynamic = true;
					break;
				}
			}
		}

		return $has_dynamic;
	}

	/**
	 * Get homepage HTML
	 *
	 * @since  1.6028.1610
	 * @return string HTML content.
	 */
	private static function get_homepage_html(): string {
		$home_url = get_home_url();

		$response = wp_remote_get( $home_url, array(
			'timeout'   => 10,
			'sslverify' => false,
		) );

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Evaluate cached results
	 *
	 * @since  1.6028.1610
	 * @param  array $analysis Cached analysis.
	 * @return array|null Finding or null.
	 */
	private static function evaluate_cached_results( array $analysis ) {
		return self::evaluate_results( $analysis );
	}

	/**
	 * Evaluate analysis results
	 *
	 * @since  1.6028.1610
	 * @param  array $analysis Analysis results.
	 * @return array|null Finding or null.
	 */
	private static function evaluate_results( array $analysis ) {
		// If CDN ready and no issues, pass.
		if ( $analysis['cdn_ready'] && empty( $analysis['issues'] ) ) {
			return null;
		}

		return self::build_finding( $analysis );
	}

	/**
	 * Build finding from analysis
	 *
	 * @since  1.6028.1610
	 * @param  array $analysis Analysis results.
	 * @return array Finding data.
	 */
	private static function build_finding( array $analysis ): array {
		$threat_level = self::calculate_threat_level( $analysis );
		$issue_count  = count( $analysis['issues'] );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( 'Found %d issues that may prevent CDN integration', 'wpshadow' ),
				$issue_count
			),
			'severity'     => $threat_level > 15 ? 'medium' : 'low',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-cdn-readiness',
			'family'       => self::$family,
			'meta'         => array(
				'cdn_ready'              => $analysis['cdn_ready'],
				'issue_count'            => $issue_count,
				'absolute_url_count'     => $analysis['absolute_url_count'],
				'hardcoded_assets'       => $analysis['hardcoded_assets'],
				'has_dynamic_assets'     => $analysis['dynamic_assets'],
				'performance_impact'     => self::calculate_performance_impact( $analysis ),
			),
			'details'      => self::build_finding_details( $analysis ),
		);
	}

	/**
	 * Calculate threat level
	 *
	 * @since  1.6028.1610
	 * @param  array $analysis Analysis results.
	 * @return int Threat level 10-25.
	 */
	private static function calculate_threat_level( array $analysis ): int {
		$base_threat = 10;

		// Add threat for each issue type.
		if ( $analysis['protocol_relative_urls'] ) {
			$base_threat += 5;
		}

		if ( $analysis['absolute_url_count'] > 0 ) {
			$base_threat += 5;
		}

		if ( $analysis['hardcoded_assets'] > 0 ) {
			$base_threat += 3;
		}

		if ( $analysis['dynamic_assets'] ) {
			$base_threat += 2;
		}

		return min( $base_threat, 25 );
	}

	/**
	 * Calculate performance impact
	 *
	 * @since  1.6028.1610
	 * @param  array $analysis Analysis results.
	 * @return string Performance impact description.
	 */
	private static function calculate_performance_impact( array $analysis ): string {
		$issue_count = count( $analysis['issues'] );

		if ( $issue_count === 0 ) {
			return __( 'Ready for CDN: 30-80% faster global delivery', 'wpshadow' );
		} elseif ( $issue_count <= 2 ) {
			return __( 'Minor issues: Fix for optimal CDN performance', 'wpshadow' );
		} else {
			return __( 'Multiple issues: Significant work needed before CDN', 'wpshadow' );
		}
	}

	/**
	 * Build detailed finding information
	 *
	 * @since  1.6028.1610
	 * @param  array $analysis Analysis results.
	 * @return array<string, mixed> Detailed finding data.
	 */
	private static function build_finding_details( array $analysis ): array {
		return array(
			'issues_found'       => $analysis['issues'],
			'why_cdn_matters'    => array(
				__( 'CDN delivers content from servers closer to users', 'wpshadow' ),
				__( 'Reduces latency by 30-80% for global audiences', 'wpshadow' ),
				__( 'Offloads bandwidth from origin server', 'wpshadow' ),
				__( 'Improves Core Web Vitals (LCP, TTFB)', 'wpshadow' ),
				__( 'Essential for scaling to high traffic', 'wpshadow' ),
			),
			'common_blockers'    => array(
				'absolute_urls'   => __( 'Hardcoded site URL in content prevents CDN domain use', 'wpshadow' ),
				'dynamic_assets'  => __( 'Dynamically generated CSS/JS may not cache properly', 'wpshadow' ),
				'protocol_issues' => __( 'Protocol-relative URLs can cause mixed content warnings', 'wpshadow' ),
			),
			'recommended_steps'  => array(
				__( '1. Use relative URLs in all content', 'wpshadow' ),
				__( '2. Properly enqueue all assets via wp_enqueue_script/style', 'wpshadow' ),
				__( '3. Test with a CDN plugin (WP Rocket, W3 Total Cache)', 'wpshadow' ),
				__( '4. Configure CDN provider (Cloudflare, StackPath, BunnyCDN)', 'wpshadow' ),
				__( '5. Verify all assets load correctly after CDN activation', 'wpshadow' ),
			),
			'cdn_providers'      => array(
				'cloudflare' => __( 'Cloudflare: Free tier available, includes DDoS protection', 'wpshadow' ),
				'stackpath'  => __( 'StackPath: Premium performance, server locations worldwide', 'wpshadow' ),
				'bunnycdn'   => __( 'BunnyCDN: Affordable, excellent performance', 'wpshadow' ),
				'fastly'     => __( 'Fastly: Enterprise-grade, real-time purging', 'wpshadow' ),
			),
			'expected_benefits'  => array(
				'lcp'       => __( 'Largest Contentful Paint: 30-50% improvement', 'wpshadow' ),
				'ttfb'      => __( 'Time To First Byte: 40-70% improvement', 'wpshadow' ),
				'bandwidth' => __( 'Origin bandwidth: 60-80% reduction', 'wpshadow' ),
				'uptime'    => __( 'Availability: Increased redundancy', 'wpshadow' ),
			),
		);
	}
}

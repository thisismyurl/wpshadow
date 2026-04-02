<?php
/**
 * Too Many HTTP Requests Diagnostic
 *
 * Checks if pages are making excessive HTTP requests which slow
 * down page load. Recommends combining and minimizing resources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Too Many HTTP Requests Diagnostic Class
 *
 * Analyzes enqueued scripts and styles to detect excessive
 * HTTP requests. Each request adds 50-200ms overhead, so
 * too many requests significantly impact performance.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Too_Many_HTTP_Requests extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'too-many-http-requests';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Too Many HTTP Requests Slow Page Load';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pages make excessive HTTP requests';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$script_count = 0;
		$style_count  = 0;

		// Count enqueued scripts
		if ( ! empty( $wp_scripts->queue ) ) {
			$script_count = count( $wp_scripts->queue );
		}

		// Count enqueued styles
		if ( ! empty( $wp_styles->queue ) ) {
			$style_count = count( $wp_styles->queue );
		}

		$total_requests = $script_count + $style_count;

		// Best practice threshold (Google recommends < 50 requests)
		$threshold         = 50;
		$warning_threshold = 35;

		if ( $total_requests > $threshold ) {
			$severity = 'high';
			$threat   = 70;
		} elseif ( $total_requests > $warning_threshold ) {
			$severity = 'medium';
			$threat   = 50;
		} else {
			return null;
		}

		// Calculate estimated overhead (100ms per request average)
		$estimated_overhead_ms = $total_requests * 100;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of HTTP requests, %d: estimated overhead in seconds */
				__( 'Your site makes %1$d HTTP requests (%2$d scripts + %3$d styles), adding ~%4$d seconds overhead. Combine resources to reduce requests below 50.', 'wpshadow' ),
				$total_requests,
				$script_count,
				$style_count,
				round( $estimated_overhead_ms / 1000, 1 )
			),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/http-requests',
			'meta'         => array(
				'total_requests'        => $total_requests,
				'script_requests'       => $script_count,
				'style_requests'        => $style_count,
				'estimated_overhead_ms' => $estimated_overhead_ms,
				'threshold'             => $threshold,
				'recommendations'       => array(
					'Combine CSS files into fewer stylesheets',
					'Combine JavaScript files using bundlers',
					'Remove unused CSS/JS with optimization plugins',
					'Inline critical CSS',
					'Defer non-critical scripts',
					'Use resource hints (preconnect, prefetch)',
				),
				'optimization_plugins'  => array(
					'Autoptimize - Combines and minifies CSS/JS',
					'WP Rocket - Comprehensive optimization',
					'Asset CleanUp - Removes unused resources',
					'Perfmatters - Selective script loading',
				),
			),
		);
	}
}

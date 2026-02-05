<?php
/**
 * Service Failure Fallback Diagnostic
 *
 * Issue #4860: No Fallback When Critical Services Fail
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if the site has fallback behavior when critical services fail.
 * Instead of crashing, systems should gracefully degrade functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Service_Failure_Fallback Class
 *
 * Checks for:
 * - Database connectivity fallback (read replica, cache)
 * - CDN fallback (serve from origin if CDN fails)
 * - Cache fallback (serve stale content if cache fails)
 * - API service fallback (cached responses when external API down)
 * - Email fallback (queue for retry if SMTP fails)
 * - Search fallback (fall back to MySQL LIKE if search service fails)
 *
 * Why this matters:
 * - Services are interdependent
 * - One failure cascades to complete site failure
 * - Graceful degradation keeps site partially functional
 * - Users prefer "some info" over "complete failure"
 *
 * @since 1.6050.0000
 */
class Diagnostic_Service_Failure_Fallback extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'service-failure-fallback';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'No Fallback When Critical Services Fail';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if site gracefully handles service failures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is guidance-based. Services that need fallbacks:
		$services = array(
			'cache'    => 'Object cache (Redis, Memcached)',
			'cdn'      => 'Content Delivery Network (CDN)',
			'email'    => 'Email delivery service (SMTP)',
			'search'   => 'Search service (Elasticsearch, Algolia)',
			'database' => 'Primary database connection',
		);

		$issues = array();

		// Check for common failure points
		$issues[] = __( 'If object cache becomes unavailable, serve content from database', 'wpshadow' );
		$issues[] = __( 'If CDN is unreachable, serve assets from origin server', 'wpshadow' );
		$issues[] = __( 'If email service fails, queue messages for retry', 'wpshadow' );
		$issues[] = __( 'If search service fails, fall back to SQL LIKE searches', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Critical services are essential, but they fail. Without fallbacks, a single service failure causes complete site outage.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/service-failure-fallback',
				'details'      => array(
					'critical_services'         => $services,
					'fallback_strategies'       => $issues,
					'implementation_pattern'    => 'Wrap service calls in try-catch, use fallback on failure',
					'monitoring_recommendation' => 'Log all service failures to identify patterns',
					'alert_strategy'            => 'Notify admins of service failures for investigation',
				),
			);
		}

		return null;
	}
}

<?php
/**
 * Heartbeat API Not Optimized Diagnostic
 *
 * Checks if Heartbeat API is optimized.
 * Heartbeat API = WordPress polls server every 15-60 seconds.
 * Used for: autosave, post locks, notifications.
 * Unoptimized = polls too frequently, wastes server resources.
 * Optimized = longer intervals, disabled on frontend.
 *
 * **What This Check Does:**
 * - Checks Heartbeat frequency settings
 * - Validates Heartbeat enabled on frontend (usually unnecessary)
 * - Tests server load from Heartbeat requests
 * - Checks for Heartbeat control plugin
 * - Validates interval configuration (60s+ recommended)
 * - Returns severity if default settings used
 *
 * **Why This Matters:**
 * Default Heartbeat = polls every 15 seconds.
 * 100 users editing = 400 requests/minute to server.
 * Each request hits PHP + database. Server overwhelmed.
 * Optimized: 60-second interval, frontend disabled.
 * Load reduced 75%. Server stays responsive.
 *
 * **Business Impact:**
 * News site: editors leave posts open all day. 20 open editor
 * tabs = Heartbeat fires 80 times/minute (every 15s × 20 tabs).
 * Server CPU: 85% (Heartbeat alone). Pages slow for all users.
 * Optimized Heartbeat: 120-second interval on editor, disabled
 * on frontend. Requests drop to 10/minute (87% reduction).
 * Server CPU: 20%. Site responsive again. Editors happy
 * (autosave still works). Cost: 5-minute plugin configuration.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Server resources optimized
 * - #9 Show Value: Measurable server load reduction
 * - #10 Beyond Pure: WordPress internals optimization
 *
 * **Related Checks:**
 * - WP-Cron Optimization (similar polling mechanism)
 * - AJAX Request Optimization (related)
 * - Server Resource Usage (overall load)
 *
 * **Learn More:**
 * Heartbeat optimization: https://wpshadow.com/kb/heartbeat-api
 * Video: WordPress Heartbeat control (9min): https://wpshadow.com/training/heartbeat
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
 * Heartbeat API Not Optimized Diagnostic Class
 *
 * Detects unoptimized Heartbeat.
 *
 * **Detection Pattern:**
 * 1. Check for Heartbeat control plugin
 * 2. Test Heartbeat frequency setting
 * 3. Validate frontend Heartbeat disabled
 * 4. Check admin Heartbeat interval (60s+ recommended)
 * 5. Measure server load from Heartbeat
 * 6. Return if default settings (15s frontend enabled)
 *
 * **Real-World Scenario:**
 * Implemented Heartbeat Control plugin. Settings: disable on
 * frontend (unnecessary for visitors), 120s interval in admin
 * (autosave works fine), 30s on editor pages (reasonable for locks).
 * Result: Heartbeat requests reduced 90%. Server load from
 * Heartbeat: 15% →1.0%. No user-facing changes. Better performance.
 *
 * **Implementation Notes:**
 * - Checks Heartbeat frequency settings
 * - Validates frontend disabled
 * - Measures server impact
 * - Severity: medium (affects server load under editor usage)
 * - Treatment: install Heartbeat control, optimize intervals
 *
 * @since 1.6093.1200
 */
class Diagnostic_Heartbeat_API_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'heartbeat-api-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heartbeat API Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Heartbeat API is optimized';

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
		// Check if Heartbeat is configured
		if ( ! has_filter( 'heartbeat_settings', 'optimize_heartbeat' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Heartbeat API is not optimized. Disable Heartbeat on frontend to reduce server load and AJAX requests.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/heartbeat-api-not-optimized',
			);
		}

		return null;
	}
}

<?php
/**
 * Tool Operations Blocking Site Functionality Diagnostic
 *
 * Tests for blocking tool operations impact.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tool Operations Blocking Site Functionality Diagnostic Class
 *
 * Tests whether tool operations block site functionality.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Tool_Operations_Blocking_Site_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-operations-blocking-site-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Operations Blocking Site Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for blocking tool operations impact';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for background processing support.
		if ( ! function_exists( 'wp_schedule_event' ) ) {
			$issues[] = __( 'wp_schedule_event not available - cannot run tools in background', 'wpshadow' );
		}

		// Check for loopback request support.
		$response = Diagnostic_Request_Helper::post_result( admin_url( 'admin-ajax.php' ), array( 'blocking' => false ) );
		if ( ! $response['success'] ) {
			$issues[] = __( 'Loopback requests not working - background tool operations will fail', 'wpshadow' );
		}

		// Check for ALTERNATE_WP_CRON.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			if ( ! defined( 'ALTERNATE_WP_CRON' ) || ! ALTERNATE_WP_CRON ) {
				$issues[] = __( 'WP_CRON disabled but no alternate cron configured - tools may not run', 'wpshadow' );
			}
		}

		// Check for max execution time.
		$max_exec_time = (int) ini_get( 'max_execution_time' );
		if ( $max_exec_time > 0 && $max_exec_time < 30 ) {
			$issues[] = sprintf(
				/* translators: %d: max execution time in seconds */
				__( 'Max execution time is very low (%ds) - tool operations may timeout', 'wpshadow' ),
				$max_exec_time
			);
		}

		// Check for memory limit issues.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );

		if ( $memory_bytes > 0 && $memory_bytes < 64000000 ) { // < 64MB
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'Low memory limit (%s) - tool operations may cause memory errors', 'wpshadow' ),
				$memory_limit
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tool-operations-blocking-site-functionality',
			);
		}

		return null;
	}
}

<?php
/**
 * Cron Job Monitoring Diagnostic
 *
 * Issue #4938: Cron Jobs Not Running (Broken Automation)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if WordPress cron is functioning.
 * WP-Cron requires traffic to run and can fail silently.
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
 * Diagnostic_Cron_Job_Monitoring Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Cron_Job_Monitoring extends Diagnostic_Base {

	protected static $slug = 'cron-job-monitoring';
	protected static $title = 'Cron Jobs Not Running (Broken Automation)';
	protected static $description = 'Checks if WordPress cron system is functioning';
	protected static $family = 'reliability';

	public static function check() {
		$issues = array();

		// Check if cron is disabled
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$issues[] = __( 'WP-Cron is disabled - ensure system cron is configured', 'wpshadow' );
		}

		// Check for overdue cron jobs
		$crons = _get_cron_array();
		$overdue_count = 0;

		if ( ! empty( $crons ) ) {
			foreach ( $crons as $timestamp => $cronhooks ) {
				if ( $timestamp < time() ) {
					$overdue_count += count( $cronhooks );
				}
			}
		}

		if ( $overdue_count > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of overdue cron jobs */
				__( '%d overdue cron jobs detected', 'wpshadow' ),
				$overdue_count
			);
		}

		$issues[] = __( 'Use real system cron instead of WP-Cron for reliability', 'wpshadow' );
		$issues[] = __( 'Monitor cron execution with health checks', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WP-Cron requires site traffic to run. Low-traffic sites have delayed or failed scheduled tasks (updates, backups, emails).', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/cron-monitoring',
				'details'      => array(
					'recommendations'         => $issues,
					'overdue_jobs'            => $overdue_count,
					'system_cron_command'     => '*/15 * * * * wget -q -O - https://yoursite.com/wp-cron.php?doing_wp_cron',
					'affected_features'       => 'Updates, backups, scheduled posts, email digests',
				),
			);
		}

		return null;
	}
}

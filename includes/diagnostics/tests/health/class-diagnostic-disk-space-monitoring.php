<?php
/**
 * Disk Space Monitoring Diagnostic
 *
 * Tracks available disk space to prevent "disk full" errors
 * that crash databases and stop email delivery.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Disk_Space_Monitoring Class
 *
 * Monitors available disk space.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Disk_Space_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'disk-space-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Disk Space Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tracks available disk space';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if disk space low, null otherwise.
	 */
	public static function check() {
		$disk_status = self::check_disk_space();

		if ( ! $disk_status['has_issue'] ) {
			return null; // Sufficient disk space
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: disk used percent */
				__( 'Disk %d%% full. When disk reaches 100%% = database crashes, emails fail, backups stop. Server won\'t accept new files.', 'wpshadow' ),
				$disk_status['used_percent']
			),
			'severity'     => $disk_status['severity'],
			'threat_level' => $disk_status['threat_level'],
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/disk-space',
			'family'       => self::$family,
			'meta'         => array(
				'disk_used_percent' => $disk_status['used_percent'] . '%',
				'free_space_gb'     => round( $disk_status['free_bytes'] / 1073741824, 2 ) . 'GB',
			),
			'details'      => array(
				'disk_space_benchmarks'       => array(
					'< 50% used' => 'Healthy - plenty of room',
					'50-80% used' => 'Good - monitoring recommended',
					'80-90% used' => 'Warning - cleanup needed',
					'90-95% used' => 'Critical - immediate action',
					'> 95% used' => 'Emergency - disk nearly full',
				),
				'when_disk_space_matters'     => array(
					__( 'Database backups need: Size of database (grow quickly)' ),
					__( 'File uploads: /wp-content/uploads (1-5GB typical)' ),
					__( 'Email queue: Unsent emails stored temporarily' ),
					__( 'Log files: /var/log grows daily' ),
					__( 'Caching: Object cache can use 100-500MB' ),
				),
				'causes_of_full_disk'         => array(
					'Large Uploads' => array(
						'Video files: 500MB-5GB each',
						'Backups: Database backup files',
						'Duplicate backups: Multiple copies accumulate',
					),
					'Log Files' => array(
						'Apache/error logs: 100MB-1GB monthly',
						'PHP errors: 50-500MB',
						'WordPress debug.log: 10-100MB',
					),
					'Database Backups' => array(
						'Backup plugins: Daily backups',
						'Old backups: Never deleted',
						'Example: 1GB backup × 30 days = 30GB',
					),
				),
				'freeing_disk_space'          => array(
					'Delete Old Backups' => array(
						'Keep: Last 7 days of daily backups',
						'Delete: Older than 30 days',
						'FTP/File Manager: /backups folder',
					),
					'Compress Old Logs' => array(
						'Rotate logs: Monthly to .gz files',
						'Tool: logrotate on Linux',
						'Space: 90% reduction (100MB → 10MB)',
					),
					'Clean Uploads' => array(
						'Find: /wp-content/uploads with large files',
						'Review: Delete unnecessary files',
						'Optimize: Compress images',
					),
					'Database Cleanup' => array(
						'Delete: Post revisions (wp_posts)',
						'Delete: Trash items',
						'Clean: Spam comments (wp_comments)',
					),
				),
				'automatic_cleanup'           => array(
					'Backup Retention' => array(
						'Setting: Keep only 7 daily backups',
						'Or: Keep 4 weekly backups',
						'Oldest automatically deleted',
					),
					'Log Rotation' => array(
						'Linux: logrotate + cron',
						'Windows: Scheduled task',
						'Compresses daily, keeps 30 days',
					),
					'Cache Clearing' => array(
						'Schedule: Clear cache monthly',
						'WP-Cron runs: Check it\'s working',
					),
				),
				'monitoring_disk_space'       => array(
					'Hosting Control Panel' => array(
						'cPanel: Home → Disk Usage',
						'Plesk: Server dashboard',
						'Shows: Real-time usage',
					),
					'Automatic Alerts' => array(
						'Set: Alert at 80% full',
						'Email: When approaching limit',
						'Action: Time to cleanup',
					),
					'Server Monitoring' => array(
						'New Relic: Infrastructure monitoring',
						'Datadog: Disk space dashboard',
						'CloudWatch (AWS): Metric tracking',
					),
				),
			),
		);
	}

	/**
	 * Check disk space.
	 *
	 * @since  1.2601.2148
	 * @return array Disk space status.
	 */
	private static function check_disk_space() {
		$total_bytes = disk_total_space( ABSPATH );
		$free_bytes = disk_free_space( ABSPATH );

		if ( false === $total_bytes || false === $free_bytes ) {
			// Cannot determine disk space
			return array(
				'has_issue'   => false,
				'used_percent' => 0,
				'free_bytes'  => 0,
				'severity'    => 'info',
				'threat_level' => 0,
			);
		}

		$used_bytes = $total_bytes - $free_bytes;
		$used_percent = round( ( $used_bytes / $total_bytes ) * 100 );

		$has_issue = $used_percent >= 80;
		$severity = 'info';
		$threat_level = 20;

		if ( $used_percent >= 95 ) {
			$severity = 'critical';
			$threat_level = 95;
		} elseif ( $used_percent >= 90 ) {
			$severity = 'high';
			$threat_level = 80;
		} elseif ( $used_percent >= 80 ) {
			$severity = 'medium';
			$threat_level = 55;
		}

		return array(
			'has_issue'    => $has_issue,
			'used_percent' => $used_percent,
			'free_bytes'   => $free_bytes,
			'severity'     => $severity,
			'threat_level' => $threat_level,
		);
	}
}

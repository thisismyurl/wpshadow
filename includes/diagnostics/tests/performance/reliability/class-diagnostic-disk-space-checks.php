<?php
/**
 * Disk Space Checks Diagnostic
 *
 * Checks whether there is enough disk space for large operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Reliability
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disk Space Checks Diagnostic Class
 *
 * Verifies that disk space is adequate before large operations.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Disk_Space_Checks extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'disk-space-checks';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Disk Space Checks Before Large Operations';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if disk space is sufficient for large tasks';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$path    = ABSPATH;
		$free    = @disk_free_space( $path );
		$total   = @disk_total_space( $path );

		if ( false === $free || false === $total || 0 === $total ) {
			return null;
		}

		$free_percent = ( $free / $total ) * 100;
		$stats['free_space']      = size_format( $free );
		$stats['total_space']     = size_format( $total );
		$stats['free_percent']    = round( $free_percent, 2 );

		$large_op_plugins = array(
			'updraftplus/updraftplus.php' => 'UpdraftPlus',
			'backwpup/backwpup.php'       => 'BackWPup',
			'wpvivid-backuprestore/wpvivid-backuprestore.php' => 'WPvivid',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'wp-super-cache/wp-cache.php' => 'WP Super Cache',
		);

		$has_large_ops = false;
		foreach ( $large_op_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_large_ops = true;
				$stats['large_operation_plugin'] = $plugin_name;
				break;
			}
		}

		if ( $free_percent < 10 || $free < 1073741824 ) { // 1GB
			$issues[] = __( 'Low disk space can interrupt backups, uploads, or migrations', 'wpshadow' );
		}

		if ( ! empty( $issues ) && $has_large_ops ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Large operations like backups and migrations should check disk space first. When space runs out mid-task, files can be corrupted or incomplete.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/disk-space-checks',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}

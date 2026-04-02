<?php
/**
 * Backup Last Run Diagnostic
 *
 * Checks when the last backup was executed to ensure recent recoverable copies exist.
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
 * Diagnostic_Backup_Last_Run Class
 *
 * Verifies that backups have run recently and reports how long it has been
 * since the last successful backup.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backup_Last_Run extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-last-run';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Last Run';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks when the last backup was executed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$backup_plugins = array(
			'updraftplus/updraftplus.php'           => array(
				'name' => 'UpdraftPlus',
				'last' => function () {
					return (int) get_option( 'updraft_last_backup', 0 );
				},
			),
			'backwpup/backwpup.php'                 => array(
				'name' => 'BackWPup',
				'last' => function () {
					$last = (int) get_option( 'backwpup_lastjob', 0 );
					if ( 0 === $last ) {
						$last = (int) get_option( 'backwpup_last_job', 0 );
					}
					return $last;
				},
			),
			'jetpack/jetpack.php'                   => array(
				'name' => 'Jetpack Backup',
				'last' => function () {
					return (int) get_option( 'jetpack_backup_last_success', 0 );
				},
			),
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => array(
				'name' => 'All-in-One WP Migration',
				'last' => function () {
					return (int) get_option( 'ai1wm_last_backup', 0 );
				},
			),
			'duplicator/duplicator.php'             => array(
				'name' => 'Duplicator',
				'last' => function () {
					return (int) get_option( 'duplicator_last_backup', 0 );
				},
			),
		);

		$active_backup   = null;
		$last_backup_time = (int) get_option( 'wpshadow_last_backup_time', 0 );
		$source          = 'WPShadow';

		foreach ( $backup_plugins as $plugin => $data ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup = $data['name'];
				if ( 0 === $last_backup_time && is_callable( $data['last'] ) ) {
					$last_backup_time = (int) call_user_func( $data['last'] );
					$source          = $data['name'];
				}
				break;
			}
		}

		if ( ! $active_backup ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No backup plugin detected. Recent backups are essential for recovery.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-last-run',
				'meta'         => array(
					'active_backup' => $active_backup,
				),
			);
		}

		if ( 0 === $last_backup_time ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backup plugin detected, but the last backup time could not be found. Confirm backups are running.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-last-run',
				'meta'         => array(
					'active_backup' => $active_backup,
					'source'        => $source,
				),
			);
		}

		$days_since_backup = (int) floor( ( time() - $last_backup_time ) / DAY_IN_SECONDS );

		if ( $days_since_backup >= 30 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of days */
					__( 'Last backup ran %d days ago. Monthly or more frequent backups are recommended.', 'wpshadow' ),
					$days_since_backup
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-last-run',
				'meta'         => array(
					'active_backup'     => $active_backup,
					'last_backup_time'  => $last_backup_time,
					'days_since_backup' => $days_since_backup,
					'source'            => $source,
				),
			);
		}

		if ( $days_since_backup >= 7 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of days */
					__( 'Last backup ran %d days ago. Weekly backups are recommended for active sites.', 'wpshadow' ),
					$days_since_backup
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-last-run',
				'meta'         => array(
					'active_backup'     => $active_backup,
					'last_backup_time'  => $last_backup_time,
					'days_since_backup' => $days_since_backup,
					'source'            => $source,
				),
			);
		}

		return null;
	}
}

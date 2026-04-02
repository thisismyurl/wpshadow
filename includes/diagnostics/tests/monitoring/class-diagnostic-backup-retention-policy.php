<?php
/**
 * Backup Retention Policy Diagnostic
 *
 * Validates backup retention length and restore window.
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
 * Backup Retention Policy Diagnostic Class
 *
 * Checks that backups are retained long enough for recovery.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backup_Retention_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-retention-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Retention Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backups are retained long enough to recover from issues';

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
		$retention_days = (int) get_option( 'wpshadow_backup_retention_days', 0 );

		if ( 0 === $retention_days ) {
			$retention_days = self::get_retention_from_plugins();
		}

		if ( 0 === $retention_days ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backup retention policy is not configured. Set retention to at least 14 days for recovery safety.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-retention-policy',
			);
		}

		if ( $retention_days < 14 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: retention days */
					__( 'Backup retention is only %d days. Increase retention to at least 14 days to expand recovery options.', 'wpshadow' ),
					$retention_days
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-retention-policy',
				'meta'         => array(
					'retention_days' => $retention_days,
				),
			);
		}

		if ( $retention_days < 30 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: retention days */
					__( 'Backup retention is %d days. Consider extending to 30 days for a safer restore window.', 'wpshadow' ),
					$retention_days
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-retention-policy',
				'meta'         => array(
					'retention_days' => $retention_days,
				),
			);
		}

		return null;
	}

	/**
	 * Attempt to read retention settings from common plugins.
	 *
	 * @since 1.6093.1200
	 * @return int Retention days.
	 */
	private static function get_retention_from_plugins(): int {
		$updraft_retain = (int) get_option( 'updraft_retain', 0 );
		$updraft_retain_db = (int) get_option( 'updraft_retain_db', 0 );

		$retention = max( $updraft_retain, $updraft_retain_db );
		if ( $retention > 0 ) {
			return $retention;
		}

		return 0;
	}
}

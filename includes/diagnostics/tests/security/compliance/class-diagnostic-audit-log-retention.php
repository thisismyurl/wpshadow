<?php
/**
 * Audit Log Retention Diagnostic
 *
 * Verifies audit logs are retained for compliance requirements.
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
 * Diagnostic_Audit_Log_Retention Class
 *
 * Checks retention period for audit logs.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Audit_Log_Retention extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'audit-log-retention';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Audit Log Retention';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks audit log retention period for compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$retention_days = (int) get_option( 'wpshadow_audit_log_retention_days', 0 );

		if ( $retention_days > 0 && $retention_days < 365 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Audit log retention is under 1 year. Compliance often requires longer retention.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/audit-log-retention',
				'meta'         => array(
					'retention_days' => $retention_days,
				),
			);
		}

		return null;
	}
}
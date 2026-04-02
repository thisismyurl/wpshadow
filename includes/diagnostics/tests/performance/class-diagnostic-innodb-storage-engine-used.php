<?php
/**
 * InnoDB Storage Engine Used Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 74.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * InnoDB Storage Engine Used Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Innodb_Storage_Engine_Used extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'innodb-storage-engine-used';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'InnoDB Storage Engine Used';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for InnoDB Storage Engine Used. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check SHOW TABLE STATUS engine values.
	 *
	 * TODO Fix Plan:
	 * Fix by converting non-required MyISAM tables to InnoDB.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		$engine = Server_Env::get_db_engine();

		// Cannot determine engine (e.g. user lacks information_schema access) — skip.
		if ( '' === $engine ) {
			return null;
		}

		if ( Server_Env::is_innodb() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: current storage engine */
				__( 'Your WordPress tables are using the %s storage engine instead of InnoDB. InnoDB provides ACID-compliant transactions, row-level locking, crash recovery, and full-text search support. MyISAM and other legacy engines lack these features and have been deprecated in modern MySQL/MariaDB releases.', 'wpshadow' ),
				esc_html( $engine )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/innodb-storage-engine',
			'details'      => array(
				'current_engine'     => $engine,
				'recommended_engine' => 'InnoDB',
				'tested_on_table'    => $GLOBALS['wpdb']->posts,
			),
		);
	}
}

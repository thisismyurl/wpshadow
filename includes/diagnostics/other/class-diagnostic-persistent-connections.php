<?php
declare(strict_types=1);
/**
 * Database Persistent Connections Diagnostic
 *
 * Philosophy: Database security - prevent connection reuse leaks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if persistent database connections are used.
 */
class Diagnostic_Persistent_Connections extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		if ( ! defined( 'DB_HOST' ) ) {
			return null;
		}

		$db_host = DB_HOST;

		// Check for persistent connection prefix
		if ( strpos( $db_host, 'p:' ) === 0 ) {
			return array(
				'id'            => 'persistent-connections',
				'title'         => 'Persistent Database Connections Enabled',
				'description'   => 'DB_HOST uses "p:" prefix enabling persistent MySQL connections. This can leak temporary tables, session variables, and transactions between requests. Remove "p:" prefix unless specifically needed.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/disable-persistent-connections/',
				'training_link' => 'https://wpshadow.com/training/database-security/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
			);
		}

		return null;
	}
}

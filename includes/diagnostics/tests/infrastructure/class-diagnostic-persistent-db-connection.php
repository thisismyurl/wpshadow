<?php
/**
 * Diagnostic: Persistent DB Connection
 *
 * Checks if persistent database connections are enabled at PHP level.
 * Persistent connections can cause stale connections or resource exhaustion on shared hosts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Persistent_Db_Connection
 *
 * Tests persistent DB connection settings.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Persistent_Db_Connection extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'persistent-db-connection';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Persistent DB Connection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if persistent DB connections are enabled in PHP';

	/**
	 * Check persistent DB connection settings.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$mysqli_persistent = ini_get( 'mysqli.allow_persistent' );
		$mysql_persistent  = ini_get( 'mysql.allow_persistent' );

		$enabled = ( '1' === $mysqli_persistent || 'On' === $mysqli_persistent || '1' === $mysql_persistent || 'On' === $mysql_persistent );

		if ( $enabled ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Persistent database connections are enabled at the PHP level. On shared or resource-constrained environments this can lead to stale connections or resource exhaustion. Consider disabling persistent connections.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/persistent_db_connection',
				'meta'        => array(
					'mysqli_allow_persistent' => $mysqli_persistent,
					'mysql_allow_persistent'  => $mysql_persistent,
				),
			);
		}

		return null;
	}
}

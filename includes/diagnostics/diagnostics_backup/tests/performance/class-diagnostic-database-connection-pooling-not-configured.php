<?php
/**
 * Database Connection Pooling Not Configured Diagnostic
 *
 * Checks if database connection pooling is set up.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2335
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Connection Pooling Not Configured Diagnostic Class
 *
 * Detects missing connection pooling.
 *
 * @since 1.2601.2335
 */
class Diagnostic_Database_Connection_Pooling_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-connection-pooling-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Connection Pooling Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if connection pooling is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if persistent connections are enabled
		if ( ! defined( 'DB_HOST' ) || ! strpos( DB_HOST, 'p:' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database connection pooling is not configured. Use persistent connections (p: prefix) to improve performance under load.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-connection-pooling-not-configured',
			);
		}

		return null;
	}
}

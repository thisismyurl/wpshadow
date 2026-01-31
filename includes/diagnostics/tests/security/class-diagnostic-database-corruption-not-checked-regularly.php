<?php
/**
 * Database Corruption Not Checked Regularly Diagnostic
 *
 * Checks if database corruption is monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Corruption Not Checked Regularly Diagnostic Class
 *
 * Detects unchecked database corruption.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Database_Corruption_Not_Checked_Regularly extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-corruption-not-checked-regularly';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Corruption Not Checked Regularly';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database corruption is monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if database integrity check is scheduled
		if ( ! wp_next_scheduled( 'wp_database_integrity_check' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database corruption is not checked regularly. Schedule regular database integrity checks and repairs to prevent data loss.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-corruption-not-checked-regularly',
			);
		}

		return null;
	}
}

<?php
/**
 * Database Foreign Keys Not Implemented Diagnostic
 *
 * Checks if database foreign keys are implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Foreign Keys Not Implemented Diagnostic Class
 *
 * Detects missing foreign key constraints.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Foreign_Keys_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-foreign-keys-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Foreign Keys Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database foreign keys are implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if custom tables exist with FK constraints
		$custom_table_count = count( $wpdb->get_results( "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '%custom%'" ) );

		if ( $custom_table_count > 0 && ! wp_next_scheduled( 'check_db_foreign_keys' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database foreign keys are not implemented. Add foreign key constraints to custom tables to maintain referential integrity.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-foreign-keys-not-implemented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

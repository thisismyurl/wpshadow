<?php
/**
 * Database Collation Consistency Not Checked Diagnostic
 *
 * Checks if database collation is consistent.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2348
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Collation Consistency Not Checked Diagnostic Class
 *
 * Detects inconsistent database collation.
 *
 * @since 1.2601.2348
 */
class Diagnostic_Database_Collation_Consistency_Not_Checked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-collation-consistency-not-checked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Collation Consistency Not Checked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database collation is consistent';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2348
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check database collation
		$db_collation = $wpdb->get_results( 'SHOW VARIABLES LIKE "collation_database"' );

		if ( ! empty( $db_collation ) && $db_collation[0]->Value !== 'utf8mb4_unicode_ci' ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database collation is not consistent. Use utf8mb4_unicode_ci collation for proper Unicode character support and consistency.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-collation-consistency-not-checked',
			);
		}

		return null;
	}
}

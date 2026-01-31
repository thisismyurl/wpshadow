<?php
/**
 * Database Collation Not Optimized Diagnostic
 *
 * Checks if database collation is properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Collation Not Optimized Diagnostic Class
 *
 * Detects inefficient database collation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Database_Collation_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-collation-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Collation Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database collation is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get current database collation
		$db_collation = $wpdb->get_var( 'SELECT @@collation_database' );

		// Recommended collation
		if ( strpos( $db_collation, 'utf8mb4' ) === false ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Database collation is "%s" but recommended is "utf8mb4_unicode_ci". This may affect international character handling.', 'wpshadow' ),
					esc_html( $db_collation )
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/database-collation-not-optimized',
			);
		}

		return null;
	}
}

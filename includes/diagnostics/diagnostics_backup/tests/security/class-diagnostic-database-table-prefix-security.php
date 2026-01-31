<?php
/**
 * Database Table Prefix Security Diagnostic
 *
 * Verifies database uses non-default prefix (not wp_).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Table Prefix Security Class
 *
 * Tests table prefix.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Database_Table_Prefix_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-table-prefix-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Table Prefix Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies database uses non-default prefix (not wp_)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$prefix = $wpdb->prefix;
		
		// Check if using default prefix.
		if ( 'wp_' === $prefix ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Using default wp_ table prefix (vulnerable to automated SQL injection attacks)', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-table-prefix-security',
				'meta'         => array(
					'current_prefix' => $prefix,
					'is_default'     => true,
				),
			);
		}

		return null;
	}
}

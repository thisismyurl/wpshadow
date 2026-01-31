<?php
/**
 * WPDB Table Prefix Not Randomized Diagnostic
 *
 * Checks if database prefix is randomized.
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
 * WPDB Table Prefix Not Randomized Diagnostic Class
 *
 * Detects default table prefix.
 *
 * @since 1.2601.2352
 */
class Diagnostic_WPDB_Table_Prefix_Not_Randomized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wpdb-table-prefix-not-randomized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WPDB Table Prefix Not Randomized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database prefix is randomized';

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
		global $wpdb;

		// Check if default prefix is used
		if ( 'wp_' === $wpdb->prefix ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database table prefix is not randomized. Change the table prefix from "wp_" to a unique value to reduce SQL injection vulnerability.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/wpdb-table-prefix-not-randomized',
			);
		}

		return null;
	}
}

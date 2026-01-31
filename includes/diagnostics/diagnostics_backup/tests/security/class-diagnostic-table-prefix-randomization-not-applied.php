<?php
/**
 * Table Prefix Randomization Not Applied Diagnostic
 *
 * Checks if database table prefix is randomized.
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
 * Table Prefix Randomization Not Applied Diagnostic Class
 *
 * Detects default database prefix.
 *
 * @since 1.2601.2335
 */
class Diagnostic_Table_Prefix_Randomization_Not_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'table-prefix-randomization-not-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Table Prefix Randomization Not Applied';

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
	 * @since  1.2601.2335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if using default wp_ prefix
		if ( $wpdb->prefix === 'wp_' ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Database table prefix is the default "wp_". Change it to a random prefix to improve security against SQL injection attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/table-prefix-randomization-not-applied',
			);
		}

		return null;
	}
}

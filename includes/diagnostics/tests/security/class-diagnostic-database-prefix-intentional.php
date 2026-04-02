<?php
/**
 * Database Prefix Intentional Diagnostic
 *
 * Checks whether the WordPress database table prefix has been changed from
 * the default "wp_", which reduces the effectiveness of automated SQL injection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Database_Prefix_Intentional Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Prefix_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'database-prefix-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Database Prefix Intentional';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the WordPress database table prefix is still the default "wp_", which makes automated SQL injection attempts easier to craft.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Uses Server_Env::is_default_db_prefix() to detect whether the table prefix
	 * is still the default "wp_" and flags it as a security risk.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when default prefix is used, null when healthy.
	 */
	public static function check() {
		if ( ! Server_Env::is_default_db_prefix() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your database table prefix is the default "wp_". Automated SQL injection tools target this prefix. Changing it to a custom value is a low-cost hardening step that reduces the effectiveness of generic database attacks.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'kb_link'      => 'https://wpshadow.com/kb/database-prefix?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'current_prefix' => Server_Env::get_db_prefix(),
			),
		);
	}
}

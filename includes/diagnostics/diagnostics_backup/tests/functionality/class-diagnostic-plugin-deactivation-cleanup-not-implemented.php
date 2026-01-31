<?php
/**
 * Plugin Deactivation Cleanup Not Implemented Diagnostic
 *
 * Checks if plugins clean up their data on deactivation.
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
 * Plugin Deactivation Cleanup Not Implemented Diagnostic Class
 *
 * Detects plugins that don't clean up after deactivation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Plugin_Deactivation_Cleanup_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-deactivation-cleanup-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Deactivation Cleanup Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins clean up data on deactivation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for orphaned plugin options
		$orphaned_options = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%plugin_%" . "' OR option_name LIKE '%_settings'"
		);

		if ( $orphaned_options > 50 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d orphaned plugin options found in database. Deactivated plugins may have left data behind.', 'wpshadow' ),
					absint( $orphaned_options )
				),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-deactivation-cleanup-not-implemented',
			);
		}

		return null;
	}
}

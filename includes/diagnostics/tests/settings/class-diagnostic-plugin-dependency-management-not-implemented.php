<?php
/**
 * Plugin Dependency Management Not Implemented Diagnostic
 *
 * Checks if plugin dependencies are managed.
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
 * Plugin Dependency Management Not Implemented Diagnostic Class
 *
 * Detects missing plugin dependency management.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Dependency_Management_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-dependency-management-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Dependency Management Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugin dependencies are managed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if dependency management is configured
		if ( ! has_filter( 'plugins_loaded', 'wp_check_plugin_dependencies' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Plugin dependency management is not implemented. Define and manage plugin dependencies to prevent activation conflicts.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-dependency-management-not-implemented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

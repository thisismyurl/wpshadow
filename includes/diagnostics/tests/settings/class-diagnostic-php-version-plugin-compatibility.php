<?php
/**
 * PHP Version Plugin Compatibility Diagnostic
 *
 * Checks if installed plugins are compatible with the current PHP version.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2205
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP Version Plugin Compatibility Diagnostic Class
 *
 * Identifies active plugins that declare a higher minimum PHP requirement.
 *
 * @since 1.6030.2205
 */
class Diagnostic_PHP_Version_Plugin_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-version-plugin-compatibility';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version Plugin Compatibility';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if installed plugins are compatible with the current PHP version';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2205
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$php_version    = PHP_VERSION;
		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$incompatible   = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( ! in_array( $plugin_file, $active_plugins, true ) ) {
				continue;
			}

			$requires_php = isset( $plugin_data['RequiresPHP'] ) ? (string) $plugin_data['RequiresPHP'] : '';

			if ( '' !== $requires_php && version_compare( $php_version, $requires_php, '<' ) ) {
				$incompatible[] = array(
					'name'         => isset( $plugin_data['Name'] ) ? (string) $plugin_data['Name'] : $plugin_file,
					'requires_php' => $requires_php,
					'current_php'  => $php_version,
				);
			}
		}

		if ( empty( $incompatible ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: incompatible plugin count, 2: current PHP version */
				__( '%1$d active plugins require a newer PHP version than %2$s.', 'wpshadow' ),
				count( $incompatible ),
				$php_version
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/php-version-plugin-compatibility',
			'details'      => array(
				'current_php'          => $php_version,
				'incompatible_plugins' => $incompatible,
			),
		);
	}
}

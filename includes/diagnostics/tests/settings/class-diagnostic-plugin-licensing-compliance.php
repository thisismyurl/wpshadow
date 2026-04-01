<?php
/**
 * Plugin Licensing Compliance Diagnostic
 *
 * Checks whether plugins declare licensing information in headers.
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
 * Plugin Licensing Compliance Diagnostic Class
 *
 * Ensures active plugins have proper license declarations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Licensing_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-licensing-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Licensing Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugin headers for license information';

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
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$all_plugins = get_plugins();

		$missing_license = array();

		foreach ( $active_plugins as $plugin_file ) {
			if ( ! isset( $all_plugins[ $plugin_file ] ) ) {
				continue;
			}

			$plugin_data = $all_plugins[ $plugin_file ];
			$license = isset( $plugin_data['License'] ) ? trim( $plugin_data['License'] ) : '';
			$license_uri = isset( $plugin_data['LicenseURI'] ) ? trim( $plugin_data['LicenseURI'] ) : '';

			if ( '' === $license && '' === $license_uri ) {
				$missing_license[] = $plugin_data['Name'];
			}
		}

		if ( count( $missing_license ) > 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some active plugins are missing license information', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'missing_license' => array_slice( $missing_license, 0, 10 ),
					'total_missing'   => count( $missing_license ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-licensing-compliance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}

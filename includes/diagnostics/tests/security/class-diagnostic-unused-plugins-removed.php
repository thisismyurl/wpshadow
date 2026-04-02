<?php
/**
 * Unused Plugins Removed Diagnostic
 *
 * Checks whether inactive plugins remain installed, as deactivated plugins
 * still represent an attack surface through outdated or vulnerable code.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unused Plugins Removed Diagnostic Class
 *
 * Compares the full plugin list against the active_plugins option to
 * identify inactive plugins that have not been deleted from the filesystem.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Unused_Plugins_Removed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'unused-plugins-removed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Unused Plugins Removed';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether inactive plugins remain installed, as deactivated plugins still represent an attack surface through outdated or vulnerable code.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Calls get_plugins() and compares the full list against the active_plugins
	 * option (including network-active plugins on multisite), flagging sites
	 * with more than one inactive plugin installed.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when inactive plugins are present, null when healthy.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$active      = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$network_active = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) );
			$active         = array_unique( array_merge( $active, $network_active ) );
		}

		$inactive = array_diff( array_keys( $all_plugins ), $active );
		$count    = count( $inactive );

		// Allow one unused plugin — many sites keep a default Twenty* theme plugin or similar.
		if ( $count <= 1 ) {
			return null;
		}

		$names = array();
		foreach ( $inactive as $file ) {
			$names[] = isset( $all_plugins[ $file ]['Name'] ) ? $all_plugins[ $file ]['Name'] : $file;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of inactive plugins */
				_n(
					'%d inactive plugin is installed. Unused plugins are an unnecessary attack surface — their vulnerabilities can still be exploited even when deactivated.',
					'%d inactive plugins are installed. Unused plugins are an unnecessary attack surface — their vulnerabilities can still be exploited even when deactivated.',
					$count,
					'wpshadow'
				),
				$count
			),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => 'https://wpshadow.com/kb/unused-plugins-removed?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'inactive_count'   => $count,
				'inactive_plugins' => $names,
			),
		);
	}
}

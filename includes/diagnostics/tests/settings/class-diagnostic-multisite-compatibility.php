<?php
/**
 * Multisite Compatibility Diagnostic
 *
 * Checks multisite configuration and plugin compatibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Compatibility Diagnostic
 *
 * Validates multisite setup and known incompatibilities.
 *
 * @since 1.6030.2240
 */
class Diagnostic_Multisite_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks multisite configuration and plugin compatibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();
		$details = array();

		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$active_plugins  = get_option( 'active_plugins', array() );

		if ( empty( $network_plugins ) ) {
			$issues[] = __( 'No network-activated plugins detected for multisite', 'wpshadow' );
		}

		// Known plugins that commonly cause multisite issues.
		$incompatible = array(
			'backupbuddy/backupbuddy.php' => 'BackupBuddy',
			'wp-smtp/wp-smtp.php'         => 'WP SMTP',
			'wp-multilang/wp-multilang.php' => 'WP Multilang',
			'wp-cache/wp-cache.php'       => 'WP Cache',
		);

		foreach ( $incompatible as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s may have limited multisite compatibility', 'wpshadow' ),
					$name
				);
			}
		}

		// Check for large network size.
		$sites = get_sites( array( 'number' => 1, 'count' => true ) );
		if ( $sites > 50 ) {
			$issues[] = sprintf(
				/* translators: %d: number of sites */
				__( 'Large multisite network detected (%d sites) - verify plugin compatibility', 'wpshadow' ),
				$sites
			);
		}

		$details['site_count'] = $sites;

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Multisite compatibility issues detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/multisite-compatibility',
				'details'      => array(
					'issues'           => $issues,
					'network_plugins'  => array_keys( $network_plugins ),
					'site_count'       => $sites,
				),
			);
		}

		return null;
	}
}

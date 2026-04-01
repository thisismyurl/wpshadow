<?php
/**
 * WordPress Version Plugin Compatibility Diagnostic
 *
 * Checks if installed plugins are compatible with current WordPress version.
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
 * WordPress Version Plugin Compatibility Diagnostic
 *
 * Detects plugins incompatible with the installed WordPress version.
 *
 * @since 0.6093.1200
 */
class Diagnostic_WordPress_Version_Plugin_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-version-plugin-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Version Plugin Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if installed plugins are compatible with current WordPress version';

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
		$wp_version = get_bloginfo( 'version' );
		$issues     = array();
		$warnings   = array();

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		$incompatible_active   = array();
		$incompatible_inactive = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$is_active = in_array( $plugin_file, $active_plugins, true );

			// Check Requires WP header
			$requires_wp = isset( $plugin_data['RequiresWP'] ) ? $plugin_data['RequiresWP'] : '';

			if ( ! empty( $requires_wp ) && version_compare( $wp_version, $requires_wp, '<' ) ) {
				$plugin_info = array(
					'name'       => $plugin_data['Name'],
					'requires_wp' => $requires_wp,
					'current_wp' => $wp_version,
				);

				if ( $is_active ) {
					$incompatible_active[] = $plugin_info;
				} else {
					$incompatible_inactive[] = $plugin_info;
				}
			}

			// Check Tested up to version
			$tested_up_to = isset( $plugin_data['TestedUpTo'] ) ? $plugin_data['TestedUpTo'] : '';
			if ( ! empty( $tested_up_to ) && version_compare( $wp_version, $tested_up_to, '>' ) ) {
				if ( $is_active ) {
					$warnings[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s not tested with WordPress %s', 'wpshadow' ),
						$plugin_data['Name'],
						$wp_version
					);
				}
			}
		}

		if ( ! empty( $incompatible_active ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins, %s: WP version */
				__( '%d active plugins require WordPress %s or later', 'wpshadow' ),
				count( $incompatible_active ),
				isset( $incompatible_active[0]['requires_wp'] ) ? $incompatible_active[0]['requires_wp'] : ''
			);
		}

		if ( ! empty( $incompatible_inactive ) ) {
			$warnings[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d inactive plugins incompatible with WordPress %s', 'wpshadow' ),
				count( $incompatible_inactive ),
				$wp_version
			);
		}

		// Report findings
		if ( ! empty( $incompatible_active ) || ! empty( $issues ) ) {
			$severity     = 'medium';
			$threat_level = 55;

			if ( ! empty( $incompatible_active ) ) {
				$severity     = 'high';
				$threat_level = 85;
			}

			$description = __( 'Plugin compatibility issues detected with current WordPress version', 'wpshadow' );

			$details = array(
				'wp_version' => $wp_version,
			);

			if ( ! empty( $incompatible_active ) ) {
				$details['incompatible_active_plugins'] = $incompatible_active;
			}
			if ( ! empty( $incompatible_inactive ) ) {
				$details['incompatible_inactive_plugins'] = array_slice( $incompatible_inactive, 0, 5 );
			}
			if ( ! empty( $issues ) ) {
				$details['issues'] = $issues;
			}
			if ( ! empty( $warnings ) ) {
				$details['warnings'] = array_slice( $warnings, 0, 10 );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-version-plugin-compatibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => $details,
			);
		}

		return null;
	}
}

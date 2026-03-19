<?php
/**
 * Plugin License Status Diagnostic
 *
 * Checks premium plugin license status and validity.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin License Status Diagnostic
 *
 * Validates premium plugin licenses and support status.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_License_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-license-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin License Status (Premium Plugins)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks premium plugin license status and validity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		// Known premium plugins and their option patterns
		$premium_plugins = array(
			'wordfence/wordfence.php'                           => array(
				'license_option' => 'wordfence_activationKey',
				'name'           => 'Wordfence Security',
			),
			'wpforms/wpforms.php'                               => array(
				'license_option' => 'wpforms_license',
				'name'           => 'WPForms Pro',
			),
			'elementor-pro/elementor-pro.php'                   => array(
				'license_option' => 'elementor_pro_license',
				'name'           => 'Elementor Pro',
			),
			'thrive-leads/thrive-leads.php'                     => array(
				'license_option' => 'tve_leads_license_status',
				'name'           => 'Thrive Leads',
			),
			'all-in-one-wp-security-and-firewall/wp-security.php' => array(
				'license_option' => 'aiowpsec_premium_version',
				'name'           => 'All In One WP Security',
			),
			'wpvivid-backuprestore/wpvivid.php'                 => array(
				'license_option' => 'wpvivid_license_status',
				'name'           => 'WPvivid Backup',
			),
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$expired_licenses = array();
		$invalid_licenses = array();
		$active_licenses = array();

		foreach ( $premium_plugins as $plugin_file => $plugin_info ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				$license_key = get_option( $plugin_info['license_option'] );

				if ( empty( $license_key ) ) {
					$invalid_licenses[] = $plugin_info['name'];
				} else {
					// Check if license is valid
					$license_status = apply_filters( 'wpshadow_check_plugin_license', 'unknown', $plugin_file );

					if ( 'expired' === $license_status ) {
						$expired_licenses[] = $plugin_info['name'];
					} elseif ( 'valid' === $license_status ) {
						$active_licenses[] = $plugin_info['name'];
					}
				}
			}
		}

		// Check for pirated plugins
		$pirated_indicators = array();

		foreach ( $active_plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );

			if ( file_exists( $plugin_dir ) ) {
				// Check for license files that have been removed
				if ( ! file_exists( $plugin_dir . '/license.txt' ) &&
					! file_exists( $plugin_dir . '/license.php' ) &&
					! file_exists( $plugin_dir . '/LICENSE' ) ) {

					// Check if this is a known premium plugin
					$is_premium = false;
					foreach ( $premium_plugins as $premium_file => $info ) {
						if ( $plugin === $premium_file ) {
							$is_premium = true;
							break;
						}
					}

					if ( $is_premium && in_array( $plugin, $active_plugins, true ) ) {
						// Only flag if license is not found
						$license_key = get_option( $plugin_info['license_option'] ?? '' );
						if ( empty( $license_key ) ) {
							$pirated_indicators[] = $plugin;
						}
					}
				}
			}
		}

		// Check WooCommerce license status
		if ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {
			$woo_key = get_option( 'woocommerce_license_key' );
			if ( empty( $woo_key ) ) {
				$invalid_licenses[] = 'WooCommerce Pro';
			}
		}

		// Report findings
		if ( ! empty( $expired_licenses ) || ! empty( $invalid_licenses ) || ! empty( $pirated_indicators ) ) {
			$all_issues = array();

			if ( ! empty( $invalid_licenses ) ) {
				$all_issues[] = sprintf(
					/* translators: %s: plugin list */
					__( 'Plugins with missing or invalid licenses: %s', 'wpshadow' ),
					implode( ', ', $invalid_licenses )
				);
			}

			if ( ! empty( $expired_licenses ) ) {
				$all_issues[] = sprintf(
					/* translators: %s: plugin list */
					__( 'Plugins with expired licenses: %s', 'wpshadow' ),
					implode( ', ', $expired_licenses )
				);
			}

			if ( ! empty( $pirated_indicators ) ) {
				$all_issues[] = sprintf(
					/* translators: %s: plugin list */
					__( 'Potential pirated plugins detected: %s', 'wpshadow' ),
					implode( ', ', $pirated_indicators )
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Premium plugin license issues detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-license-status',
				'details'      => array(
					'issues'           => $all_issues,
					'expired'          => $expired_licenses,
					'invalid'          => $invalid_licenses,
					'active_licenses'  => $active_licenses,
					'pirated_suspects'  => $pirated_indicators,
				),
			);
		}

		return null;
	}
}

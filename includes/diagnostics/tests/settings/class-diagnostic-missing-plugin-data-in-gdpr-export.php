<?php
/**
 * Missing Plugin Data in GDPR Export Diagnostic
 *
 * Detects when third-party plugins don't contribute data to personal data exports.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_Plugin_Data_In_GDPR_Export Class
 *
 * Checks if installed plugins properly register GDPR data exporters.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Missing_Plugin_Data_In_GDPR_Export extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-plugin-data-in-gdpr-export';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin GDPR Export Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins properly integrate with GDPR personal data export';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get all active plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins  = array_merge( $active_plugins, array_keys( $network_plugins ) );
		}

		if ( empty( $active_plugins ) ) {
			return null;
		}

		// Get registered data exporters.
		$exporters = apply_filters( 'wp_privacy_personal_data_exporters', array() );

		// Plugins known to handle user data.
		$data_heavy_plugins = array(
			'woocommerce/woocommerce.php'                       => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'buddypress/bp-loader.php'                          => 'BuddyPress',
			'bbpress/bbpress.php'                               => 'bbPress',
			'memberpress/memberpress.php'                       => 'MemberPress',
			'paid-memberships-pro/paid-memberships-pro.php'     => 'Paid Memberships Pro',
			'gravityforms/gravityforms.php'                     => 'Gravity Forms',
			'contact-form-7/wp-contact-form-7.php'              => 'Contact Form 7',
			'ninja-forms/ninja-forms.php'                       => 'Ninja Forms',
			'wpforms-lite/wpforms.php'                          => 'WPForms',
			'wpforms/wpforms.php'                               => 'WPForms',
			'learndash/learndash.php'                           => 'LearnDash',
			'lifterlms/lifterlms.php'                           => 'LifterLMS',
			'sensei-lms/sensei-lms.php'                         => 'Sensei LMS',
			'mailchimp-for-wp/mailchimp-for-wp.php'             => 'Mailchimp for WordPress',
			'jetpack/jetpack.php'                               => 'Jetpack',
		);

		$issues           = array();
		$active_data_plugins = array();

		// Check which data-heavy plugins are active.
		foreach ( $data_heavy_plugins as $plugin_file => $plugin_name ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				$active_data_plugins[ $plugin_file ] = $plugin_name;
			}
		}

		if ( empty( $active_data_plugins ) ) {
			// No known data-heavy plugins active.
			return null;
		}

		// Check if these plugins have registered exporters.
		foreach ( $active_data_plugins as $plugin_file => $plugin_name ) {
			$has_exporter = false;

			// Look for exporter with plugin name.
			foreach ( $exporters as $exporter_id => $exporter ) {
				if ( isset( $exporter['exporter_friendly_name'] ) ) {
					$exporter_name = strtolower( $exporter['exporter_friendly_name'] );
					$plugin_slug   = strtolower( str_replace( ' ', '', $plugin_name ) );

					if ( false !== strpos( $exporter_name, strtolower( $plugin_name ) ) ||
					     false !== strpos( str_replace( ' ', '-', $exporter_name ), str_replace( ' ', '-', strtolower( $plugin_name ) ) ) ) {
						$has_exporter = true;
						break;
					}
				}
			}

			if ( ! $has_exporter ) {
				$issues[] = $plugin_name;
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of plugin names */
				_n(
					'Plugin may not export user data: %s',
					'Plugins may not export user data: %s',
					count( $issues ),
					'wpshadow'
				),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 90,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/plugin-gdpr-export-integration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'plugins_without_exporters' => $issues,
				'total_exporters'           => count( $exporters ),
				'active_data_plugins'       => count( $active_data_plugins ),
			),
		);
	}
}

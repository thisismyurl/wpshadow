<?php
/**
 * Elementor Pro License and Update Status Diagnostic
 *
 * Verify Elementor Pro license active and receiving updates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Updates
 * @since      1.6030.1240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro License Diagnostic Class
 *
 * @since 1.6030.1240
 */
class Diagnostic_ElementorProLicense extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'elementor-pro-license';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Elementor Pro License and Update Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verify Elementor Pro license active and receiving updates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'updates';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Elementor Pro is active
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Check Elementor Pro license activated
		$license_key = get_option( 'elementor_pro_license_key', '' );
		$license_data = get_option( '_elementor_pro_license_data', array() );

		if ( empty( $license_key ) ) {
			$issues[] = 'Elementor Pro license not activated';
		}

		// Check 2: Verify license not expired
		if ( ! empty( $license_data ) && is_array( $license_data ) ) {
			if ( isset( $license_data['license'] ) && 'invalid' === $license_data['license'] ) {
				$issues[] = 'license is invalid';
			}

			if ( isset( $license_data['license'] ) && 'expired' === $license_data['license'] ) {
				$issues[] = 'license has expired';
			}
		}

		// Check 3: Test for available updates
		$update_data = get_site_transient( 'update_plugins' );
		if ( isset( $update_data->response['elementor-pro/elementor-pro.php'] ) ) {
			$current_version = defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : '0';
			$new_version = $update_data->response['elementor-pro/elementor-pro.php']->new_version;

			if ( version_compare( $current_version, $new_version, '<' ) ) {
				$issues[] = sprintf( 'update available (current: %s, new: %s)', $current_version, $new_version );
			}
		}

		// Check 4: Check license domain match
		$site_url = get_site_url();
		if ( ! empty( $license_data ) && isset( $license_data['site_url'] ) ) {
			if ( $license_data['site_url'] !== $site_url ) {
				$issues[] = 'license registered to different domain';
			}
		}

		// Check 5: Verify no nulled/pirated version
		$plugin_file = WP_PLUGIN_DIR . '/elementor-pro/elementor-pro.php';
		if ( file_exists( $plugin_file ) ) {
			$plugin_content = file_get_contents( $plugin_file );

			// Check for common nulled plugin indicators
			$nulled_indicators = array( 'nulled', 'cracked', 'leaked', 'pirated', 'warez' );
			foreach ( $nulled_indicators as $indicator ) {
				if ( stripos( $plugin_content, $indicator ) !== false ) {
					$issues[] = 'possible nulled version detected (security risk)';
					break;
				}
			}
		}

		// Check 6: Test for API connectivity to Elementor servers
		$api_errors = get_transient( 'elementor_pro_api_error' );
		if ( ! empty( $api_errors ) ) {
			$issues[] = 'API connectivity issues with Elementor servers';
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 65 + ( count( $issues ) * 5 ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Elementor Pro license issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-license',
			);
		}

		return null;
	}
}

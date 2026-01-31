<?php
/**
 * Multisite Domain Mapping Configuration Diagnostic
 *
 * Multisite Domain Mapping Configuration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.941.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Domain Mapping Configuration Diagnostic Class
 *
 * @since 1.941.0000
 */
class Diagnostic_MultisiteDomainMappingConfiguration extends Diagnostic_Base {

	protected static $slug = 'multisite-domain-mapping-configuration';
	protected static $title = 'Multisite Domain Mapping Configuration';
	protected static $description = 'Multisite Domain Mapping Configuration misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify SUNRISE is enabled
		if ( ! defined( 'SUNRISE' ) || ! SUNRISE ) {
			$issues[] = 'SUNRISE constant not enabled for domain mapping';
		}

		// Check 2: Check for sunrise.php file
		if ( ! file_exists( WP_CONTENT_DIR . '/sunrise.php' ) ) {
			$issues[] = 'sunrise.php file missing';
		}

		// Check 3: Verify cookie domain configuration
		$cookie_domain = get_site_option( 'dm_cookie_domain', '' );
		if ( empty( $cookie_domain ) ) {
			$issues[] = 'Cookie domain not configured';
		}

		// Check 4: Check for admin redirect settings
		$redirect_admin = get_site_option( 'dm_redirect_admin', '' );
		if ( empty( $redirect_admin ) ) {
			$issues[] = 'Admin redirect setting not configured';
		}

		// Check 5: Verify mapped domain primary setting
		$primary_mapping = get_site_option( 'dm_primary_domain', '' );
		if ( empty( $primary_mapping ) ) {
			$issues[] = 'Primary mapped domain setting missing';
		}

		// Check 6: Check for domain mapping plugin activation
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! is_plugin_active( 'wordpress-mu-domain-mapping/domain_mapping.php' ) ) {
			$issues[] = 'Domain mapping plugin not active';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d multisite domain mapping issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-domain-mapping-configuration',
			);
		}

		return null;
	}
}

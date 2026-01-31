<?php
/**
 * Network Subsite Manager Cloning Diagnostic
 *
 * Network Subsite Manager Cloning misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.961.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Network Subsite Manager Cloning Diagnostic Class
 *
 * @since 1.961.0000
 */
class Diagnostic_NetworkSubsiteManagerCloning extends Diagnostic_Base {

	protected static $slug = 'network-subsite-manager-cloning';
	protected static $title = 'Network Subsite Manager Cloning';
	protected static $description = 'Network Subsite Manager Cloning misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'is_multisite' ) || ! is_multisite() ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify clone template selection
		$clone_template = get_site_option( 'nsm_clone_template', '' );
		if ( empty( $clone_template ) ) {
			$issues[] = __( 'Clone template site not configured', 'wpshadow' );
		}

		// Check 2: Check content duplication
		$duplicate_content = get_option( 'nsm_clone_duplicate_content', false );
		if ( ! $duplicate_content ) {
			$issues[] = __( 'Content duplication setting not configured', 'wpshadow' );
		}

		// Check 3: Verify user role mapping
		$role_mapping = get_option( 'nsm_clone_role_mapping', array() );
		if ( empty( $role_mapping ) ) {
			$issues[] = __( 'User role mapping not configured', 'wpshadow' );
		}

		// Check 4: Check plugin cloning
		$plugin_cloning = get_option( 'nsm_clone_plugins', false );
		if ( ! $plugin_cloning ) {
			$issues[] = __( 'Plugin cloning not enabled', 'wpshadow' );
		}

		// Check 5: Verify settings transfer
		$settings_transfer = get_option( 'nsm_clone_settings_transfer', false );
		if ( ! $settings_transfer ) {
			$issues[] = __( 'Settings transfer not enabled', 'wpshadow' );
		}

		// Check 6: Check media cloning
		$media_cloning = get_option( 'nsm_clone_media', false );
		if ( ! $media_cloning ) {
			$issues[] = __( 'Media library cloning not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Network Subsite Manager cloning issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/network-subsite-manager-cloning',
			);
		}

		return null;
	}
}

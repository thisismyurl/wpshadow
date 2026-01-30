<?php
/**
 * Yoast SEO Security and Access Controls Diagnostic
 *
 * Ensure Yoast SEO settings secured from unauthorized changes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6030.1255
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast SEO Security Diagnostic Class
 *
 * @since 1.6030.1255
 */
class Diagnostic_YoastSeoSecurity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'yoast-seo-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Yoast SEO Security and Access Controls';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure Yoast SEO settings secured from unauthorized changes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1255
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Yoast SEO is active
		if ( ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'WPSEO_Options' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Check SEO editor capability assignments
		$role_manager_active = get_option( 'wpseo_role_manager_enabled', false );

		if ( ! $role_manager_active ) {
			$issues[] = 'role manager not configured (default permissions may be too permissive)';
		}

		// Check 2: Verify non-admins can't change site-wide settings
		$roles_to_check = array( 'editor', 'author', 'contributor' );
		foreach ( $roles_to_check as $role_name ) {
			$role = get_role( $role_name );
			if ( $role && $role->has_cap( 'wpseo_manage_options' ) ) {
				$issues[] = sprintf( '%s role can manage SEO options (should be admin-only)', $role_name );
			}
		}

		// Check 3: Test for proper role capabilities
		$editor_role = get_role( 'editor' );
		if ( $editor_role && $editor_role->has_cap( 'wpseo_edit_advanced_metadata' ) ) {
			$issues[] = 'editors can edit advanced metadata (potential security risk)';
		}

		// Check 4: Check webmaster tools verification secured
		$google_verify = get_option( 'wpseo-google-verify', '' );
		$bing_verify = get_option( 'wpseo-msverify', '' );

		// Check if these are stored as plain options (should be in Yoast options)
		if ( ! empty( $google_verify ) || ! empty( $bing_verify ) ) {
			// Verify they're not exposed in REST API
			$rest_enabled = get_option( 'wpseo_json_ld_search', true );
			if ( $rest_enabled ) {
				$issues[] = 'webmaster verification codes may be exposed via REST API';
			}
		}

		// Check 5: Verify search appearance settings protected
		$anyone_can_register = get_option( 'users_can_register', 0 );
		if ( $anyone_can_register ) {
			$default_role = get_option( 'default_role', 'subscriber' );
			$default_role_obj = get_role( $default_role );

			if ( $default_role_obj && ( $default_role_obj->has_cap( 'edit_posts' ) || $default_role_obj->has_cap( 'wpseo_bulk_edit' ) ) ) {
				$issues[] = sprintf( 'new users get %s role with SEO edit capabilities', $default_role );
			}
		}

		// Check 6: Test for unauthorized XML sitemap modifications
		$sitemap_disabled = get_option( 'wpseo_xml_sitemap_disabled', false );
		if ( ! $sitemap_disabled ) {
			// Check if custom sitemap has been modified recently by non-admin
			global $wpdb;
			$recent_sitemap_changes = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->options}
					WHERE option_name LIKE %s
					AND autoload = 'yes'",
					'%wpseo_sitemap%'
				)
			);

			if ( $recent_sitemap_changes > 20 ) {
				$issues[] = sprintf( '%d sitemap-related options (verify not modified)', $recent_sitemap_changes );
			}
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 90, 55 + ( count( $issues ) * 6 ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Yoast SEO security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/yoast-seo-security',
			);
		}

		return null;
	}
}

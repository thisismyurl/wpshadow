<?php
/**
 * Personal Data Erasure Functionality Diagnostic
 *
 * Verifies that WordPress's personal data erasure feature (right to be forgotten)
 * is properly configured and functional, as required by GDPR Article 17.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personal Data Erasure Functionality Diagnostic Class
 *
 * Ensures the GDPR data erasure functionality is operational and properly configured.
 *
 * @since 1.26032.1600
 */
class Diagnostic_Personal_Data_Erasure_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'personal-data-erasure-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Personal Data Erasure Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies GDPR data erasure feature is functional';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Erasure feature is accessible
	 * - Email notifications are configured
	 * - Erasure includes custom data if registered
	 * - User deletion doesn't break site
	 *
	 * @since  1.26032.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if admin email is set (required for erasure requests).
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) || ! is_email( $admin_email ) ) {
			$issues[] = __( 'Admin email is not properly configured; data erasure requests cannot be processed', 'wpshadow' );
		}

		// Check if wp_privacy_personal_data_erasers filter has erasers registered.
		global $wp_filter;
		$has_erasers = isset( $wp_filter['wp_privacy_personal_data_erasers'] ) &&
					! empty( $wp_filter['wp_privacy_personal_data_erasers']->callbacks );

		if ( ! $has_erasers ) {
			$issues[] = __( 'No personal data erasers are registered; data erasure may be incomplete', 'wpshadow' );
		}

		// Check for required capabilities.
		$admin_role = get_role( 'administrator' );
		if ( $admin_role && ! $admin_role->has_cap( 'erase_others_personal_data' ) ) {
			$issues[] = __( 'Administrator role lacks erase_others_personal_data capability', 'wpshadow' );
		}

		// Check user deletion setting.
		$delete_with_user = get_option( 'wpmu_delete_user_action', 'reassign' );
		if ( 'delete' === $delete_with_user ) {
			$issues[] = __( 'User deletion is set to delete all content; this may not be appropriate for all sites', 'wpshadow' );
		}

		// Check if any plugins might be preventing proper erasure.
		$privacy_sensitive_plugins = array(
			'woocommerce/woocommerce.php'          => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'memberpress/memberpress.php'          => 'MemberPress',
			'bbpress/bbpress.php'                  => 'bbPress',
			'buddypress/bp-loader.php'             => 'BuddyPress',
		);

		$active_privacy_plugins = array();
		foreach ( $privacy_sensitive_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_privacy_plugins[] = $name;
			}
		}

		if ( ! empty( $active_privacy_plugins ) && ! $has_erasers ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of plugin names */
				__( 'Privacy-sensitive plugins detected (%s) but no custom erasers registered; data may not be fully deleted', 'wpshadow' ),
				implode( ', ', $active_privacy_plugins )
			);
		}

		// Check for comment moderation (anonymization vs deletion).
		$comment_max_links = (int) get_option( 'comment_max_links', 2 );
		$require_name_email = (bool) get_option( 'require_name_email', true );

		if ( ! $require_name_email ) {
			$issues[] = __( 'Comment forms do not require name/email; anonymization of comments may not be possible', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/personal-data-erasure-functionality',
			);
		}

		return null;
	}
}

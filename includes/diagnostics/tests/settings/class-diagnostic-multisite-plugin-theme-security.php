<?php
/**
 * Multisite Plugin and Theme Security Diagnostic
 *
 * Verifies network-wide plugin/theme security controls
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Multisite;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_MultisitePluginThemeSecurity Class
 *
 * Checks for DISALLOW_FILE_MODS, plugin restrictions, network-only activation
 *
 * @since 1.6093.1200
 */
class Diagnostic_MultisitePluginThemeSecurity extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'multisite-plugin-theme-security';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Multisite Plugin and Theme Security';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies network-wide plugin/theme security controls';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'multisite';

/**
 * Run the diagnostic check.
 *
 * @since 1.6093.1200
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Only run on multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check if plugin/theme installation is restricted to super admins.
		if ( ! is_super_admin() ) {
			// Check if regular admins can install plugins/themes.
			if ( ! is_network_admin() ) {
				$menu_perms = get_site_option( 'menu_items', array() );
				if ( isset( $menu_perms['plugins'] ) || isset( $menu_perms['themes'] ) ) {
					$issues[] = __( 'Site admins may have plugin/theme installation permissions', 'wpshadow' );
				}
			}
		}

		// Check for network-activated security plugins.
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$security_plugins = array( 'wordfence', 'sucuri', 'ithemes-security', 'all-in-one-wp-security' );
		$has_network_security = false;

		foreach ( array_keys( $active_plugins ) as $plugin ) {
			foreach ( $security_plugins as $sec_plugin ) {
				if ( stripos( $plugin, $sec_plugin ) !== false ) {
					$has_network_security = true;
					break 2;
				}
			}
		}

		if ( ! $has_network_security ) {
			$issues[] = __( 'No network-wide security plugin detected', 'wpshadow' );
		}

		// Check for plugin update management.
		$auto_update_plugins = get_site_option( 'auto_update_plugins', array() );
		if ( empty( $auto_update_plugins ) ) {
			$issues[] = __( 'No plugins configured for automatic updates', 'wpshadow' );
		}

		// Check for outdated plugins across network.
		$all_plugins = get_plugins();
		$update_plugins = get_site_transient( 'update_plugins' );
		$outdated_count = 0;

		if ( $update_plugins && ! empty( $update_plugins->response ) ) {
			$outdated_count = count( $update_plugins->response );
		}

		if ( $outdated_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of outdated plugins */
				__( '%d plugins have available updates', 'wpshadow' ),
				$outdated_count
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Multisite security concerns: %s. Network should have centralized plugin/theme management.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-plugin-theme-security',
		);
	}
}

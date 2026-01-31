<?php
/**
 * Multisite Network Plugin and Theme Security Diagnostic
 *
 * Checks if multisite networks properly restrict plugin/theme installation,
 * implement update controls, and prevent unauthorized code execution.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Multisite
 * @since      1.6031.1456
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Multisite;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Plugin Theme Security Diagnostic Class
 *
 * Verifies multisite networks implement proper plugin/theme security controls.
 *
 * @since 1.6031.1456
 */
class Diagnostic_Multisite_Plugin_Theme_Security extends Diagnostic_Base {

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
	protected static $title = 'Multisite Network Plugin and Theme Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies multisite networks restrict plugin/theme installation and prevent unauthorized code';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1456
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_multisite() ) {
			return null; // Not multisite.
		}

		$issues = array();

		// Check if sub-site admins can install plugins (security risk).
		if ( ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS ) {
			$issues[] = __( 'File modifications not disabled (sub-sites can install plugins/themes)', 'wpshadow' );
		}

		// Check if only network admin can manage plugins.
		$menu_perms = get_site_option( 'menu_items' );
		if ( isset( $menu_perms['plugins'] ) && $menu_perms['plugins'] ) {
			$issues[] = __( 'Plugin menu enabled for sub-site admins (allows plugin activation)', 'wpshadow' );
		}

		// Check for plugin management plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$has_plugin_control = false;
		$control_plugins = array(
			'multisite-plugin-manager',
			'network-plugin-auditor',
			'pro-sites',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $control_plugins as $ctrl_plugin ) {
				if ( stripos( $plugin, $ctrl_plugin ) !== false ) {
					$has_plugin_control = true;
					break 2;
				}
			}
		}

		if ( ! $has_plugin_control ) {
			$issues[] = __( 'No plugin management/auditing plugin for multisite detected', 'wpshadow' );
		}

		// Check for file editor (should be disabled).
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
			$issues[] = __( 'File editor not disabled (allows theme/plugin editing)', 'wpshadow' );
		}

		// Check for automatic updates (should be controlled at network level).
		if ( ! defined( 'AUTOMATIC_UPDATER_DISABLED' ) ) {
			$issues[] = __( 'Automatic updates not explicitly controlled at network level', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Multisite plugin/theme security concerns: %s. Networks should restrict plugin installation and prevent unauthorized code execution at sub-site level.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-plugin-theme-security',
		);
	}
}

<?php
/**
 * Wp Cli Integration Diagnostic
 *
 * Wp Cli Integration issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1047.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Cli Integration Diagnostic Class
 *
 * @since 1.1047.0000
 */
class Diagnostic_WpCliIntegration extends Diagnostic_Base {

	protected static $slug = 'wp-cli-integration';
	protected static $title = 'Wp Cli Integration';
	protected static $description = 'Wp Cli Integration issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check if WP-CLI is available
		$has_cli = defined( 'WP_CLI' ) && WP_CLI;

		if ( ! $has_cli ) {
			return null;
		}

		$issues = array();

		// Check 1: Custom commands registered
		$custom_commands = get_option( 'wp_cli_custom_commands', array() );
		if ( empty( $custom_commands ) && function_exists( 'wp_cli_get_commands' ) ) {
			$issues[] = __( 'No custom commands registered (basic functionality only)', 'wpshadow' );
		}

		// Check 2: Command aliases
		$aliases = get_option( 'wp_cli_aliases', array() );
		if ( empty( $aliases ) ) {
			$issues[] = __( 'No command aliases (inefficient workflow)', 'wpshadow' );
		}

		// Check 3: Package dependencies
		$packages = get_option( 'wp_cli_packages', array() );
		if ( empty( $packages ) ) {
			$issues[] = __( 'No packages installed (limited features)', 'wpshadow' );
		}

		// Check 4: Config file
		$config_file = ABSPATH . 'wp-cli.yml';
		if ( ! file_exists( $config_file ) ) {
			$issues[] = __( 'No wp-cli.yml config (defaults used)', 'wpshadow' );
		}

		// Check 5: Remote commands
		$remote_enabled = get_option( 'wp_cli_remote_enabled', 'no' );
		if ( 'yes' === $remote_enabled && ! is_ssl() ) {
			$issues[] = __( 'Remote commands without SSL (security risk)', 'wpshadow' );
		}

		// Check 6: Command logging
		$logging = get_option( 'wp_cli_logging', 'no' );
		if ( 'no' === $logging ) {
			$issues[] = __( 'Commands not logged (no audit trail)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'WP-CLI has %d integration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-cli-integration',
		);
	}
}

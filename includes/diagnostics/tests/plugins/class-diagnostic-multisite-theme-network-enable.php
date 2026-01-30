<?php
/**
 * Multisite Theme Network Enable Diagnostic
 *
 * Multisite Theme Network Enable misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.945.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Theme Network Enable Diagnostic Class
 *
 * @since 1.945.0000
 */
class Diagnostic_MultisiteThemeNetworkEnable extends Diagnostic_Base {

	protected static $slug = 'multisite-theme-network-enable';
	protected static $title = 'Multisite Theme Network Enable';
	protected static $description = 'Multisite Theme Network Enable misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$issues = array();
		$allowed_themes = get_site_option( 'allowedthemes', array() );
		
		// Check 1: Allowed themes array
		if ( empty( $allowed_themes ) || ! is_array( $allowed_themes ) ) {
			$issues[] = 'No network-enabled themes configured';
		}
		
		// Check 2: At least one theme enabled
		if ( is_array( $allowed_themes ) && 0 === count( $allowed_themes ) ) {
			$issues[] = 'Network has zero enabled themes';
		}
		
		// Check 3: Default theme enabled
		$default_theme = get_option( 'stylesheet', '' );
		if ( ! empty( $default_theme ) && ( empty( $allowed_themes[ $default_theme ] ) ) ) {
			$issues[] = 'Default site theme not network-enabled';
		}
		
		// Check 4: Too many enabled themes
		if ( is_array( $allowed_themes ) && count( $allowed_themes ) > 25 ) {
			$issues[] = 'Too many network-enabled themes (over 25)';
		}
		
		// Check 5: Disallowed themes list
		$disallowed = get_site_option( 'disallowedthemes', array() );
		if ( empty( $disallowed ) ) {
			$issues[] = 'Disallowed theme list not configured';
		}
		
		// Check 6: Network theme updates
		$updates_disabled = defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS;
		if ( $updates_disabled ) {
			$issues[] = 'Theme updates disabled for network';
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
					'Found %d multisite theme configuration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-theme-network-enable',
			);
		}
		
		return null;
	}
}

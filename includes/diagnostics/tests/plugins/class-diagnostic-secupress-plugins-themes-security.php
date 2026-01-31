<?php
/**
 * Secupress Plugins Themes Security Diagnostic
 *
 * Secupress Plugins Themes Security misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.872.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Secupress Plugins Themes Security Diagnostic Class
 *
 * @since 1.872.0000
 */
class Diagnostic_SecupressPluginsThemesSecurity extends Diagnostic_Base {

	protected static $slug = 'secupress-plugins-themes-security';
	protected static $title = 'Secupress Plugins Themes Security';
	protected static $description = 'Secupress Plugins Themes Security misconfiguration';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		// Check 1: Plugin updates monitoring
		$plugin_updates = get_option( 'secupress_plugin_updates_enabled', 0 );
		if ( ! $plugin_updates ) {
			$issues[] = 'Plugin update monitoring not enabled';
		}

		// Check 2: Theme update monitoring
		$theme_updates = get_option( 'secupress_theme_updates_enabled', 0 );
		if ( ! $theme_updates ) {
			$issues[] = 'Theme update monitoring not enabled';
		}

		// Check 3: Vulnerable plugin detection
		$vuln_detect = get_option( 'secupress_vulnerable_plugins_detection_enabled', 0 );
		if ( ! $vuln_detect ) {
			$issues[] = 'Vulnerable plugin detection not enabled';
		}

		// Check 4: Outdated plugin alerts
		$outdated = get_option( 'secupress_outdated_plugin_alerts_enabled', 0 );
		if ( ! $outdated ) {
			$issues[] = 'Outdated plugin alerts not enabled';
		}

		// Check 5: Unused plugin detection
		$unused = get_option( 'secupress_unused_plugin_detection_enabled', 0 );
		if ( ! $unused ) {
			$issues[] = 'Unused plugin detection not enabled';
		}

		// Check 6: Security patches notification
		$patches = get_option( 'secupress_security_patches_notification_enabled', 0 );
		if ( ! $patches ) {
			$issues[] = 'Security patches notification not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d plugin/theme security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/secupress-plugins-themes-security',
			);
		}

		return null;
	}
}

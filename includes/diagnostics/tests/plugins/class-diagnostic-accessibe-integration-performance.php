<?php
/**
 * Accessibe Integration Performance Diagnostic
 *
 * Accessibe Integration Performance not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1103.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibe Integration Performance Diagnostic Class
 *
 * @since 1.1103.0000
 */
class Diagnostic_AccessibeIntegrationPerformance extends Diagnostic_Base {

	protected static $slug = 'accessibe-integration-performance';
	protected static $title = 'Accessibe Integration Performance';
	protected static $description = 'Accessibe Integration Performance not compliant';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ACCESSIBE_VERSION' ) && ! class_exists( 'AccessiBe' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Script loading strategy.
		$script_loading = get_option( 'accessibe_script_loading', 'sync' );
		if ( 'sync' === $script_loading ) {
			$issues[] = 'widget script loading synchronously (blocks page rendering)';
		}

		// Check 2: Script position in page.
		global $wp_scripts;
		if ( isset( $wp_scripts->registered['accessibe-widget'] ) ) {
			$script_data = $wp_scripts->registered['accessibe-widget'];
			if ( empty( $script_data->extra['group'] ) || 1 !== $script_data->extra['group'] ) {
				$issues[] = 'widget script loaded in header (should load in footer)';
			}
		}

		// Check 3: Caching of widget configuration.
		$cache_config = get_option( 'accessibe_cache_config', '0' );
		if ( '0' === $cache_config ) {
			$issues[] = 'widget configuration not cached (API called on every page load)';
		}

		// Check 4: External API response time.
		$api_response_time = get_transient( 'accessibe_api_response_time' );
		if ( false !== $api_response_time && $api_response_time > 1000 ) {
			$response_seconds = round( $api_response_time / 1000, 2 );
			$issues[] = "slow API responses ({$response_seconds}s average, affects page speed)";
		}

		// Check 5: DOM manipulation frequency.
		$dom_scan_frequency = get_option( 'accessibe_dom_scan_frequency', 'high' );
		if ( 'high' === $dom_scan_frequency ) {
			$issues[] = 'DOM scanning frequency set to high (increases CPU usage)';
		}

		// Check 6: Conflicts with page builders.
		$active_plugins = get_option( 'active_plugins', array() );
		$page_builders = array(
			'elementor/elementor.php',
			'beaver-builder/fl-builder.php',
			'siteorigin-panels/siteorigin-panels.php',
		);
		$conflicts = array_intersect( $page_builders, $active_plugins );
		if ( ! empty( $conflicts ) && '0' === get_option( 'accessibe_pagebuilder_compat', '0' ) ) {
			$issues[] = 'page builder detected but compatibility mode disabled (may slow editor)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'accessiBe performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/accessibe-integration-performance',
			);
		}

		return null;
	}
}

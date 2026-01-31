<?php
/**
 * Google Tag Manager Trigger Performance Diagnostic
 *
 * Google Tag Manager Trigger Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1346.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Tag Manager Trigger Performance Diagnostic Class
 *
 * @since 1.1346.0000
 */
class Diagnostic_GoogleTagManagerTriggerPerformance extends Diagnostic_Base {

	protected static $slug = 'google-tag-manager-trigger-performance';
	protected static $title = 'Google Tag Manager Trigger Performance';
	protected static $description = 'Google Tag Manager Trigger Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'GTM4WP_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: GTM container ID configured
		$container_id = get_option( 'googletagmanager_container_id', '' );
		if ( empty( $container_id ) ) {
			$issues[] = 'GTM container ID not configured';
		}

		// Check 2: Trigger optimization enabled
		$trigger_opt = get_option( 'googletagmanager_trigger_optimization', false );
		if ( ! $trigger_opt ) {
			$issues[] = 'Trigger optimization disabled';
		}

		// Check 3: Data layer configured
		$data_layer = get_option( 'googletagmanager_datalayer_name', '' );
		if ( empty( $data_layer ) ) {
			$issues[] = 'Data layer name not configured';
		}

		// Check 4: Event tracking configured
		$event_tracking = get_option( 'googletagmanager_event_tracking', false );
		if ( ! $event_tracking ) {
			$issues[] = 'Event tracking not configured';
		}

		// Check 5: Tag firing rules optimized
		$firing_rules = get_option( 'googletagmanager_firing_rules_optimized', false );
		if ( ! $firing_rules ) {
			$issues[] = 'Tag firing rules not optimized';
		}

		// Check 6: Container version up to date
		$version_check = get_option( 'googletagmanager_version_check', false );
		if ( ! $version_check ) {
			$issues[] = 'Container version not checked';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Google Tag Manager trigger performance issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/google-tag-manager-trigger-performance',
			);
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}

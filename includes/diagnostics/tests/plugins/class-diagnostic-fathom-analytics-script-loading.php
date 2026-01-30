<?php
/**
 * Fathom Analytics Script Loading Diagnostic
 *
 * Fathom Analytics Script Loading misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1363.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fathom Analytics Script Loading Diagnostic Class
 *
 * @since 1.1363.0000
 */
class Diagnostic_FathomAnalyticsScriptLoading extends Diagnostic_Base {

	protected static $slug = 'fathom-analytics-script-loading';
	protected static $title = 'Fathom Analytics Script Loading';
	protected static $description = 'Fathom Analytics Script Loading misconfigured';
	protected static $family = 'performance';

	public static function check() {
		// Check for Fathom Analytics
		$fathom_code = get_option( 'fathom_analytics_code', '' );
		$fathom_site_id = get_option( 'fathom_site_id', '' );
		
		if ( empty( $fathom_code ) && empty( $fathom_site_id ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Script loading strategy
		$script_position = get_option( 'fathom_script_position', 'header' );
		if ( 'header' === $script_position ) {
			$issues[] = __( 'Fathom script loads in header (render-blocking)', 'wpshadow' );
		}
		
		// Check 2: Async loading
		$async_enabled = get_option( 'fathom_async_loading', false );
		if ( ! $async_enabled ) {
			$issues[] = __( 'Fathom script not loaded asynchronously (page speed impact)', 'wpshadow' );
		}
		
		// Check 3: DNS prefetch
		$dns_prefetch = get_option( 'fathom_dns_prefetch', false );
		if ( ! $dns_prefetch ) {
			$issues[] = __( 'DNS prefetch not configured for Fathom CDN', 'wpshadow' );
		}
		
		// Check 4: Local script hosting
		$local_hosting = get_option( 'fathom_local_hosting', false );
		if ( ! $local_hosting ) {
			$issues[] = __( 'Fathom script not hosted locally (external DNS lookup)', 'wpshadow' );
		}
		
		// Check 5: Consent mode integration
		$consent_mode = get_option( 'fathom_respect_do_not_track', true );
		if ( ! $consent_mode ) {
			$issues[] = __( 'Do Not Track not respected (privacy concern)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of loading issues */
				__( 'Fathom Analytics script loading has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/fathom-analytics-script-loading',
		);
	}
}

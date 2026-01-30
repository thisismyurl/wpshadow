<?php
/**
 * Hotjar Feedback Widget Loading Diagnostic
 *
 * Hotjar Feedback Widget Loading misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1372.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hotjar Feedback Widget Loading Diagnostic Class
 *
 * @since 1.1372.0000
 */
class Diagnostic_HotjarFeedbackWidgetLoading extends Diagnostic_Base {

	protected static $slug = 'hotjar-feedback-widget-loading';
	protected static $title = 'Hotjar Feedback Widget Loading';
	protected static $description = 'Hotjar Feedback Widget Loading misconfigured';
	protected static $family = 'performance';

	public static function check() {
		// Check for Hotjar tracking code
		$hotjar_id = get_option( 'hotjar_site_id', '' );
		if ( empty( $hotjar_id ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Async loading
		$async_enabled = get_option( 'hotjar_async_loading', 'yes' );
		if ( 'no' === $async_enabled ) {
			$issues[] = __( 'Synchronous loading (blocks page render)', 'wpshadow' );
		}
		
		// Check 2: Session recording on all pages
		$record_all = get_option( 'hotjar_record_all_pages', 'yes' );
		if ( 'yes' === $record_all ) {
			$issues[] = __( 'Recording all pages (privacy concerns, storage quota)', 'wpshadow' );
		}
		
		// Check 3: Feedback polling frequency
		$polling_rate = get_option( 'hotjar_polling_rate', 2000 );
		if ( $polling_rate < 5000 ) {
			$issues[] = sprintf( __( 'Polling every %dms (network overhead)', 'wpshadow' ), $polling_rate );
		}
		
		// Check 4: Mobile optimization
		$disable_mobile = get_option( 'hotjar_disable_mobile', 'no' );
		if ( 'no' === $disable_mobile ) {
			$mobile_traffic = get_option( 'hotjar_mobile_traffic_percent', 0 );
			if ( $mobile_traffic > 50 ) {
				$issues[] = sprintf( __( 'Recording %d%% mobile traffic (bandwidth cost)', 'wpshadow' ), $mobile_traffic );
			}
		}
		
		// Check 5: Trigger conditions
		$triggers = get_option( 'hotjar_feedback_triggers', array() );
		if ( empty( $triggers ) ) {
			$issues[] = __( 'No feedback triggers (indiscriminate collection)', 'wpshadow' );
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
				/* translators: %s: list of Hotjar loading issues */
				__( 'Hotjar feedback widget has %d loading issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/hotjar-feedback-widget-loading',
		);
	}
}

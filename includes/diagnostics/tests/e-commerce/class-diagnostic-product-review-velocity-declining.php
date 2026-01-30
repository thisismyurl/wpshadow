<?php
/**
 * Product Review Velocity Declining Diagnostic
 *
 * Measures review count trend to detect declining customer engagement.
 * Declining reviews may indicate customer satisfaction or request issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Ecommerce
 * @since      1.6028.2150
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Product_Review_Velocity_Declining Class
 *
 * Tracks review submissions over time to detect engagement decline.
 *
 * @since 1.6028.2150
 */
class Diagnostic_Product_Review_Velocity_Declining extends Diagnostic_Base {

	protected static $slug        = 'product-review-velocity-declining';
	protected static $title       = 'Product Review Velocity Declining';
	protected static $description = 'Detects declining review submission rates';
	protected static $family      = 'ecommerce';

	public static function check() {
		$cached = get_transient( 'wpshadow_diagnostic_review_velocity' );
		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			set_transient( 'wpshadow_diagnostic_review_velocity', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$velocity_data = self::analyze_review_velocity();
		if ( ! $velocity_data || $velocity_data['decline_percent'] < 10 ) {
			set_transient( 'wpshadow_diagnostic_review_velocity', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$decline  = $velocity_data['decline_percent'];
		$severity = $decline > 30 ? 'high' : 'medium';

		$finding = array(
			'id'             => self::$slug,
			'title'          => self::$title,
			'description'    => sprintf( __( 'Review submission rate declined by %s%% over the last 3 months', 'wpshadow' ), number_format( $decline, 1 ) ),
			'severity'       => $severity,
			'threat_level'   => min( 70, 40 + $decline ),
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/review-velocity',
			'meta'           => $velocity_data,
			'details'        => array(
				sprintf( __( 'Monthly decline: %s%%', 'wpshadow' ), number_format( $decline, 1 ) ),
				__( 'Declining reviews indicate potential satisfaction issues', 'wpshadow' ),
			),
			'recommendations' => array(
				__( 'Send follow-up emails requesting reviews', 'wpshadow' ),
				__( 'Offer incentives for verified reviews', 'wpshadow' ),
				__( 'Investigate customer satisfaction issues', 'wpshadow' ),
			),
		);

		set_transient( 'wpshadow_diagnostic_review_velocity', $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	private static function analyze_review_velocity() {
		global $wpdb;

		$three_months_ago = date( 'Y-m-d', strtotime( '-3 months' ) );
		$two_months_ago   = date( 'Y-m-d', strtotime( '-2 months' ) );
		$one_month_ago    = date( 'Y-m-d', strtotime( '-1 month' ) );

		$recent_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_type = 'review' 
				AND comment_date >= %s",
				$one_month_ago
			)
		);

		$previous_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_type = 'review' 
				AND comment_date >= %s 
				AND comment_date < %s",
				$two_months_ago,
				$one_month_ago
			)
		);

		if ( $previous_count === 0 ) {
			return false;
		}

		$decline_percent = ( ( $previous_count - $recent_count ) / $previous_count ) * 100;

		return array(
			'recent_count'    => $recent_count,
			'previous_count'  => $previous_count,
			'decline_percent' => $decline_percent,
		);
	}
}

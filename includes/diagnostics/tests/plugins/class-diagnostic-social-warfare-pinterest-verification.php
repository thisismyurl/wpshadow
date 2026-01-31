<?php
/**
 * Social Warfare Pinterest Diagnostic
 *
 * Social Warfare Pinterest not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.433.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Warfare Pinterest Diagnostic Class
 *
 * @since 1.433.0000
 */
class Diagnostic_SocialWarfarePinterestVerification extends Diagnostic_Base {

	protected static $slug = 'social-warfare-pinterest-verification';
	protected static $title = 'Social Warfare Pinterest';
	protected static $description = 'Social Warfare Pinterest not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'SWP_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Pinterest meta tag configured
		$pinterest_meta = get_option( 'swp_pinterest_verification', '' );
		if ( empty( $pinterest_meta ) ) {
			$issues[] = 'Pinterest verification meta tag not configured';
		}
		
		// Check 2: Pinterest board verification
		$board_verified = get_option( 'swp_pinterest_board_verified', false );
		if ( ! $board_verified ) {
			$issues[] = 'Pinterest board not verified';
		}
		
		// Check 3: Rich pins enabled
		$rich_pins = get_option( 'swp_pinterest_rich_pins', false );
		if ( ! $rich_pins ) {
			$issues[] = 'Rich pins not enabled';
		}
		
		// Check 4: Pin count display
		$pin_counts = get_option( 'swp_pinterest_pin_counts', false );
		if ( ! $pin_counts ) {
			$issues[] = 'Pin counts not displayed';
		}
		
		// Check 5: Pinterest image optimization
		$image_opt = get_option( 'swp_pinterest_image_optimization', false );
		if ( ! $image_opt ) {
			$issues[] = 'Pinterest image optimization disabled';
		}
		
		// Check 6: Verification status check
		$verification_status = get_option( 'swp_pinterest_verification_status', '' );
		if ( 'verified' !== $verification_status ) {
			$issues[] = 'Pinterest verification incomplete';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 50, 20 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Social Warfare Pinterest verification issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/social-warfare-pinterest-verification',
			);
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}

<?php
/**
 * Stripe 3d Secure Configuration Diagnostic
 *
 * Stripe 3d Secure Configuration vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1391.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe 3d Secure Configuration Diagnostic Class
 *
 * @since 1.1391.0000
 */
class Diagnostic_Stripe3dSecureConfiguration extends Diagnostic_Base {

	protected static $slug = 'stripe-3d-secure-configuration';
	protected static $title = 'Stripe 3d Secure Configuration';
	protected static $description = 'Stripe 3d Secure Configuration vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Stripe' ) ) {
			return null;
		}

		$issues = array();
		$settings = get_option( 'woocommerce_stripe_settings', array() );

		// Check 1: 3D Secure enabled
		$sca_enabled = isset( $settings['inline_cc_form'] ) && 'yes' === $settings['inline_cc_form'];
		if ( ! $sca_enabled ) {
			$issues[] = __( '3D Secure (SCA) not enabled (compliance risk)', 'wpshadow' );
		}

		// Check 2: Test mode in production
		$test_mode = isset( $settings['testmode'] ) && 'yes' === $settings['testmode'];
		if ( $test_mode && ! defined( 'WP_DEBUG' ) ) {
			$issues[] = __( 'Test mode in production (transactions failing)', 'wpshadow' );
		}

		// Check 3: Webhook configured
		$webhook_secret = isset( $settings['webhook_secret'] ) ? $settings['webhook_secret'] : '';
		if ( empty( $webhook_secret ) ) {
			$issues[] = __( 'No webhook secret (payment status issues)', 'wpshadow' );
		}

		// Check 4: Saved cards
		$saved_cards = isset( $settings['saved_cards'] ) && 'yes' === $settings['saved_cards'];
		if ( $saved_cards && ! $sca_enabled ) {
			$issues[] = __( 'Saved cards without SCA (PSD2 violation)', 'wpshadow' );
		}

		// Check 5: Statement descriptor
		$descriptor = isset( $settings['statement_descriptor'] ) ? $settings['statement_descriptor'] : '';
		if ( empty( $descriptor ) ) {
			$issues[] = __( 'No statement descriptor (customer confusion)', 'wpshadow' );
		}

		// Check 6: Capture method
		$capture = isset( $settings['capture'] ) && 'yes' === $settings['capture'];
		if ( ! $capture ) {
			$issues[] = __( 'Manual capture (delayed settlement)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 75;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 87;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 81;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Stripe has %d 3D Secure configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/stripe-3d-secure-configuration',
		);
	}
}

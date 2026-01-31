<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WoocommerceEmailNotifications extends Diagnostic_Base {
	protected static $slug = 'woocommerce-email-notifications';
	protected static $title = 'WooCommerce Email Notifications';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) { return null; }
		$mailer = WC()->mailer();
		$emails = $mailer->get_emails();
		$disabled = array();
		foreach ( $emails as $email ) {
			if ( ! $email->is_enabled() && in_array( $email->id, array( 'new_order', 'customer_processing_order' ), true ) ) {
				$disabled[] = $email->title;
			}
		}
		if ( ! empty( $disabled ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Critical emails disabled: %s', 'wpshadow' ), implode( ', ', $disabled ) ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/woocommerce-emails',
			);
		}

		// Plugin integration checks
		if ( ! function_exists( 'get_plugins' ) ) {
			$issues[] = __( 'Plugin listing not available', 'wpshadow' );
		}
		if ( ! function_exists( 'is_plugin_active' ) ) {
			$issues[] = __( 'Plugin status check unavailable', 'wpshadow' );
		}
		// Verify integration point
		if ( ! function_exists( 'do_action' ) ) {
			$issues[] = __( 'Action hooks unavailable', 'wpshadow' );
		}
		return null;
	}
}

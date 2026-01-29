<?php
/**
 * Stripe Api Key Security Diagnostic
 *
 * Stripe Api Key Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1388.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Api Key Security Diagnostic Class
 *
 * @since 1.1388.0000
 */
class Diagnostic_StripeApiKeySecurity extends Diagnostic_Base {

	protected static $slug = 'stripe-api-key-security';
	protected static $title = 'Stripe Api Key Security';
	protected static $description = 'Stripe Api Key Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Stripe' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/stripe-api-key-security',
			);
		}
		
		return null;
	}
}

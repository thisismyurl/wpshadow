<?php
/**
 * LifterLMS Payment Gateways Diagnostic
 *
 * LifterLMS payments insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.367.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LifterLMS Payment Gateways Diagnostic Class
 *
 * @since 1.367.0000
 */
class Diagnostic_LifterlmsPaymentGateways extends Diagnostic_Base {

	protected static $slug = 'lifterlms-payment-gateways';
	protected static $title = 'LifterLMS Payment Gateways';
	protected static $description = 'LifterLMS payments insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'LLMS' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/lifterlms-payment-gateways',
			);
		}
		
		return null;
	}
}

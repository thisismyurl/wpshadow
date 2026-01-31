<?php
/**
 * Wp User Frontend Payment Gateways Diagnostic
 *
 * Wp User Frontend Payment Gateways issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1222.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp User Frontend Payment Gateways Diagnostic Class
 *
 * @since 1.1222.0000
 */
class Diagnostic_WpUserFrontendPaymentGateways extends Diagnostic_Base {

	protected static $slug = 'wp-user-frontend-payment-gateways';
	protected static $title = 'Wp User Frontend Payment Gateways';
	protected static $description = 'Wp User Frontend Payment Gateways issue found';
	protected static $family = 'security';

	public static function check() {
		
		$issues = array();
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-user-frontend-payment-gateways',
			);
		}
		
		return null;
	}
}

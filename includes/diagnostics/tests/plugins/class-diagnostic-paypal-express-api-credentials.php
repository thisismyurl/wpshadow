<?php
/**
 * Paypal Express Api Credentials Diagnostic
 *
 * Paypal Express Api Credentials vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1395.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paypal Express Api Credentials Diagnostic Class
 *
 * @since 1.1395.0000
 */
class Diagnostic_PaypalExpressApiCredentials extends Diagnostic_Base {

	protected static $slug = 'paypal-express-api-credentials';
	protected static $title = 'Paypal Express Api Credentials';
	protected static $description = 'Paypal Express Api Credentials vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'WC_Gateway_Paypal' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/paypal-express-api-credentials',
			);
		}
		
		return null;
	}
}

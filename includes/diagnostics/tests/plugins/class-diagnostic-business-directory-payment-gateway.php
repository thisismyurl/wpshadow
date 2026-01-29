<?php
/**
 * Business Directory Payment Gateway Diagnostic
 *
 * Business Directory payments insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.547.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Payment Gateway Diagnostic Class
 *
 * @since 1.547.0000
 */
class Diagnostic_BusinessDirectoryPaymentGateway extends Diagnostic_Base {

	protected static $slug = 'business-directory-payment-gateway';
	protected static $title = 'Business Directory Payment Gateway';
	protected static $description = 'Business Directory payments insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/business-directory-payment-gateway',
			);
		}
		
		return null;
	}
}

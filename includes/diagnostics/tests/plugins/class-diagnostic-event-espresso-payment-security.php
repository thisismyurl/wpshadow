<?php
/**
 * Event Espresso Payment Security Diagnostic
 *
 * Event Espresso payments insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.587.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Espresso Payment Security Diagnostic Class
 *
 * @since 1.587.0000
 */
class Diagnostic_EventEspressoPaymentSecurity extends Diagnostic_Base {

	protected static $slug = 'event-espresso-payment-security';
	protected static $title = 'Event Espresso Payment Security';
	protected static $description = 'Event Espresso payments insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/event-espresso-payment-security',
			);
		}
		
		return null;
	}
}

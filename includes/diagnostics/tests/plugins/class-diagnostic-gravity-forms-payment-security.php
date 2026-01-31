<?php
/**
 * Gravity Forms Payment Security Diagnostic
 *
 * Gravity Forms payment processing insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.258.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Payment Security Diagnostic Class
 *
 * @since 1.258.0000
 */
class Diagnostic_GravityFormsPaymentSecurity extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-payment-security';
	protected static $title = 'Gravity Forms Payment Security';
	protected static $description = 'Gravity Forms payment processing insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
			return null;
		}
		
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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-payment-security',
			);
		}
		
		return null;
	}
}

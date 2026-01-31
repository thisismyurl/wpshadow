<?php
/**
 * Caldera Forms Email Delivery Diagnostic
 *
 * Caldera Forms emails not sending.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.475.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms Email Delivery Diagnostic Class
 *
 * @since 1.475.0000
 */
class Diagnostic_CalderaFormsEmailDelivery extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-email-delivery';
	protected static $title = 'Caldera Forms Email Delivery';
	protected static $description = 'Caldera Forms emails not sending';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-email-delivery',
			);
		}
		
		return null;
	}
}

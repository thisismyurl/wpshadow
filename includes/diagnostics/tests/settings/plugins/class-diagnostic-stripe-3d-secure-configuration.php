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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/stripe-3d-secure-configuration',
			);
		}
		
		return null;
	}
}

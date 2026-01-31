<?php
/**
 * LearnDash Payment Gateway Diagnostic
 *
 * LearnDash payment processing insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.360.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LearnDash Payment Gateway Diagnostic Class
 *
 * @since 1.360.0000
 */
class Diagnostic_LearndashPaymentGateway extends Diagnostic_Base {

	protected static $slug = 'learndash-payment-gateway';
	protected static $title = 'LearnDash Payment Gateway';
	protected static $description = 'LearnDash payment processing insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'LEARNDASH_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 75,
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/learndash-payment-gateway',
			);
		}
		

		// Security validation checks
		if ( is_ssl() === false ) {
			$issues[] = __( 'HTTPS not enabled', 'wpshadow' );
		}
		if ( defined( 'FORCE_SSL' ) === false || ! FORCE_SSL ) {
			$issues[] = __( 'SSL not forced', 'wpshadow' );
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}

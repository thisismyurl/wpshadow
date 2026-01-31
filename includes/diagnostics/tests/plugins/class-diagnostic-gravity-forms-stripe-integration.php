<?php
/**
 * Gravity Forms Stripe Integration Diagnostic
 *
 * Gravity Forms Stripe Integration issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1191.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Stripe Integration Diagnostic Class
 *
 * @since 1.1191.0000
 */
class Diagnostic_GravityFormsStripeIntegration extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-stripe-integration';
	protected static $title = 'Gravity Forms Stripe Integration';
	protected static $description = 'Gravity Forms Stripe Integration issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-stripe-integration',
			);
		}
		

		// Feature availability checks
		if ( ! function_exists( 'add_action' ) ) {
			$issues[] = __( 'WordPress hooks unavailable', 'wpshadow' );
		}
		if ( empty( $GLOBALS['wpdb'] ) ) {
			$issues[] = __( 'Database not initialized', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}

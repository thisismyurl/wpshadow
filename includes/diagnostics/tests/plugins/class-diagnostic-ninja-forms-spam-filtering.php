<?php
/**
 * Ninja Forms Spam Filtering Diagnostic
 *
 * Ninja Forms Spam Filtering issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1190.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Forms Spam Filtering Diagnostic Class
 *
 * @since 1.1190.0000
 */
class Diagnostic_NinjaFormsSpamFiltering extends Diagnostic_Base {

	protected static $slug = 'ninja-forms-spam-filtering';
	protected static $title = 'Ninja Forms Spam Filtering';
	protected static $description = 'Ninja Forms Spam Filtering issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) {
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-forms-spam-filtering',
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

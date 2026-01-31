<?php
/**
 * Weglot Woocommerce Integration Diagnostic
 *
 * Weglot Woocommerce Integration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1161.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weglot Woocommerce Integration Diagnostic Class
 *
 * @since 1.1161.0000
 */
class Diagnostic_WeglotWoocommerceIntegration extends Diagnostic_Base {

	protected static $slug = 'weglot-woocommerce-integration';
	protected static $title = 'Weglot Woocommerce Integration';
	protected static $description = 'Weglot Woocommerce Integration misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WEGLOT_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/weglot-woocommerce-integration',
			);
		}
		
		return null;
	}
}

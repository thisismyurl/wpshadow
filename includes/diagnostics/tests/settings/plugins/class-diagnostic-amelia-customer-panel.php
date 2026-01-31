<?php
/**
 * Amelia Customer Panel Diagnostic
 *
 * Amelia customer panel permissions wrong.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.467.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Customer Panel Diagnostic Class
 *
 * @since 1.467.0000
 */
class Diagnostic_AmeliaCustomerPanel extends Diagnostic_Base {

	protected static $slug = 'amelia-customer-panel';
	protected static $title = 'Amelia Customer Panel';
	protected static $description = 'Amelia customer panel permissions wrong';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/amelia-customer-panel',
			);
		}
		
		return null;
	}
}

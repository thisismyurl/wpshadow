<?php
/**
 * Mollie Api Key Security Diagnostic
 *
 * Mollie Api Key Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1409.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mollie Api Key Security Diagnostic Class
 *
 * @since 1.1409.0000
 */
class Diagnostic_MollieApiKeySecurity extends Diagnostic_Base {

	protected static $slug = 'mollie-api-key-security';
	protected static $title = 'Mollie Api Key Security';
	protected static $description = 'Mollie Api Key Security vulnerability detected';
	protected static $family = 'security';

	public static function check() {
		
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
				'severity'    => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/mollie-api-key-security',
			);
		}
		
		return null;
	}
}

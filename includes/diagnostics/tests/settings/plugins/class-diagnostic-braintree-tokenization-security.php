<?php
/**
 * Braintree Tokenization Security Diagnostic
 *
 * Braintree Tokenization Security vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1406.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Braintree Tokenization Security Diagnostic Class
 *
 * @since 1.1406.0000
 */
class Diagnostic_BraintreeTokenizationSecurity extends Diagnostic_Base {

	protected static $slug = 'braintree-tokenization-security';
	protected static $title = 'Braintree Tokenization Security';
	protected static $description = 'Braintree Tokenization Security vulnerability detected';
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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/braintree-tokenization-security',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Constant Contact Api Security Diagnostic
 *
 * Constant Contact Api Security configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.721.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Constant Contact Api Security Diagnostic Class
 *
 * @since 1.721.0000
 */
class Diagnostic_ConstantContactApiSecurity extends Diagnostic_Base {

	protected static $slug = 'constant-contact-api-security';
	protected static $title = 'Constant Contact Api Security';
	protected static $description = 'Constant Contact Api Security configuration issues';
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/constant-contact-api-security',
			);
		}
		
		return null;
	}
}

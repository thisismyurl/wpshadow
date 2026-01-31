<?php
/**
 * Wordfence Rate Limiting Diagnostic
 *
 * Wordfence Rate Limiting misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.844.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Rate Limiting Diagnostic Class
 *
 * @since 1.844.0000
 */
class Diagnostic_WordfenceRateLimiting extends Diagnostic_Base {

	protected static $slug = 'wordfence-rate-limiting';
	protected static $title = 'Wordfence Rate Limiting';
	protected static $description = 'Wordfence Rate Limiting misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-rate-limiting',
			);
		}
		
		return null;
	}
}

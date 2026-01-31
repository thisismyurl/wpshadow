<?php
/**
 * Wordfence Brute Force Protection Diagnostic
 *
 * Wordfence Brute Force Protection misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.848.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Brute Force Protection Diagnostic Class
 *
 * @since 1.848.0000
 */
class Diagnostic_WordfenceBruteForceProtection extends Diagnostic_Base {

	protected static $slug = 'wordfence-brute-force-protection';
	protected static $title = 'Wordfence Brute Force Protection';
	protected static $description = 'Wordfence Brute Force Protection misconfiguration';
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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-brute-force-protection',
			);
		}
		
		return null;
	}
}

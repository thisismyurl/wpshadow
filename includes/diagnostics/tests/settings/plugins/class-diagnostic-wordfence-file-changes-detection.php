<?php
/**
 * Wordfence File Changes Detection Diagnostic
 *
 * Wordfence File Changes Detection misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.845.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence File Changes Detection Diagnostic Class
 *
 * @since 1.845.0000
 */
class Diagnostic_WordfenceFileChangesDetection extends Diagnostic_Base {

	protected static $slug = 'wordfence-file-changes-detection';
	protected static $title = 'Wordfence File Changes Detection';
	protected static $description = 'Wordfence File Changes Detection misconfiguration';
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
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-file-changes-detection',
			);
		}
		
		return null;
	}
}

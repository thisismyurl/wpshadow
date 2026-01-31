<?php
/**
 * Crazy Egg Recordings Privacy Diagnostic
 *
 * Crazy Egg Recordings Privacy misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1376.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Crazy Egg Recordings Privacy Diagnostic Class
 *
 * @since 1.1376.0000
 */
class Diagnostic_CrazyEggRecordingsPrivacy extends Diagnostic_Base {

	protected static $slug = 'crazy-egg-recordings-privacy';
	protected static $title = 'Crazy Egg Recordings Privacy';
	protected static $description = 'Crazy Egg Recordings Privacy misconfigured';
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
				'kb_link'     => 'https://wpshadow.com/kb/crazy-egg-recordings-privacy',
			);
		}
		
		return null;
	}
}

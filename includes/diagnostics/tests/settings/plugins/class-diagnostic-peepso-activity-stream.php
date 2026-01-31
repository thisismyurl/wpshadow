<?php
/**
 * PeepSo Activity Stream Diagnostic
 *
 * PeepSo activity queries inefficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.520.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PeepSo Activity Stream Diagnostic Class
 *
 * @since 1.520.0000
 */
class Diagnostic_PeepsoActivityStream extends Diagnostic_Base {

	protected static $slug = 'peepso-activity-stream';
	protected static $title = 'PeepSo Activity Stream';
	protected static $description = 'PeepSo activity queries inefficient';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'PeepSo' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/peepso-activity-stream',
			);
		}
		
		return null;
	}
}

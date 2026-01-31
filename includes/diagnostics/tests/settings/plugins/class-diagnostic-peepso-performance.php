<?php
/**
 * PeepSo Performance Diagnostic
 *
 * PeepSo slowing site significantly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.518.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PeepSo Performance Diagnostic Class
 *
 * @since 1.518.0000
 */
class Diagnostic_PeepsoPerformance extends Diagnostic_Base {

	protected static $slug = 'peepso-performance';
	protected static $title = 'PeepSo Performance';
	protected static $description = 'PeepSo slowing site significantly';
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/peepso-performance',
			);
		}
		
		return null;
	}
}

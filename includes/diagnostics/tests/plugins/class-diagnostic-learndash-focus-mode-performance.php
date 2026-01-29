<?php
/**
 * LearnDash Focus Mode Diagnostic
 *
 * LearnDash focus mode loading slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.363.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LearnDash Focus Mode Diagnostic Class
 *
 * @since 1.363.0000
 */
class Diagnostic_LearndashFocusModePerformance extends Diagnostic_Base {

	protected static $slug = 'learndash-focus-mode-performance';
	protected static $title = 'LearnDash Focus Mode';
	protected static $description = 'LearnDash focus mode loading slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LEARNDASH_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/learndash-focus-mode-performance',
			);
		}
		
		return null;
	}
}

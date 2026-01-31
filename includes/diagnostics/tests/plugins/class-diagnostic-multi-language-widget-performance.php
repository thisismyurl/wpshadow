<?php
/**
 * Multi Language Widget Performance Diagnostic
 *
 * Multi Language Widget Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1183.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multi Language Widget Performance Diagnostic Class
 *
 * @since 1.1183.0000
 */
class Diagnostic_MultiLanguageWidgetPerformance extends Diagnostic_Base {

	protected static $slug = 'multi-language-widget-performance';
	protected static $title = 'Multi Language Widget Performance';
	protected static $description = 'Multi Language Widget Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		
		$issues = array();
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
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
				'kb_link'     => 'https://wpshadow.com/kb/multi-language-widget-performance',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Smush Pro Lazy Load Diagnostic
 *
 * Smush Pro Lazy Load detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.758.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Smush Pro Lazy Load Diagnostic Class
 *
 * @since 1.758.0000
 */
class Diagnostic_SmushProLazyLoad extends Diagnostic_Base {

	protected static $slug = 'smush-pro-lazy-load';
	protected static $title = 'Smush Pro Lazy Load';
	protected static $description = 'Smush Pro Lazy Load detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WP_SMUSH_VERSION' ) ) {
			return null;
		}
		
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/smush-pro-lazy-load',
			);
		}
		
		return null;
	}
}

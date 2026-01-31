<?php
/**
 * Pods Framework Field Debugging Diagnostic
 *
 * Pods Framework Field Debugging issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1053.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pods Framework Field Debugging Diagnostic Class
 *
 * @since 1.1053.0000
 */
class Diagnostic_PodsFrameworkFieldDebugging extends Diagnostic_Base {

	protected static $slug = 'pods-framework-field-debugging';
	protected static $title = 'Pods Framework Field Debugging';
	protected static $description = 'Pods Framework Field Debugging issue detected';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/pods-framework-field-debugging',
			);
		}
		
		return null;
	}
}

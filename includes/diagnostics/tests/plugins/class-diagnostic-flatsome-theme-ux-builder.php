<?php
/**
 * Flatsome Theme Ux Builder Diagnostic
 *
 * Flatsome Theme Ux Builder needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1321.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flatsome Theme Ux Builder Diagnostic Class
 *
 * @since 1.1321.0000
 */
class Diagnostic_FlatsomeThemeUxBuilder extends Diagnostic_Base {

	protected static $slug = 'flatsome-theme-ux-builder';
	protected static $title = 'Flatsome Theme Ux Builder';
	protected static $description = 'Flatsome Theme Ux Builder needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! true // Generic check ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/flatsome-theme-ux-builder',
			);
		}
		
		return null;
	}
}

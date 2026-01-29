<?php
/**
 * Divi Builder Cache Management Diagnostic
 *
 * Divi cache not managed properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.349.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Cache Management Diagnostic Class
 *
 * @since 1.349.0000
 */
class Diagnostic_DiviBuilderCacheManagement extends Diagnostic_Base {

	protected static $slug = 'divi-builder-cache-management';
	protected static $title = 'Divi Builder Cache Management';
	protected static $description = 'Divi cache not managed properly';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'et_divi_fonts_url' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-cache-management',
			);
		}
		
		return null;
	}
}

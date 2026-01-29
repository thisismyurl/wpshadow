<?php
/**
 * Crazy Egg Scroll Map Performance Diagnostic
 *
 * Crazy Egg Scroll Map Performance misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1375.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Crazy Egg Scroll Map Performance Diagnostic Class
 *
 * @since 1.1375.0000
 */
class Diagnostic_CrazyEggScrollMapPerformance extends Diagnostic_Base {

	protected static $slug = 'crazy-egg-scroll-map-performance';
	protected static $title = 'Crazy Egg Scroll Map Performance';
	protected static $description = 'Crazy Egg Scroll Map Performance misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! true // Generic check ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/crazy-egg-scroll-map-performance',
			);
		}
		
		return null;
	}
}

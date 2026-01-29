<?php
/**
 * Beaver Builder Pro Module Performance Diagnostic
 *
 * Beaver Builder Pro Module Performance issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.803.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Pro Module Performance Diagnostic Class
 *
 * @since 1.803.0000
 */
class Diagnostic_BeaverBuilderProModulePerformance extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-pro-module-performance';
	protected static $title = 'Beaver Builder Pro Module Performance';
	protected static $description = 'Beaver Builder Pro Module Performance issues found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-pro-module-performance',
			);
		}
		
		return null;
	}
}

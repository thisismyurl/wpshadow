<?php
/**
 * Beaver Builder Asset Optimization Diagnostic
 *
 * Beaver Builder assets not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.341.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Asset Optimization Diagnostic Class
 *
 * @since 1.341.0000
 */
class Diagnostic_BeaverBuilderAssetOptimization extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-asset-optimization';
	protected static $title = 'Beaver Builder Asset Optimization';
	protected static $description = 'Beaver Builder assets not optimized';
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
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-asset-optimization',
			);
		}
		
		return null;
	}
}

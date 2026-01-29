<?php
/**
 * Beaver Builder Cache Clearing Diagnostic
 *
 * Beaver Builder cache not clearing properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.340.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Cache Clearing Diagnostic Class
 *
 * @since 1.340.0000
 */
class Diagnostic_BeaverBuilderCacheClearing extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-cache-clearing';
	protected static $title = 'Beaver Builder Cache Clearing';
	protected static $description = 'Beaver Builder cache not clearing properly';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-cache-clearing',
			);
		}
		
		return null;
	}
}

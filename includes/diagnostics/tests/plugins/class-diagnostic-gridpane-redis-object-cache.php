<?php
/**
 * Gridpane Redis Object Cache Diagnostic
 *
 * Gridpane Redis Object Cache needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gridpane Redis Object Cache Diagnostic Class
 *
 * @since 1.1029.0000
 */
class Diagnostic_GridpaneRedisObjectCache extends Diagnostic_Base {

	protected static $slug = 'gridpane-redis-object-cache';
	protected static $title = 'Gridpane Redis Object Cache';
	protected static $description = 'Gridpane Redis Object Cache needs attention';
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
				'kb_link'     => 'https://wpshadow.com/kb/gridpane-redis-object-cache',
			);
		}
		
		return null;
	}
}

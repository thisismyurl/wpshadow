<?php
/**
 * Local By Flywheel Blueprints Diagnostic
 *
 * Local By Flywheel Blueprints issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1069.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Local By Flywheel Blueprints Diagnostic Class
 *
 * @since 1.1069.0000
 */
class Diagnostic_LocalByFlywheelBlueprints extends Diagnostic_Base {

	protected static $slug = 'local-by-flywheel-blueprints';
	protected static $title = 'Local By Flywheel Blueprints';
	protected static $description = 'Local By Flywheel Blueprints issue detected';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
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
				'kb_link'     => 'https://wpshadow.com/kb/local-by-flywheel-blueprints',
			);
		}
		
		return null;
	}
}

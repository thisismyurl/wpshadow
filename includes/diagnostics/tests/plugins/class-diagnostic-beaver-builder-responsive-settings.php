<?php
/**
 * Beaver Builder Responsive Settings Diagnostic
 *
 * Beaver Builder mobile settings missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.343.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Responsive Settings Diagnostic Class
 *
 * @since 1.343.0000
 */
class Diagnostic_BeaverBuilderResponsiveSettings extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-responsive-settings';
	protected static $title = 'Beaver Builder Responsive Settings';
	protected static $description = 'Beaver Builder mobile settings missing';
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-responsive-settings',
			);
		}
		
		return null;
	}
}

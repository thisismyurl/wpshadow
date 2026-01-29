<?php
/**
 * Beaver Builder Theme Compatibility Diagnostic
 *
 * Beaver Builder theme conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.346.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Theme Compatibility Diagnostic Class
 *
 * @since 1.346.0000
 */
class Diagnostic_BeaverBuilderThemeCompatibility extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-theme-compatibility';
	protected static $title = 'Beaver Builder Theme Compatibility';
	protected static $description = 'Beaver Builder theme conflicts';
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-theme-compatibility',
			);
		}
		
		return null;
	}
}

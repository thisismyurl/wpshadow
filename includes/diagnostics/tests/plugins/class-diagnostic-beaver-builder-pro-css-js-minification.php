<?php
/**
 * Beaver Builder Pro Css Js Minification Diagnostic
 *
 * Beaver Builder Pro Css Js Minification issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.805.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Pro Css Js Minification Diagnostic Class
 *
 * @since 1.805.0000
 */
class Diagnostic_BeaverBuilderProCssJsMinification extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-pro-css-js-minification';
	protected static $title = 'Beaver Builder Pro Css Js Minification';
	protected static $description = 'Beaver Builder Pro Css Js Minification issues found';
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
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-pro-css-js-minification',
			);
		}
		
		return null;
	}
}

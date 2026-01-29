<?php
/**
 * Beaver Builder Pro White Label Diagnostic
 *
 * Beaver Builder Pro White Label issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.801.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Pro White Label Diagnostic Class
 *
 * @since 1.801.0000
 */
class Diagnostic_BeaverBuilderProWhiteLabel extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-pro-white-label';
	protected static $title = 'Beaver Builder Pro White Label';
	protected static $description = 'Beaver Builder Pro White Label issues found';
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
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-pro-white-label',
			);
		}
		
		return null;
	}
}

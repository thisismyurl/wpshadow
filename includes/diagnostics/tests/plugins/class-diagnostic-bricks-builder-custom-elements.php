<?php
/**
 * Bricks Builder Custom Elements Diagnostic
 *
 * Bricks Builder Custom Elements issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.820.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bricks Builder Custom Elements Diagnostic Class
 *
 * @since 1.820.0000
 */
class Diagnostic_BricksBuilderCustomElements extends Diagnostic_Base {

	protected static $slug = 'bricks-builder-custom-elements';
	protected static $title = 'Bricks Builder Custom Elements';
	protected static $description = 'Bricks Builder Custom Elements issues found';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/bricks-builder-custom-elements',
			);
		}
		
		return null;
	}
}

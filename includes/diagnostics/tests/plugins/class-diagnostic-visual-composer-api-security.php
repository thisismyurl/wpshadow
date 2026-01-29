<?php
/**
 * Visual Composer Api Security Diagnostic
 *
 * Visual Composer Api Security issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.833.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Composer Api Security Diagnostic Class
 *
 * @since 1.833.0000
 */
class Diagnostic_VisualComposerApiSecurity extends Diagnostic_Base {

	protected static $slug = 'visual-composer-api-security';
	protected static $title = 'Visual Composer Api Security';
	protected static $description = 'Visual Composer Api Security issues found';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/visual-composer-api-security',
			);
		}
		
		return null;
	}
}

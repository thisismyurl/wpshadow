<?php
/**
 * Flatsome Theme Quick View Diagnostic
 *
 * Flatsome Theme Quick View needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1323.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flatsome Theme Quick View Diagnostic Class
 *
 * @since 1.1323.0000
 */
class Diagnostic_FlatsomeThemeQuickView extends Diagnostic_Base {

	protected static $slug = 'flatsome-theme-quick-view';
	protected static $title = 'Flatsome Theme Quick View';
	protected static $description = 'Flatsome Theme Quick View needs optimization';
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
				'kb_link'     => 'https://wpshadow.com/kb/flatsome-theme-quick-view',
			);
		}
		
		return null;
	}
}

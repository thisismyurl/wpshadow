<?php
/**
 * Elementor Pro Forms Spam Diagnostic
 *
 * Elementor Pro Forms Spam issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.788.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Forms Spam Diagnostic Class
 *
 * @since 1.788.0000
 */
class Diagnostic_ElementorProFormsSpam extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-forms-spam';
	protected static $title = 'Elementor Pro Forms Spam';
	protected static $description = 'Elementor Pro Forms Spam issues found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-forms-spam',
			);
		}
		
		return null;
	}
}

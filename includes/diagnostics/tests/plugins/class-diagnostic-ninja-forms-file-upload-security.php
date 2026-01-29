<?php
/**
 * Ninja Forms File Upload Security Diagnostic
 *
 * Ninja Forms File Upload Security issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1189.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Forms File Upload Security Diagnostic Class
 *
 * @since 1.1189.0000
 */
class Diagnostic_NinjaFormsFileUploadSecurity extends Diagnostic_Base {

	protected static $slug = 'ninja-forms-file-upload-security';
	protected static $title = 'Ninja Forms File Upload Security';
	protected static $description = 'Ninja Forms File Upload Security issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-forms-file-upload-security',
			);
		}
		
		return null;
	}
}

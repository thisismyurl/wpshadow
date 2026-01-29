<?php
/**
 * Formidable Forms Calculated Fields Diagnostic
 *
 * Formidable Forms Calculated Fields issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1195.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms Calculated Fields Diagnostic Class
 *
 * @since 1.1195.0000
 */
class Diagnostic_FormidableFormsCalculatedFields extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-calculated-fields';
	protected static $title = 'Formidable Forms Calculated Fields';
	protected static $description = 'Formidable Forms Calculated Fields issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FrmAppHelper' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-calculated-fields',
			);
		}
		
		return null;
	}
}

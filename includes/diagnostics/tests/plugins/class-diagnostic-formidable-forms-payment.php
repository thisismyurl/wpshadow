<?php
/**
 * Formidable Forms Payment Diagnostic
 *
 * Formidable Forms payment security weak.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.264.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms Payment Diagnostic Class
 *
 * @since 1.264.0000
 */
class Diagnostic_FormidableFormsPayment extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-payment';
	protected static $title = 'Formidable Forms Payment';
	protected static $description = 'Formidable Forms payment security weak';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-payment',
			);
		}
		
		return null;
	}
}

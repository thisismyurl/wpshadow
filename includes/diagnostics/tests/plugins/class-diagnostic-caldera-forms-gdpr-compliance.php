<?php
/**
 * Caldera Forms GDPR Compliance Diagnostic
 *
 * Caldera Forms GDPR settings missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.476.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms GDPR Compliance Diagnostic Class
 *
 * @since 1.476.0000
 */
class Diagnostic_CalderaFormsGdprCompliance extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-gdpr-compliance';
	protected static $title = 'Caldera Forms GDPR Compliance';
	protected static $description = 'Caldera Forms GDPR settings missing';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-gdpr-compliance',
			);
		}
		
		return null;
	}
}

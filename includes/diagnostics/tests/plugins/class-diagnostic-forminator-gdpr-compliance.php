<?php
/**
 * Forminator Gdpr Compliance Diagnostic
 *
 * Forminator Gdpr Compliance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1208.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forminator Gdpr Compliance Diagnostic Class
 *
 * @since 1.1208.0000
 */
class Diagnostic_ForminatorGdprCompliance extends Diagnostic_Base {

	protected static $slug = 'forminator-gdpr-compliance';
	protected static $title = 'Forminator Gdpr Compliance';
	protected static $description = 'Forminator Gdpr Compliance issue found';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/forminator-gdpr-compliance',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Userway Widget Gdpr Compliance Diagnostic
 *
 * Userway Widget Gdpr Compliance not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1101.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Userway Widget Gdpr Compliance Diagnostic Class
 *
 * @since 1.1101.0000
 */
class Diagnostic_UserwayWidgetGdprCompliance extends Diagnostic_Base {

	protected static $slug = 'userway-widget-gdpr-compliance';
	protected static $title = 'Userway Widget Gdpr Compliance';
	protected static $description = 'Userway Widget Gdpr Compliance not compliant';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/userway-widget-gdpr-compliance',
			);
		}
		
		return null;
	}
}

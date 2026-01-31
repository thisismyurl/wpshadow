<?php
/**
 * Gdpr Cookie Compliance Geolocation Diagnostic
 *
 * Gdpr Cookie Compliance Geolocation not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1108.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gdpr Cookie Compliance Geolocation Diagnostic Class
 *
 * @since 1.1108.0000
 */
class Diagnostic_GdprCookieComplianceGeolocation extends Diagnostic_Base {

	protected static $slug = 'gdpr-cookie-compliance-geolocation';
	protected static $title = 'Gdpr Cookie Compliance Geolocation';
	protected static $description = 'Gdpr Cookie Compliance Geolocation not compliant';
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
				'kb_link'     => 'https://wpshadow.com/kb/gdpr-cookie-compliance-geolocation',
			);
		}
		
		return null;
	}
}

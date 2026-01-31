<?php
/**
 * Google Tag Manager Consent Mode Diagnostic
 *
 * Google Tag Manager Consent Mode misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1349.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Tag Manager Consent Mode Diagnostic Class
 *
 * @since 1.1349.0000
 */
class Diagnostic_GoogleTagManagerConsentMode extends Diagnostic_Base {

	protected static $slug = 'google-tag-manager-consent-mode';
	protected static $title = 'Google Tag Manager Consent Mode';
	protected static $description = 'Google Tag Manager Consent Mode misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'GTM4WP_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
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
				'kb_link'     => 'https://wpshadow.com/kb/google-tag-manager-consent-mode',
			);
		}
		
		return null;
	}
}

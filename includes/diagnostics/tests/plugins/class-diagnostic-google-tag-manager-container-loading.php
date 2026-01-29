<?php
/**
 * Google Tag Manager Container Loading Diagnostic
 *
 * Google Tag Manager Container Loading misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1344.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Tag Manager Container Loading Diagnostic Class
 *
 * @since 1.1344.0000
 */
class Diagnostic_GoogleTagManagerContainerLoading extends Diagnostic_Base {

	protected static $slug = 'google-tag-manager-container-loading';
	protected static $title = 'Google Tag Manager Container Loading';
	protected static $description = 'Google Tag Manager Container Loading misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'GTM4WP_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/google-tag-manager-container-loading',
			);
		}
		
		return null;
	}
}

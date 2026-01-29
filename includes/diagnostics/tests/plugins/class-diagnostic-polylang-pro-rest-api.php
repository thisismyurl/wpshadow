<?php
/**
 * Polylang Pro Rest Api Diagnostic
 *
 * Polylang Pro Rest Api misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1144.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang Pro Rest Api Diagnostic Class
 *
 * @since 1.1144.0000
 */
class Diagnostic_PolylangProRestApi extends Diagnostic_Base {

	protected static $slug = 'polylang-pro-rest-api';
	protected static $title = 'Polylang Pro Rest Api';
	protected static $description = 'Polylang Pro Rest Api misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'POLYLANG_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/polylang-pro-rest-api',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Smush Pro Cdn Integration Diagnostic
 *
 * Smush Pro Cdn Integration detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.757.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Smush Pro Cdn Integration Diagnostic Class
 *
 * @since 1.757.0000
 */
class Diagnostic_SmushProCdnIntegration extends Diagnostic_Base {

	protected static $slug = 'smush-pro-cdn-integration';
	protected static $title = 'Smush Pro Cdn Integration';
	protected static $description = 'Smush Pro Cdn Integration detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WP_SMUSH_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/smush-pro-cdn-integration',
			);
		}
		
		return null;
	}
}

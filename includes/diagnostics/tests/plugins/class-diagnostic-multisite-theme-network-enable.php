<?php
/**
 * Multisite Theme Network Enable Diagnostic
 *
 * Multisite Theme Network Enable misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.945.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Theme Network Enable Diagnostic Class
 *
 * @since 1.945.0000
 */
class Diagnostic_MultisiteThemeNetworkEnable extends Diagnostic_Base {

	protected static $slug = 'multisite-theme-network-enable';
	protected static $title = 'Multisite Theme Network Enable';
	protected static $description = 'Multisite Theme Network Enable misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/multisite-theme-network-enable',
			);
		}
		
		return null;
	}
}

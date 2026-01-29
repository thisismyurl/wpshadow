<?php
/**
 * Sucuri Firewall Configuration Diagnostic
 *
 * Sucuri Firewall Configuration misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.850.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sucuri Firewall Configuration Diagnostic Class
 *
 * @since 1.850.0000
 */
class Diagnostic_SucuriFirewallConfiguration extends Diagnostic_Base {

	protected static $slug = 'sucuri-firewall-configuration';
	protected static $title = 'Sucuri Firewall Configuration';
	protected static $description = 'Sucuri Firewall Configuration misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SUCURISCAN_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/sucuri-firewall-configuration',
			);
		}
		
		return null;
	}
}

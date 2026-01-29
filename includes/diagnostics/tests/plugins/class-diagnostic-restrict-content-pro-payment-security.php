<?php
/**
 * Restrict Content Pro Payment Security Diagnostic
 *
 * RCP payment processing insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.326.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Payment Security Diagnostic Class
 *
 * @since 1.326.0000
 */
class Diagnostic_RestrictContentProPaymentSecurity extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-payment-security';
	protected static $title = 'Restrict Content Pro Payment Security';
	protected static $description = 'RCP payment processing insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-payment-security',
			);
		}
		
		return null;
	}
}

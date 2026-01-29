<?php
/**
 * Really Simple Ssl Security Headers Diagnostic
 *
 * Really Simple Ssl Security Headers issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1450.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Really Simple Ssl Security Headers Diagnostic Class
 *
 * @since 1.1450.0000
 */
class Diagnostic_ReallySimpleSslSecurityHeaders extends Diagnostic_Base {

	protected static $slug = 'really-simple-ssl-security-headers';
	protected static $title = 'Really Simple Ssl Security Headers';
	protected static $description = 'Really Simple Ssl Security Headers issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'REALLY_SIMPLE_SSL_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/really-simple-ssl-security-headers',
			);
		}
		
		return null;
	}
}

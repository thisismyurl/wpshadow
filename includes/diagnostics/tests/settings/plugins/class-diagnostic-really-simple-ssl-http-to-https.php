<?php
/**
 * Really Simple Ssl Http To Https Diagnostic
 *
 * Really Simple Ssl Http To Https issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1449.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Really Simple Ssl Http To Https Diagnostic Class
 *
 * @since 1.1449.0000
 */
class Diagnostic_ReallySimpleSslHttpToHttps extends Diagnostic_Base {

	protected static $slug = 'really-simple-ssl-http-to-https';
	protected static $title = 'Really Simple Ssl Http To Https';
	protected static $description = 'Really Simple Ssl Http To Https issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'REALLY_SIMPLE_SSL_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/really-simple-ssl-http-to-https',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Redirection Plugin 404 Monitoring Diagnostic
 *
 * Redirection Plugin 404 Monitoring issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1418.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirection Plugin 404 Monitoring Diagnostic Class
 *
 * @since 1.1418.0000
 */
class Diagnostic_RedirectionPlugin404Monitoring extends Diagnostic_Base {

	protected static $slug = 'redirection-plugin-404-monitoring';
	protected static $title = 'Redirection Plugin 404 Monitoring';
	protected static $description = 'Redirection Plugin 404 Monitoring issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'REDIRECTION_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/redirection-plugin-404-monitoring',
			);
		}
		
		return null;
	}
}

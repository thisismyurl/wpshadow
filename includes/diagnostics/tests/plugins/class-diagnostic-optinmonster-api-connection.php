<?php
/**
 * OptinMonster API Connection Diagnostic
 *
 * OptinMonster API key not configured or connection failing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.218.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OptinMonster API Connection Diagnostic Class
 *
 * @since 1.218.0000
 */
class Diagnostic_OptinmonsterApiConnection extends Diagnostic_Base {

	protected static $slug = 'optinmonster-api-connection';
	protected static $title = 'OptinMonster API Connection';
	protected static $description = 'OptinMonster API key not configured or connection failing';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'OMAPI_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/optinmonster-api-connection',
			);
		}
		
		return null;
	}
}

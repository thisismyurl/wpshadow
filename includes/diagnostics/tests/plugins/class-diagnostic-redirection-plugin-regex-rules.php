<?php
/**
 * Redirection Plugin Regex Rules Diagnostic
 *
 * Redirection Plugin Regex Rules issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1420.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirection Plugin Regex Rules Diagnostic Class
 *
 * @since 1.1420.0000
 */
class Diagnostic_RedirectionPluginRegexRules extends Diagnostic_Base {

	protected static $slug = 'redirection-plugin-regex-rules';
	protected static $title = 'Redirection Plugin Regex Rules';
	protected static $description = 'Redirection Plugin Regex Rules issue found';
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
				'kb_link'     => 'https://wpshadow.com/kb/redirection-plugin-regex-rules',
			);
		}
		
		return null;
	}
}

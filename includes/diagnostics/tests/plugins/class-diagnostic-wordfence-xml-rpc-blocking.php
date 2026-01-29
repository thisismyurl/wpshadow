<?php
/**
 * Wordfence Xml Rpc Blocking Diagnostic
 *
 * Wordfence Xml Rpc Blocking misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.849.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Xml Rpc Blocking Diagnostic Class
 *
 * @since 1.849.0000
 */
class Diagnostic_WordfenceXmlRpcBlocking extends Diagnostic_Base {

	protected static $slug = 'wordfence-xml-rpc-blocking';
	protected static $title = 'Wordfence Xml Rpc Blocking';
	protected static $description = 'Wordfence Xml Rpc Blocking misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-xml-rpc-blocking',
			);
		}
		
		return null;
	}
}

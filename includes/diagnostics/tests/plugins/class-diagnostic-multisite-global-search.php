<?php
/**
 * Multisite Global Search Diagnostic
 *
 * Multisite Global Search misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.986.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Global Search Diagnostic Class
 *
 * @since 1.986.0000
 */
class Diagnostic_MultisiteGlobalSearch extends Diagnostic_Base {

	protected static $slug = 'multisite-global-search';
	protected static $title = 'Multisite Global Search';
	protected static $description = 'Multisite Global Search misconfigured';
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
				'kb_link'     => 'https://wpshadow.com/kb/multisite-global-search',
			);
		}
		
		return null;
	}
}

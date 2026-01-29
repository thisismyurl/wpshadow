<?php
/**
 * Multisite Cross Site Forms Diagnostic
 *
 * Multisite Cross Site Forms misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.985.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Cross Site Forms Diagnostic Class
 *
 * @since 1.985.0000
 */
class Diagnostic_MultisiteCrossSiteForms extends Diagnostic_Base {

	protected static $slug = 'multisite-cross-site-forms';
	protected static $title = 'Multisite Cross Site Forms';
	protected static $description = 'Multisite Cross Site Forms misconfigured';
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
				'kb_link'     => 'https://wpshadow.com/kb/multisite-cross-site-forms',
			);
		}
		
		return null;
	}
}

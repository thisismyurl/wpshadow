<?php
/**
 * Gtranslate Seo Urls Diagnostic
 *
 * Gtranslate Seo Urls misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1163.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gtranslate Seo Urls Diagnostic Class
 *
 * @since 1.1163.0000
 */
class Diagnostic_GtranslateSeoUrls extends Diagnostic_Base {

	protected static $slug = 'gtranslate-seo-urls';
	protected static $title = 'Gtranslate Seo Urls';
	protected static $description = 'Gtranslate Seo Urls misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! true // Generic check ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/gtranslate-seo-urls',
			);
		}
		
		return null;
	}
}

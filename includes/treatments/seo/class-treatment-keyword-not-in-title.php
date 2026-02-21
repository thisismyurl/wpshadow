<?php
/**
 * Missing Primary Keyword in Title Treatment
 *
 * Tests whether the primary keyword appears in the title tag. Missing the
 * target keyword from the title can result in a 40% ranking penalty.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Keyword_Not_In_Title Class
 *
 * Detects when focus keywords are not present in title tags. The title is
 * the most important on-page SEO factor after content.
 *
 * @since 1.5003.1200
 */
class Treatment_Keyword_Not_In_Title extends Treatment_Base {

	protected static $slug = 'keyword-not-in-title';
	protected static $title = 'Missing Primary Keyword in Title';
	protected static $description = 'Tests whether primary keywords appear in title tags';
	protected static $family = 'keyword-strategy';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Keyword_Not_In_Title' );
	}
}

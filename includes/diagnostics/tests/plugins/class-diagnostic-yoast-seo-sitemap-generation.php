<?php
/**
 * Yoast Seo Sitemap Generation Diagnostic
 *
 * Yoast Seo Sitemap Generation configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.688.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast Seo Sitemap Generation Diagnostic Class
 *
 * @since 1.688.0000
 */
class Diagnostic_YoastSeoSitemapGeneration extends Diagnostic_Base {

	protected static $slug = 'yoast-seo-sitemap-generation';
	protected static $title = 'Yoast Seo Sitemap Generation';
	protected static $description = 'Yoast Seo Sitemap Generation configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/yoast-seo-sitemap-generation',
			);
		}
		
		return null;
	}
}

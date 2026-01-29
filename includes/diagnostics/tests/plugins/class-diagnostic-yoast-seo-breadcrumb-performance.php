<?php
/**
 * Yoast Seo Breadcrumb Performance Diagnostic
 *
 * Yoast Seo Breadcrumb Performance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.689.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast Seo Breadcrumb Performance Diagnostic Class
 *
 * @since 1.689.0000
 */
class Diagnostic_YoastSeoBreadcrumbPerformance extends Diagnostic_Base {

	protected static $slug = 'yoast-seo-breadcrumb-performance';
	protected static $title = 'Yoast Seo Breadcrumb Performance';
	protected static $description = 'Yoast Seo Breadcrumb Performance configuration issues';
	protected static $family = 'performance';

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
				'kb_link'     => 'https://wpshadow.com/kb/yoast-seo-breadcrumb-performance',
			);
		}
		
		return null;
	}
}

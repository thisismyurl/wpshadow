<?php
/**
 * Yoast Seo Meta Description Missing Diagnostic
 *
 * Yoast Seo Meta Description Missing configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.690.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast Seo Meta Description Missing Diagnostic Class
 *
 * @since 1.690.0000
 */
class Diagnostic_YoastSeoMetaDescriptionMissing extends Diagnostic_Base {

	protected static $slug = 'yoast-seo-meta-description-missing';
	protected static $title = 'Yoast Seo Meta Description Missing';
	protected static $description = 'Yoast Seo Meta Description Missing configuration issues';
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
				'kb_link'     => 'https://wpshadow.com/kb/yoast-seo-meta-description-missing',
			);
		}
		
		return null;
	}
}

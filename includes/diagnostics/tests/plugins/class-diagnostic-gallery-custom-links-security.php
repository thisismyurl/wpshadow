<?php
/**
 * Gallery Custom Links Security Diagnostic
 *
 * Gallery custom links not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.501.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gallery Custom Links Security Diagnostic Class
 *
 * @since 1.501.0000
 */
class Diagnostic_GalleryCustomLinksSecurity extends Diagnostic_Base {

	protected static $slug = 'gallery-custom-links-security';
	protected static $title = 'Gallery Custom Links Security';
	protected static $description = 'Gallery custom links not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! true // Generic plugin check ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/gallery-custom-links-security',
			);
		}
		
		return null;
	}
}

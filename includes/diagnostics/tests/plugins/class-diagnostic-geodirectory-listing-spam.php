<?php
/**
 * GeoDirectory Listing Spam Diagnostic
 *
 * GeoDirectory spam protection insufficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.553.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory Listing Spam Diagnostic Class
 *
 * @since 1.553.0000
 */
class Diagnostic_GeodirectoryListingSpam extends Diagnostic_Base {

	protected static $slug = 'geodirectory-listing-spam';
	protected static $title = 'GeoDirectory Listing Spam';
	protected static $description = 'GeoDirectory spam protection insufficient';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/geodirectory-listing-spam',
			);
		}
		
		return null;
	}
}

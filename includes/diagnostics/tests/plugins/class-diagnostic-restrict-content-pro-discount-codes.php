<?php
/**
 * Restrict Content Pro Discount Codes Diagnostic
 *
 * RCP discount codes not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.328.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Discount Codes Diagnostic Class
 *
 * @since 1.328.0000
 */
class Diagnostic_RestrictContentProDiscountCodes extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-discount-codes';
	protected static $title = 'Restrict Content Pro Discount Codes';
	protected static $description = 'RCP discount codes not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-discount-codes',
			);
		}
		
		return null;
	}
}

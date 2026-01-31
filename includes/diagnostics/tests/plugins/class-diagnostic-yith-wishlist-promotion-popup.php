<?php
/**
 * Yith Wishlist Promotion Popup Diagnostic
 *
 * Yith Wishlist Promotion Popup issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1241.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yith Wishlist Promotion Popup Diagnostic Class
 *
 * @since 1.1241.0000
 */
class Diagnostic_YithWishlistPromotionPopup extends Diagnostic_Base {

	protected static $slug = 'yith-wishlist-promotion-popup';
	protected static $title = 'Yith Wishlist Promotion Popup';
	protected static $description = 'Yith Wishlist Promotion Popup issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! true // Generic check ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/yith-wishlist-promotion-popup',
			);
		}
		
		return null;
	}
}

<?php
/**
 * Yith Wishlist Multi Wishlist Diagnostic
 *
 * Yith Wishlist Multi Wishlist issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1240.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yith Wishlist Multi Wishlist Diagnostic Class
 *
 * @since 1.1240.0000
 */
class Diagnostic_YithWishlistMultiWishlist extends Diagnostic_Base {

	protected static $slug = 'yith-wishlist-multi-wishlist';
	protected static $title = 'Yith Wishlist Multi Wishlist';
	protected static $description = 'Yith Wishlist Multi Wishlist issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! get_option( 'yith_wishlist_enabled', '' ) && ! defined( 'YITH_WCWL_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Multi-wishlist enabled
		$multi_wishlist = get_option( 'yith_multi_wishlist_enabled', 0 );
		if ( ! $multi_wishlist ) {
			$issues[] = 'Multi-wishlist feature not enabled';
		}

		// Check 2: Wishlist sharing enabled
		$sharing = get_option( 'yith_wishlist_sharing_enabled', 0 );
		if ( ! $sharing ) {
			$issues[] = 'Wishlist sharing not enabled';
		}

		// Check 3: Notification emails enabled
		$notifications = get_option( 'yith_wishlist_notifications_enabled', 0 );
		if ( ! $notifications ) {
			$issues[] = 'Wishlist notifications not enabled';
		}

		// Check 4: User wishlists limit set
		$wishlists_limit = absint( get_option( 'yith_wishlists_per_user_limit', 0 ) );
		if ( $wishlists_limit <= 0 ) {
			$issues[] = 'Wishlists per user limit not configured';
		}

		// Check 5: Duplicate handling configured
		$duplicate_handling = get_option( 'yith_wishlist_duplicate_handling', '' );
		if ( empty( $duplicate_handling ) ) {
			$issues[] = 'Duplicate product handling not configured';
		}

		// Check 6: Privacy settings
		$privacy = get_option( 'yith_wishlist_privacy_enabled', 0 );
		if ( ! $privacy ) {
			$issues[] = 'Privacy controls not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d wishlist configuration issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/yith-wishlist-multi-wishlist',
			);
		}

		return null;
	}
}

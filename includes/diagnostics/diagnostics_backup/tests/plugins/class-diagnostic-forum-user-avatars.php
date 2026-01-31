<?php
/**
 * Forum User Avatars Diagnostic
 *
 * Forum avatars not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.537.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forum User Avatars Diagnostic Class
 *
 * @since 1.537.0000
 */
class Diagnostic_ForumUserAvatars extends Diagnostic_Base {

	protected static $slug = 'forum-user-avatars';
	protected static $title = 'Forum User Avatars';
	protected static $description = 'Forum avatars not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'bp_is_active' ) && ! class_exists( 'BuddyPress' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify avatar optimization
		$avatar_optimization = get_option( 'forum_avatar_optimization', false );
		if ( ! $avatar_optimization ) {
			$issues[] = __( 'Forum avatar optimization not enabled', 'wpshadow' );
		}

		// Check 2: Check avatar image compression
		$compression = get_option( 'forum_avatar_compression', false );
		if ( ! $compression ) {
			$issues[] = __( 'Avatar image compression not enabled', 'wpshadow' );
		}

		// Check 3: Verify lazy loading for avatars
		$lazy_load = get_option( 'forum_avatar_lazy_load', false );
		if ( ! $lazy_load ) {
			$issues[] = __( 'Avatar lazy loading not enabled', 'wpshadow' );
		}

		// Check 4: Check avatar caching
		$avatar_cache = get_transient( 'forum_avatar_cache' );
		if ( false === $avatar_cache ) {
			$issues[] = __( 'Avatar caching not active', 'wpshadow' );
		}

		// Check 5: Verify responsive sizing
		$responsive_sizing = get_option( 'forum_avatar_responsive', false );
		if ( ! $responsive_sizing ) {
			$issues[] = __( 'Responsive avatar sizing not enabled', 'wpshadow' );
		}

		// Check 6: Check CDN usage for avatars
		$cdn_enabled = get_option( 'forum_avatar_cdn', false );
		if ( ! $cdn_enabled ) {
			$issues[] = __( 'CDN not enabled for avatar delivery', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Forum user avatars performance issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/forum-user-avatars',
			);
		}

		return null;
	}
}

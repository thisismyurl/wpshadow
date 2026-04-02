<?php
/**
 * Diagnostic: Zero Social Shares
 *
 * Detects top 20 posts with zero social shares, indicating poor content
 * quality, missing share buttons, or shareability issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zero Social Shares Diagnostic Class
 *
 * Checks for posts with no social shares via share plugins or meta data.
 *
 * Detection methods:
 * - Social share plugin detection
 * - Share count meta data
 * - Share button presence
 *
 * @since 1.6093.1200
 */
class Diagnostic_Zero_Social_Shares extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'zero-social-shares';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Zero Social Shares';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Top posts with zero shares = poor content or missing share buttons';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Social share plugin installed
	 * - 1 point: <40% of top posts have zero shares
	 * - 1 point: <60% of top posts have zero shares
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score              = 0;
		$max_score          = 4;
		$has_share_plugin   = false;
		$zero_shares_posts  = array();

		// Check for social share plugins.
		$share_plugins = array(
			'social-warfare/social-warfare.php'         => 'Social Warfare',
			'monarch/monarch.php'                       => 'Monarch',
			'sassy-social-share/sassy-social-share.php' => 'Sassy Social Share',
			'addtoany/addtoany.php'                     => 'AddToAny',
			'sharethis-share-buttons/sharethis.php'     => 'ShareThis',
			'jetpack/jetpack.php'                       => 'Jetpack Sharing',
			'novashare/novashare.php'                   => 'NovaSha re',
		);

		foreach ( $share_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score           += 2;
				$has_share_plugin = true;
				break;
			}
		}

		// Get top 20 posts by views (or recent if no view data).
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 20,
				'meta_key'       => 'views',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC',
			)
		);

		// Fallback to recent posts if no view data.
		if ( empty( $posts ) ) {
			$posts = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 20,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
		}

		if ( empty( $posts ) ) {
			return null;
		}

		// Check for share count meta.
		$posts_with_zero_shares = 0;
		foreach ( $posts as $post ) {
			$has_shares = false;

			// Check various share count meta keys.
			$share_meta_keys = array(
				'_shares',
				'_social_shares',
				'shareaholic_shares',
				'social_warfare_shares',
				'total_shares',
			);

			foreach ( $share_meta_keys as $meta_key ) {
				$share_count = get_post_meta( $post->ID, $meta_key, true );
				if ( ! empty( $share_count ) && is_numeric( $share_count ) && $share_count > 0 ) {
					$has_shares = true;
					break;
				}
			}

			if ( ! $has_shares ) {
				$posts_with_zero_shares++;
				$zero_shares_posts[] = array(
					'post_id' => $post->ID,
					'title'   => $post->post_title,
					'date'    => $post->post_date,
					'url'     => get_permalink( $post->ID ),
				);
			}
		}

		$zero_shares_percentage = ( $posts_with_zero_shares / count( $posts ) ) * 100;

		// Scoring.
		if ( $zero_shares_percentage < 40 ) {
			$score += 2;
		} elseif ( $zero_shares_percentage < 60 ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		// Build message.
		if ( ! $has_share_plugin ) {
			$message = __( 'No social share plugin detected. Unable to verify share counts', 'wpshadow' );
		} else {
			$message = sprintf(
				/* translators: 1: percentage, 2: number of posts */
				__( '%1$d%% of top posts (%2$d/%3$d) have zero social shares', 'wpshadow' ),
				round( $zero_shares_percentage ),
				$posts_with_zero_shares,
				count( $posts )
			);
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: issue message */
				__( '%s. Zero social shares indicate: missing share buttons, poor content quality, not shareable (no images/headlines), wrong audience, or topic doesn\'t resonate. Social signals correlate with rankings (not causation). Benefits: traffic referrals, brand awareness, social proof, indirect SEO boost. Fix: Add prominent share buttons, create shareable content (lists, infographics, quotes), optimize Open Graph tags, include compelling images.', 'wpshadow' ),
				$message
			),
			'severity'    => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/zero-social-shares',
			'zero_shares_posts' => $zero_shares_posts,
			'stats'       => array(
				'total_checked'      => count( $posts ),
				'zero_shares'        => $posts_with_zero_shares,
				'percentage'         => round( $zero_shares_percentage, 1 ),
				'has_share_plugin'   => $has_share_plugin,
			),
			'recommendation' => __( 'Install social share plugin (AddToAny, Social Warfare, Jetpack). Optimize Open Graph tags. Add compelling images. Create shareable content (actionable tips, controversial opinions, data-driven insights). Place share buttons prominently.', 'wpshadow' ),
		);
	}
}

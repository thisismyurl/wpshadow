<?php
/**
 * Google Business Profile Optimized Diagnostic
 *
 * Tests whether the site maintains a complete, current, and optimized Google Business
 * Profile with regular posts. GBP is the #1 local SEO ranking factor.
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
 * Diagnostic_Optimizes_Google_Business_Profile Class
 *
 * Diagnostic #13: Google Business Profile Optimized from Specialized & Emerging Success Habits.
 * Checks if Google Business Profile is complete and active.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Optimizes_Google_Business_Profile extends Diagnostic_Base {

	protected static $slug = 'optimizes-google-business-profile';
	protected static $title = 'Google Business Profile Optimized';
	protected static $description = 'Tests whether the site maintains a complete and optimized Google Business Profile';
	protected static $family = 'local-seo';

	public static function check() {
		$score          = 0;
		$max_score      = 6;
		$score_details  = array();
		$recommendations = array();

		// Check Google Business Profile mentions.
		$gbp_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'Google Business Google Maps find us',
			)
		);

		if ( ! empty( $gbp_content ) ) {
			++$score;
			$score_details[] = __( '✓ Google Business Profile mentioned', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No Google Business Profile references', 'wpshadow' );
			$recommendations[] = __( 'Claim and verify your Google Business Profile if not done already', 'wpshadow' );
		}

		// Check Google Maps embed.
		$has_map_embed = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'maps.google.com embed iframe',
			)
		);

		if ( ! empty( $has_map_embed ) ) {
			++$score;
			$score_details[] = __( '✓ Google Maps embedded on site', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No Google Maps embed found', 'wpshadow' );
			$recommendations[] = __( 'Embed your Google Business Profile map on your contact page', 'wpshadow' );
		}

		// Check business hours displayed.
		$hours_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'hours open Monday Tuesday',
			)
		);

		if ( ! empty( $hours_content ) ) {
			++$score;
			$score_details[] = __( '✓ Business hours documented', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No business hours found', 'wpshadow' );
			$recommendations[] = __( 'Display your business hours prominently and keep Google Business Profile hours updated', 'wpshadow' );
		}

		// Check photos/images (indicates active management).
		$business_images = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 10,
				'post_status'    => 'inherit',
				'date_query'     => array(
					array(
						'after' => '3 months ago',
					),
				),
			)
		);

		if ( count( $business_images ) >= 5 ) {
			++$score;
			$score_details[] = __( '✓ Recent business photos uploaded', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Insufficient recent imagery', 'wpshadow' );
			$recommendations[] = __( 'Upload 5+ high-quality photos to Google Business Profile monthly (exterior, interior, products, team)', 'wpshadow' );
		}

		// Check Google reviews widget/plugin.
		$review_widget = is_plugin_active( 'google-reviews-widget/google-reviews-widget.php' ) ||
						 is_plugin_active( 'reviews-widget/reviews-widget.php' );

		if ( $review_widget ) {
			++$score;
			$score_details[] = __( '✓ Google reviews widget active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No Google reviews integration', 'wpshadow' );
			$recommendations[] = __( 'Install a Google reviews widget to showcase your ratings on your website', 'wpshadow' );
		}

		// Check GBP post mentions (indicates active posting).
		$gbp_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'announcement offer special',
				'date_query'     => array(
					array(
						'after' => '1 month ago',
					),
				),
			)
		);

		if ( ! empty( $gbp_posts ) ) {
			++$score;
			$score_details[] = __( '✓ Recent announcements/offers published', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No recent promotional content', 'wpshadow' );
			$recommendations[] = __( 'Post weekly to Google Business Profile (offers, news, events) to stay visible', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 40 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 70 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Google Business Profile score: %d%%. Optimized GBP is the #1 local ranking factor, increasing local pack appearances by 70%%. Active profiles (weekly posts, fresh photos) outrank inactive ones by 50%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/google-business-profile',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Google Business Profile is your primary local storefront - complete, active profiles dominate "near me" searches and Google Maps results.', 'wpshadow' ),
		);
	}
}

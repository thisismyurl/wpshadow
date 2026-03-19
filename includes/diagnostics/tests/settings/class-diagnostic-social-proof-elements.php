<?php
/**
 * Social Proof Elements Diagnostic
 *
 * Tests whether the site uses social proof (reviews, testimonials, counters) to build trust and increase conversions.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Proof Elements Diagnostic Class
 *
 * Social proof increases conversions by 15-34% by reducing purchase anxiety
 * and building trust through others' experiences.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Social_Proof_Elements extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-proof-elements';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Proof Elements';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses social proof to build trust and increase conversions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$social_proof_score = 0;
		$max_score = 6;

		// Check for customer reviews.
		$reviews = self::check_reviews();
		if ( $reviews ) {
			$social_proof_score++;
		} else {
			$issues[] = __( 'No customer reviews visible on products/services', 'wpshadow' );
		}

		// Check for testimonials.
		$testimonials = self::check_testimonials();
		if ( $testimonials ) {
			$social_proof_score++;
		} else {
			$issues[] = __( 'No testimonial sections showcasing customer success', 'wpshadow' );
		}

		// Check for trust badges.
		$trust_badges = self::check_trust_badges();
		if ( $trust_badges ) {
			$social_proof_score++;
		} else {
			$issues[] = __( 'Missing trust badges (payment security, certifications)', 'wpshadow' );
		}

		// Check for social counters.
		$social_counters = self::check_social_counters();
		if ( $social_counters ) {
			$social_proof_score++;
		} else {
			$issues[] = __( 'No social proof counters (customers served, purchases)', 'wpshadow' );
		}

		// Check for recent activity.
		$recent_activity = self::check_recent_activity();
		if ( $recent_activity ) {
			$social_proof_score++;
		} else {
			$issues[] = __( 'No recent activity notifications (recent purchases, signups)', 'wpshadow' );
		}

		// Check for case studies.
		$case_studies = self::check_case_studies();
		if ( $case_studies ) {
			$social_proof_score++;
		} else {
			$issues[] = __( 'No detailed case studies showing results', 'wpshadow' );
		}

		// Determine severity based on social proof implementation.
		$social_proof_percentage = ( $social_proof_score / $max_score ) * 100;

		if ( $social_proof_percentage < 30 ) {
			$severity = 'medium';
			$threat_level = 30;
		} elseif ( $social_proof_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Social proof implementation percentage */
				__( 'Social proof at %d%%. ', 'wpshadow' ),
				(int) $social_proof_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Social proof increases conversions 15-34%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/social-proof-elements',
			);
		}

		return null;
	}

	/**
	 * Check for reviews.
	 *
	 * @since 1.6093.1200
	 * @return bool True if reviews exist, false otherwise.
	 */
	private static function check_reviews() {
		// WooCommerce has built-in reviews.
		if ( class_exists( 'WooCommerce' ) ) {
			$reviews_enabled = get_option( 'woocommerce_enable_reviews', 'yes' );
			if ( 'yes' === $reviews_enabled ) {
				return true;
			}
		}

		// Check for review plugins.
		$review_plugins = array(
			'site-reviews/site-reviews.php',
			'wp-review/wp-review.php',
			'yet-another-stars-rating/yet-another-stars-rating.php',
		);

		foreach ( $review_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for review content.
		$query = new \WP_Query(
			array(
				's'              => 'review rating customer',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for testimonials.
	 *
	 * @since 1.6093.1200
	 * @return bool True if testimonials exist, false otherwise.
	 */
	private static function check_testimonials() {
		// Check for testimonial plugins.
		$testimonial_plugins = array(
			'strong-testimonials/strong-testimonials.php',
			'testimonial-builder/testimonial-builder.php',
		);

		foreach ( $testimonial_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for testimonial content.
		$query = new \WP_Query(
			array(
				's'              => 'testimonial client said',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for trust badges.
	 *
	 * @since 1.6093.1200
	 * @return bool True if trust badges exist, false otherwise.
	 */
	private static function check_trust_badges() {
		$keywords = array( 'secure payment', 'ssl', 'money back guarantee', 'verified', 'certified' );
		$badge_found = 0;

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				$badge_found++;
			}
		}

		return ( $badge_found >= 2 );
	}

	/**
	 * Check for social counters.
	 *
	 * @since 1.6093.1200
	 * @return bool True if counters exist, false otherwise.
	 */
	private static function check_social_counters() {
		// Check for counter plugins.
		if ( is_plugin_active( 'social-warfare/social-warfare.php' ) ) {
			return true;
		}

		// Check for counter content.
		$keywords = array( 'customers served', 'happy customers', 'products sold', 'members joined' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for recent activity.
	 *
	 * @since 1.6093.1200
	 * @return bool True if activity notifications exist, false otherwise.
	 */
	private static function check_recent_activity() {
		// Check for notification plugins.
		$notification_plugins = array(
			'notification-x/notification-x.php',
			'wp-notification-bars/wp-notification-bars.php',
		);

		foreach ( $notification_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_activity_notifications', false );
	}

	/**
	 * Check for case studies.
	 *
	 * @since 1.6093.1200
	 * @return bool True if case studies exist, false otherwise.
	 */
	private static function check_case_studies() {
		// Check for case study content.
		$query = new \WP_Query(
			array(
				's'              => 'case study results success story',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}
}

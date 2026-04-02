<?php
/**
 * User-Generated Content Encouraged Diagnostic
 *
 * Tests whether the site actively encourages users to contribute content that enriches the community.
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
 * User-Generated Content Encouraged Diagnostic Class
 *
 * User-generated content increases engagement by 450% and creates 7x more
 * authentic connections than brand content alone.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Encourages_User_Generated_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'encourages-user-generated-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User-Generated Content Encouraged';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site actively encourages users to contribute content that enriches the community';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$ugc_score = 0;
		$max_score = 5;

		// Check for user submission forms.
		$submission_forms = self::check_submission_forms();
		if ( $submission_forms ) {
			$ugc_score++;
		} else {
			$issues[] = __( 'No user content submission forms or capabilities', 'wpshadow' );
		}

		// Check for commenting enabled.
		$comments_enabled = self::check_comments_enabled();
		if ( $comments_enabled ) {
			$ugc_score++;
		} else {
			$issues[] = __( 'Comments disabled on most posts', 'wpshadow' );
		}

		// Check for UGC campaigns.
		$ugc_campaigns = self::check_ugc_campaigns();
		if ( $ugc_campaigns ) {
			$ugc_score++;
		} else {
			$issues[] = __( 'No active campaigns encouraging user contributions', 'wpshadow' );
		}

		// Check for user testimonials/reviews.
		$testimonials = self::check_testimonials();
		if ( $testimonials ) {
			$ugc_score++;
		} else {
			$issues[] = __( 'Not showcasing user testimonials or reviews', 'wpshadow' );
		}

		// Check for content recognition.
		$content_recognition = self::check_content_recognition();
		if ( $content_recognition ) {
			$ugc_score++;
		} else {
			$issues[] = __( 'Not recognizing or featuring user contributions', 'wpshadow' );
		}

		// Determine severity based on UGC encouragement.
		$ugc_percentage = ( $ugc_score / $max_score ) * 100;

		if ( $ugc_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $ugc_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: UGC encouragement percentage */
				__( 'User-generated content encouragement at %d%%. ', 'wpshadow' ),
				(int) $ugc_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'UGC increases engagement by 450%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/encourages-user-generated-content',
			);
		}

		return null;
	}

	/**
	 * Check submission forms.
	 *
	 * @since 1.6093.1200
	 * @return bool True if forms exist, false otherwise.
	 */
	private static function check_submission_forms() {
		// Check for form plugins.
		if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ||
			 is_plugin_active( 'wpforms-lite/wpforms.php' ) ||
			 is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			return true;
		}

		// Check for user submission plugins.
		if ( is_plugin_active( 'user-submitted-posts/user-submitted-posts.php' ) ||
			 is_plugin_active( 'wp-user-frontend/wpuf.php' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check comments enabled.
	 *
	 * @since 1.6093.1200
	 * @return bool True if enabled, false otherwise.
	 */
	private static function check_comments_enabled() {
		// Check if comments are open on recent posts.
		$query = new \WP_Query(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$open_count = 0;
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				if ( comments_open() ) {
					$open_count++;
				}
			}
			wp_reset_postdata();
		}

		// If more than half have comments enabled.
		return ( $open_count >= 5 );
	}

	/**
	 * Check UGC campaigns.
	 *
	 * @since 1.6093.1200
	 * @return bool True if campaigns exist, false otherwise.
	 */
	private static function check_ugc_campaigns() {
		// Check for UGC campaign content.
		$keywords = array( 'share your', 'submit your', 'contribute', 'contest', 'challenge' );

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
	 * Check testimonials.
	 *
	 * @since 1.6093.1200
	 * @return bool True if exist, false otherwise.
	 */
	private static function check_testimonials() {
		// Check for testimonial content.
		$keywords = array( 'testimonial', 'review', 'success story', 'case study' );

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
	 * Check content recognition.
	 *
	 * @since 1.6093.1200
	 * @return bool True if recognized, false otherwise.
	 */
	private static function check_content_recognition() {
		// Check for recognition content.
		$keywords = array( 'featured', 'spotlight', 'showcase', 'highlighted' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' member user',
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
}

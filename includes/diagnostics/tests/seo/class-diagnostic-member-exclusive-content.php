<?php
/**
 * Member Exclusive Content Diagnostic
 *
 * Tests whether the site provides exclusive content for members to maintain value.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Member Exclusive Content Diagnostic Class
 *
 * Exclusive content is a primary value driver for membership retention.
 * Sites need consistent, high-quality exclusive content to justify ongoing subscriptions.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Member_Exclusive_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'member-exclusive-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Exclusive Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site provides exclusive content for members to maintain value';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for membership sites.
		if ( ! self::is_membership_site() ) {
			return null;
		}

		$issues = array();
		$exclusive_score = 0;
		$max_score = 6;

		// Check for restricted content.
		$restricted_content = self::check_restricted_content();
		if ( $restricted_content ) {
			$exclusive_score++;
		} else {
			$issues[] = __( 'No protected member-only content detected', 'wpshadow' );
		}

		// Check content publishing frequency.
		$content_frequency = self::check_content_frequency();
		if ( $content_frequency ) {
			$exclusive_score++;
		} else {
			$issues[] = __( 'No regular exclusive content published in last 30 days', 'wpshadow' );
		}

		// Check for diverse content types.
		$content_diversity = self::check_content_diversity();
		if ( $content_diversity ) {
			$exclusive_score++;
		} else {
			$issues[] = __( 'Limited variety in exclusive content types', 'wpshadow' );
		}

		// Check for member forums/community.
		$community = self::check_community_features();
		if ( $community ) {
			$exclusive_score++;
		} else {
			$issues[] = __( 'No exclusive community or forum features', 'wpshadow' );
		}

		// Check for downloadable resources.
		$downloads = self::check_downloadable_resources();
		if ( $downloads ) {
			$exclusive_score++;
		} else {
			$issues[] = __( 'No exclusive downloadable resources or tools', 'wpshadow' );
		}

		// Check for content exclusivity messaging.
		$messaging = self::check_exclusivity_messaging();
		if ( $messaging ) {
			$exclusive_score++;
		} else {
			$issues[] = __( 'No clear messaging about exclusive member benefits', 'wpshadow' );
		}

		// Determine severity based on exclusive content implementation.
		$exclusive_percentage = ( $exclusive_score / $max_score ) * 100;

		if ( $exclusive_percentage < 40 ) {
			$severity = 'high';
			$threat_level = 65;
		} elseif ( $exclusive_percentage < 70 ) {
			$severity = 'medium';
			$threat_level = 45;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Exclusive content percentage */
				__( 'Exclusive content strategy at %d%%. ', 'wpshadow' ),
				(int) $exclusive_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Regular exclusive content increases retention by 20-30%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/member-exclusive-content?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check if this is a membership site.
	 *
	 * @since 0.6093.1200
	 * @return bool True if membership features detected, false otherwise.
	 */
	private static function is_membership_site() {
		$membership_plugins = array(
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
			'memberpress/memberpress.php',
			'woocommerce-memberships/woocommerce-memberships.php',
			'restrict-content/restrict-content.php',
		);

		foreach ( $membership_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for restricted content.
	 *
	 * @since 0.6093.1200
	 * @return bool True if restricted content exists, false otherwise.
	 */
	private static function check_restricted_content() {
		// Check for protected posts/pages.
		$protected = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_is_restricted',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'pmpro_access',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'rcp_access_level',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( ! empty( $protected ) ) {
			return true;
		}

		// Check for member-only categories.
		$categories = get_categories( array( 'number' => 100 ) );
		foreach ( $categories as $category ) {
			if ( strpos( strtolower( $category->name ), 'member' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_restricted_content', false );
	}

	/**
	 * Check content frequency.
	 *
	 * @since 0.6093.1200
	 * @return bool True if recent exclusive content exists, false otherwise.
	 */
	private static function check_content_frequency() {
		$thirty_days_ago = date( 'Y-m-d', strtotime( '-30 days' ) );

		$recent_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'date_query'     => array(
					array(
						'after' => $thirty_days_ago,
					),
				),
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_is_restricted',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'pmpro_access',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		return ! empty( $recent_posts );
	}

	/**
	 * Check content diversity.
	 *
	 * @since 0.6093.1200
	 * @return bool True if diverse content types exist, false otherwise.
	 */
	private static function check_content_diversity() {
		$content_types = array();

		// Check for videos.
		if ( is_plugin_active( 'video-embed-thumbnail-generator/video-embed-thumbnail-generator.php' ) ) {
			$content_types[] = 'video';
		}

		// Check for courses.
		if ( is_plugin_active( 'learndash/learndash.php' ) || is_plugin_active( 'sensei-lms/sensei-lms.php' ) ) {
			$content_types[] = 'courses';
		}

		// Check for downloads.
		if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
			$content_types[] = 'downloads';
		}

		// Check for webinars/events.
		if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) {
			$content_types[] = 'events';
		}

		// Need at least 2 different content types.
		return count( $content_types ) >= 2;
	}

	/**
	 * Check for community features.
	 *
	 * @since 0.6093.1200
	 * @return bool True if community features exist, false otherwise.
	 */
	private static function check_community_features() {
		$community_plugins = array(
			'bbpress/bbpress.php',
			'buddypress/bp-loader.php',
			'wpforo/wpforo.php',
			'peepso-core/peepso.php',
		);

		foreach ( $community_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_community_features', false );
	}

	/**
	 * Check for downloadable resources.
	 *
	 * @since 0.6093.1200
	 * @return bool True if downloads exist, false otherwise.
	 */
	private static function check_downloadable_resources() {
		// Check for attachment posts.
		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => '_is_restricted',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( ! empty( $attachments ) ) {
			return true;
		}

		// Check for download plugins.
		if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
			return true;
		}

		// Check for download-related content.
		$query = new \WP_Query(
			array(
				's'              => 'download pdf template worksheet',
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for exclusivity messaging.
	 *
	 * @since 0.6093.1200
	 * @return bool True if messaging exists, false otherwise.
	 */
	private static function check_exclusivity_messaging() {
		$keywords = array( 'member exclusive', 'members only', 'premium content', 'subscriber benefits' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_exclusivity_messaging', false );
	}
}

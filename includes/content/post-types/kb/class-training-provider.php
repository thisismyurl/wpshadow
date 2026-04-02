<?php
/**
 * Training Provider
 *
 * Manages training courses and video library integration.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Training Provider
 */
class Training_Provider {
	/**
	 * Get available training courses.
	 *
	 * @return array Training courses.
	 */
	public static function get_courses() {
		return array(
			'security-101'        => array(
				'title'            => __( 'WordPress Security 101', 'wpshadow' ),
				'description'      => __( 'Learn the fundamentals of keeping your WordPress site secure.', 'wpshadow' ),
				'duration'         => '15 minutes',
				'difficulty'       => 'Beginner',
				'topics'           => array( 'ssl', 'security-headers', 'admin-username' ),
				'video_url'        => '',
				'estimate_benefit' => __( '2-3 hours saved per year', 'wpshadow' ),
			),
			'performance-basics'  => array(
				'title'            => __( 'Site Performance Basics', 'wpshadow' ),
				'description'      => __( 'Speed up your WordPress site with proven optimization techniques.', 'wpshadow' ),
				'duration'         => '20 minutes',
				'difficulty'       => 'Beginner',
				'topics'           => array( 'memory-limit', 'external-fonts', 'image-lazy-load', 'jquery-migrate' ),
				'video_url'        => '',
				'estimate_benefit' => __( '5-10 hours saved per year', 'wpshadow' ),
			),
			'seo-essentials'      => array(
				'title'            => __( 'SEO Essentials for WordPress', 'wpshadow' ),
				'description'      => __( 'Optimize your site for search engines without plugins or complexity.', 'wpshadow' ),
				'duration'         => '12 minutes',
				'difficulty'       => 'Beginner',
				'topics'           => array( 'tagline', 'permalinks', 'wp-generator', 'security-headers' ),
				'video_url'        => '',
				'estimate_benefit' => __( '3-5 hours saved per year', 'wpshadow' ),
			),
			'accessibility-intro' => array(
				'title'            => __( 'Accessibility Fundamentals', 'wpshadow' ),
				'description'      => __( 'Make your WordPress site accessible to everyone, including people with disabilities.', 'wpshadow' ),
				'duration'         => '18 minutes',
				'difficulty'       => 'Intermediate',
				'topics'           => array( 'aria-labels', 'skiplinks', 'color-contrast', 'alt-text' ),
				'video_url'        => '',
				'estimate_benefit' => __( '1-2 hours saved per year', 'wpshadow' ),
			),
			'maintenance-pro'     => array(
				'title'            => __( 'WordPress Maintenance Pro', 'wpshadow' ),
				'description'      => __( 'Advanced maintenance strategies to keep your site running smoothly.', 'wpshadow' ),
				'duration'         => '25 minutes',
				'difficulty'       => 'Advanced',
				'topics'           => array( 'database-health', 'error-logging', 'backup-strategy', 'plugin-updates' ),
				'video_url'        => '',
				'estimate_benefit' => __( '8-10 hours saved per year', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get training topics/lessons.
	 *
	 * @return array Training topics.
	 */
	public static function get_topics() {
		return array(
			'ssl'              => array(
				'title'       => __( 'Understanding SSL Certificates', 'wpshadow' ),
				'duration'    => '3 min',
				'course'      => 'security-101',
				'description' => __( 'What SSL is, why you need it, and how to set it up.', 'wpshadow' ),
			),
			'security-headers' => array(
				'title'       => __( 'Security Headers Explained', 'wpshadow' ),
				'duration'    => '4 min',
				'course'      => 'security-101',
				'description' => __( 'Protect your site from common web attacks with security headers.', 'wpshadow' ),
			),
			'admin-username'   => array(
				'title'       => __( 'Why Custom Admin Usernames Matter', 'wpshadow' ),
				'duration'    => '3 min',
				'course'      => 'security-101',
				'description' => __( 'The risks of "admin" and how to stay secure.', 'wpshadow' ),
			),
			'memory-limit'     => array(
				'title'       => __( 'PHP Memory Limit Optimization', 'wpshadow' ),
				'duration'    => '4 min',
				'course'      => 'performance-basics',
				'description' => __( 'When and how to increase memory for better performance.', 'wpshadow' ),
			),
			'external-fonts'   => array(
				'title'       => __( 'Self-Hosted Fonts for Speed', 'wpshadow' ),
				'duration'    => '5 min',
				'course'      => 'performance-basics',
				'description' => __( 'Why external fonts slow you down and how to fix it.', 'wpshadow' ),
			),
			'image-lazy-load'  => array(
				'title'       => __( 'Lazy Loading Images', 'wpshadow' ),
				'duration'    => '4 min',
				'course'      => 'performance-basics',
				'description' => __( 'Speed up page loads with native lazy loading.', 'wpshadow' ),
			),
			'tagline'          => array(
				'title'       => __( 'Taglines & SEO', 'wpshadow' ),
				'duration'    => '3 min',
				'course'      => 'seo-essentials',
				'description' => __( 'Why site taglines matter for search engines and visitors.', 'wpshadow' ),
			),
			'permalinks'       => array(
				'title'       => __( 'Permalink Structures for SEO', 'wpshadow' ),
				'duration'    => '3 min',
				'course'      => 'seo-essentials',
				'description' => __( 'Choose the right permalink structure for SEO success.', 'wpshadow' ),
			),
			'aria-labels'      => array(
				'title'       => __( 'ARIA Labels & Screen Readers', 'wpshadow' ),
				'duration'    => '4 min',
				'course'      => 'accessibility-intro',
				'description' => __( 'Make your site accessible to screen reader users.', 'wpshadow' ),
			),
			'skiplinks'        => array(
				'title'       => __( 'Skip Links for Accessibility', 'wpshadow' ),
				'duration'    => '3 min',
				'course'      => 'accessibility-intro',
				'description' => __( 'Help keyboard users navigate your site quickly.', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get training for a specific diagnostic/treatment.
	 *
	 * @param string $item_id Diagnostic or treatment ID.
	 * @param string $type    Type: 'diagnostic' or 'treatment'.
	 * @return array Training recommendations.
	 */
	public static function get_training_for_item( $item_id, $type = 'diagnostic' ) {
		$topics   = self::get_topics();
		$matching = array();

		// Map item IDs to training topics
		$mapping = array(
			'ssl'              => array( 'ssl' ),
			'security-headers' => array( 'security-headers' ),
			'admin-username'   => array( 'admin-username' ),
			'memory-limit'     => array( 'memory-limit' ),
			'external-fonts'   => array( 'external-fonts' ),
			'image-lazy-load'  => array( 'image-lazy-load' ),
			'tagline'          => array( 'tagline' ),
			'permalinks'       => array( 'permalinks' ),
			'nav-aria'         => array( 'aria-labels' ),
			'skiplinks'        => array( 'skiplinks' ),
		);

		if ( isset( $mapping[ $item_id ] ) ) {
			foreach ( $mapping[ $item_id ] as $topic_id ) {
				if ( isset( $topics[ $topic_id ] ) ) {
					$matching[ $topic_id ] = $topics[ $topic_id ];
				}
			}
		}

		return $matching;
	}

	/**
	 * Get suggested next courses based on completed training.
	 *
	 * @param int   $user_id User ID.
	 * @param int   $limit   Max suggestions.
	 * @return array Suggested courses.
	 */
	public static function get_course_recommendations( $user_id, $limit = 3 ) {
		$courses     = self::get_courses();
		$completed   = Training_Progress::get_completed_courses( $user_id );
		$suggestions = array();

		// Suggest courses user hasn't completed
		foreach ( $courses as $id => $course ) {
			if ( ! isset( $completed[ $id ] ) ) {
				$suggestions[ $id ] = $course;
			}

			if ( count( $suggestions ) >= $limit ) {
				break;
			}
		}

		return $suggestions;
	}

	/**
	 * Get a course by ID.
	 *
	 * @param string $course_id Course ID.
	 * @return array|null Course data or null.
	 */
	public static function get_course( $course_id ) {
		$courses = self::get_courses();
		return isset( $courses[ $course_id ] ) ? $courses[ $course_id ] : null;
	}

	/**
	 * Get courses by difficulty level.
	 *
	 * @param string $difficulty Difficulty level.
	 * @return array Courses.
	 */
	public static function get_courses_by_difficulty( $difficulty ) {
		$courses = self::get_courses();
		$result  = array();

		foreach ( $courses as $id => $course ) {
			if ( isset( $course['difficulty'] ) && $course['difficulty'] === $difficulty ) {
				$result[ $id ] = $course;
			}
		}

		return $result;
	}
}

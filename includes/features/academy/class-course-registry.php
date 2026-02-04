<?php
/**
 * WPShadow Academy - Course Registry
 *
 * Registry of 10+ complete courses with multiple lessons each.
 *
 * @package    WPShadow
 * @subpackage Academy
 * @since      1.6030.1915
 */

declare(strict_types=1);

namespace WPShadow\Academy;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Course_Registry Class
 *
 * Manages courses and their relationships to issue families.
 *
 * @since 1.6030.1915
 */
class Course_Registry extends Hook_Subscriber_Base {

	/**
	 * Registered courses
	 *
	 * @var array
	 */
	private static $courses = array();

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(); // Configuration only, no hooks needed
	}

	/**
	 * Initialize registry (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Course_Registry::subscribe() instead
	 * @since      1.6030.1915
	 * @return     void
	 */
	public static function init() {
		self::register_courses();
	}

	/**
	 * Register all courses
	 *
	 * @since  1.6030.1915
	 * @return void
	 */
	private static function register_courses() {
		// WordPress Security Master Course.
		self::register(
			'wordpress-security',
			array(
				'title'          => __( 'WordPress Security Masterclass', 'wpshadow' ),
				'description'    => __( 'Learn to protect your WordPress site from hackers, malware, and security vulnerabilities.', 'wpshadow' ),
				'url'            => 'https://wpshadow.com/academy/courses/wordpress-security/',
				'thumbnail'      => 'https://wpshadow.com/academy/images/course-security.jpg',
				'lesson_count'   => 12,
				'total_duration' => 3600, // 60 minutes.
				'difficulty'     => 'intermediate',
				'issue_families' => array( 'security' ),
				'free'           => true,
				'lessons'        => array(
					array( 'title' => __( 'Introduction to WordPress Security', 'wpshadow' ), 'duration' => 300 ),
					array( 'title' => __( 'SSL/HTTPS Setup', 'wpshadow' ), 'duration' => 420 ),
					array( 'title' => __( 'File Permissions', 'wpshadow' ), 'duration' => 300 ),
					array( 'title' => __( 'Database Security', 'wpshadow' ), 'duration' => 360 ),
					array( 'title' => __( 'Login Security & Two-Factor Auth', 'wpshadow' ), 'duration' => 420 ),
					array( 'title' => __( 'Firewall Configuration', 'wpshadow' ), 'duration' => 480 ),
					array( 'title' => __( 'Malware Scanning', 'wpshadow' ), 'duration' => 300 ),
					array( 'title' => __( 'Backup Strategy', 'wpshadow' ), 'duration' => 360 ),
					array( 'title' => __( 'Security Headers', 'wpshadow' ), 'duration' => 240 ),
					array( 'title' => __( 'Plugin Security Best Practices', 'wpshadow' ), 'duration' => 300 ),
					array( 'title' => __( 'Incident Response', 'wpshadow' ), 'duration' => 420 ),
					array( 'title' => __( 'Ongoing Security Maintenance', 'wpshadow' ), 'duration' => 300 ),
				),
			)
		);

		// WordPress Performance Optimization.
		self::register(
			'wordpress-performance',
			array(
				'title'          => __( 'WordPress Performance Optimization', 'wpshadow' ),
				'description'    => __( 'Master caching, database optimization, and advanced speed techniques.', 'wpshadow' ),
				'url'            => 'https://wpshadow.com/academy/courses/wordpress-performance/',
				'thumbnail'      => 'https://wpshadow.com/academy/images/course-performance.jpg',
				'lesson_count'   => 10,
				'total_duration' => 3000, // 50 minutes.
				'difficulty'     => 'intermediate',
				'issue_families' => array( 'performance' ),
				'free'           => true,
				'lessons'        => array(
					array( 'title' => __( 'Understanding WordPress Performance', 'wpshadow' ), 'duration' => 240 ),
					array( 'title' => __( 'Page Caching', 'wpshadow' ), 'duration' => 360 ),
					array( 'title' => __( 'Object Caching', 'wpshadow' ), 'duration' => 300 ),
					array( 'title' => __( 'Database Optimization', 'wpshadow' ), 'duration' => 420 ),
					array( 'title' => __( 'Image Optimization', 'wpshadow' ), 'duration' => 300 ),
					array( 'title' => __( 'CDN Setup', 'wpshadow' ), 'duration' => 360 ),
					array( 'title' => __( 'Lazy Loading', 'wpshadow' ), 'duration' => 240 ),
					array( 'title' => __( 'Minification & Compression', 'wpshadow' ), 'duration' => 300 ),
					array( 'title' => __( 'PHP & Server Configuration', 'wpshadow' ), 'duration' => 300 ),
					array( 'title' => __( 'Performance Monitoring', 'wpshadow' ), 'duration' => 180 ),
				),
			)
		);

		// GDPR & Privacy Compliance.
		self::register(
			'gdpr-privacy',
			array(
				'title'          => __( 'GDPR & Privacy Compliance for WordPress', 'wpshadow' ),
				'description'    => __( 'Complete guide to GDPR, CCPA, and privacy best practices.', 'wpshadow' ),
				'url'            => 'https://wpshadow.com/academy/courses/gdpr-privacy/',
				'thumbnail'      => 'https://wpshadow.com/academy/images/course-privacy.jpg',
				'lesson_count'   => 8,
				'total_duration' => 2400, // 40 minutes.
				'difficulty'     => 'intermediate',
				'issue_families' => array( 'privacy' ),
				'free'           => true,
			)
		);

		// WordPress SEO Fundamentals.
		self::register(
			'wordpress-seo',
			array(
				'title'          => __( 'WordPress SEO Fundamentals', 'wpshadow' ),
				'description'    => __( 'Learn technical SEO, on-page optimization, and content strategy.', 'wpshadow' ),
				'url'            => 'https://wpshadow.com/academy/courses/wordpress-seo/',
				'thumbnail'      => 'https://wpshadow.com/academy/images/course-seo.jpg',
				'lesson_count'   => 15,
				'total_duration' => 4500, // 75 minutes.
				'difficulty'     => 'beginner',
				'issue_families' => array( 'seo' ),
				'free'           => true,
			)
		);

		// WordPress Database Management.
		self::register(
			'database-management',
			array(
				'title'          => __( 'WordPress Database Management', 'wpshadow' ),
				'description'    => __( 'Master database structure, optimization, and troubleshooting.', 'wpshadow' ),
				'url'            => 'https://wpshadow.com/academy/courses/database-management/',
				'thumbnail'      => 'https://wpshadow.com/academy/images/course-database.jpg',
				'lesson_count'   => 9,
				'total_duration' => 2700, // 45 minutes.
				'difficulty'     => 'advanced',
				'issue_families' => array( 'database', 'performance' ),
				'free'           => true,
			)
		);

		// Plugin Development Best Practices.
		self::register(
			'plugin-development',
			array(
				'title'          => __( 'WordPress Plugin Development Best Practices', 'wpshadow' ),
				'description'    => __( 'Build secure, performant, and maintainable WordPress plugins.', 'wpshadow' ),
				'url'            => 'https://wpshadow.com/academy/courses/plugin-development/',
				'thumbnail'      => 'https://wpshadow.com/academy/images/course-plugin-dev.jpg',
				'lesson_count'   => 20,
				'total_duration' => 7200, // 120 minutes.
				'difficulty'     => 'advanced',
				'issue_families' => array( 'code-quality', 'performance', 'security' ),
				'free'           => false, // Pro course.
			)
		);

		// WordPress Accessibility (WCAG 2.1).
		self::register(
			'accessibility-wcag',
			array(
				'title'          => __( 'WordPress Accessibility: WCAG 2.1 Compliance', 'wpshadow' ),
				'description'    => __( 'Make your WordPress site accessible to all users.', 'wpshadow' ),
				'url'            => 'https://wpshadow.com/academy/courses/accessibility/',
				'thumbnail'      => 'https://wpshadow.com/academy/images/course-accessibility.jpg',
				'lesson_count'   => 12,
				'total_duration' => 3600, // 60 minutes.
				'difficulty'     => 'intermediate',
				'issue_families' => array( 'accessibility' ),
				'free'           => true,
			)
		);

		// Allow other modules to register courses.
		do_action( 'wpshadow_academy_register_courses' );
	}

	/**
	 * Register a course
	 *
	 * @since  1.6030.1915
	 * @param  string $id Course ID.
	 * @param  array  $data Course data.
	 * @return void
	 */
	public static function register( $id, $data ) {
		self::$courses[ $id ] = $data;
	}

	/**
	 * Get course by ID
	 *
	 * @since  1.6030.1915
	 * @param  string $id Course ID.
	 * @return array|null Course data or null.
	 */
	public static function get( $id ) {
		return isset( self::$courses[ $id ] ) ? self::$courses[ $id ] : null;
	}

	/**
	 * Get course for issue
	 *
	 * @since  1.6030.1915
	 * @param  string $issue_slug Issue slug.
	 * @return array|null Course data or null.
	 */
	public static function get_course_for_issue( $issue_slug ) {
		// Map issue to family.
		$family = self::get_issue_family( $issue_slug );

		if ( $family ) {
			return self::get_course_for_family( $family );
		}

		return null;
	}

	/**
	 * Get course for issue family
	 *
	 * @since  1.6030.1915
	 * @param  string $family Issue family.
	 * @return array|null Course data or null.
	 */
	public static function get_course_for_family( $family ) {
		foreach ( self::$courses as $id => $course ) {
			if ( isset( $course['issue_families'] ) && in_array( $family, $course['issue_families'], true ) ) {
				$course['id'] = $id;
				return $course;
			}
		}

		return null;
	}

	/**
	 * Get all free courses
	 *
	 * @since  1.6030.1915
	 * @return array Free courses.
	 */
	public static function get_free_courses() {
		$results = array();

		foreach ( self::$courses as $id => $course ) {
			if ( isset( $course['free'] ) && $course['free'] ) {
				$course['id'] = $id;
				$results[]    = $course;
			}
		}

		return $results;
	}

	/**
	 * Get all courses
	 *
	 * @since  1.6030.1915
	 * @return array All registered courses.
	 */
	public static function get_all() {
		$results = array();

		foreach ( self::$courses as $id => $course ) {
			$course['id'] = $id;
			$results[]    = $course;
		}

		return $results;
	}

	/**
	 * Get issue family from slug
	 *
	 * @since  1.6030.1915
	 * @param  string $issue_slug Issue slug.
	 * @return string|null Issue family or null.
	 */
	private static function get_issue_family( $issue_slug ) {
		// Map common issue patterns to families.
		$family_map = array(
			'ssl'          => 'security',
			'security'     => 'security',
			'permission'   => 'security',
			'firewall'     => 'security',
			'memory'       => 'performance',
			'cache'        => 'performance',
			'database'     => 'database',
			'slow'         => 'performance',
			'gdpr'         => 'privacy',
			'privacy'      => 'privacy',
			'cookie'       => 'privacy',
			'seo'          => 'seo',
			'sitemap'      => 'seo',
			'title'        => 'seo',
			'accessibility' => 'accessibility',
			'alt-text'     => 'accessibility',
		);

		foreach ( $family_map as $pattern => $family ) {
			if ( strpos( $issue_slug, $pattern ) !== false ) {
				return $family;
			}
		}

		return null;
	}

	/**
	 * Format duration for display
	 *
	 * @since  1.6030.1915
	 * @param  int $seconds Duration in seconds.
	 * @return string Formatted duration.
	 */
	public static function format_duration( $seconds ) {
		$hours   = floor( $seconds / 3600 );
		$minutes = floor( ( $seconds % 3600 ) / 60 );

		if ( $hours > 0 ) {
			return sprintf(
				/* translators: 1: hours, 2: minutes */
				__( '%1$d hr %2$d min', 'wpshadow' ),
				$hours,
				$minutes
			);
		}

		return sprintf(
			/* translators: %d: minutes */
			__( '%d min', 'wpshadow' ),
			$minutes
		);
	}
}

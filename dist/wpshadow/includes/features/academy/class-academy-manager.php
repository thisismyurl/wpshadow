<?php
/**
 * WPShadow Academy Manager
 *
 * Central orchestrator for the adaptive learning system.
 * Provides contextual education based on user's site issues.
 *
 * @package    WPShadow
 * @subpackage Academy
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Academy;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Settings_Registry;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Academy_Manager Class
 *
 * Manages smart learning prompts, tracks user progress,
 * and recommends personalized learning paths.
 *
 * @since 1.6093.1200
 */
class Academy_Manager extends Hook_Subscriber_Base {

	/**
	 * Singleton instance
	 *
	 * @var Academy_Manager|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @since 1.6093.1200
	 * @return Academy_Manager Instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.6093.1200
	 */
	private function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Get hook subscriptions.
	 *
	 * Note: Academy_Manager uses instance methods, not static methods,
	 * so we override get_hooks() to return instance-based subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		// Note: This is called statically but Academy_Manager uses
		// instance methods. The instance is created in init().
		return array(); // Hooks registered in setup_hooks() instead
	}

	/**
	 * Get the minimum required version for this feature.
	 *
	 * @since 1.6093.1200
	 * @return string Minimum required version.
	 */
	protected static function get_required_version(): string {
		return '1.6089';
	}

	/**
	 * Setup WordPress hooks (instance method)
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private function setup_hooks() {
		if ( class_exists( '\\WPShadow\\Academy\\Academy_Release_Gate' ) && ! Academy_Release_Gate::is_available() ) {
			return;
		}

		// Smart prompts after diagnostic checks.
		add_action( 'wpshadow_after_diagnostic_check', array( $this, 'maybe_suggest_learning' ), 10, 3 );

		// Smart prompts after treatment application.
		add_action( 'wpshadow_after_treatment_apply', array( $this, 'maybe_suggest_post_treatment_learning' ), 10, 3 );

		// Track KB article views.
		add_action( 'wpshadow_kb_article_viewed', array( $this, 'track_article_view' ), 10, 2 );

		// Track video completions.
		add_action( 'wpshadow_training_video_completed', array( $this, 'track_video_completion' ), 10, 2 );

		// Track course completions.
		add_action( 'wpshadow_course_completed', array( $this, 'track_course_completion' ), 10, 2 );

		// Detect struggling patterns.
		add_action( 'wpshadow_issue_recurring', array( $this, 'detect_struggling_pattern' ), 10, 2 );
	}

	/**
	 * Initialize Academy system (deprecated)
	 *
	 * @deprecated1.0 Academy_Manager is singleton with instance hooks
	 * @since 1.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::get_instance();
	}

	/**
	 * Maybe suggest learning after diagnostic check
	 *
	 * Triggered by wpshadow_after_diagnostic_check hook.
	 *
	 * @since 1.6093.1200
	 * @param  string     $class Diagnostic class name.
	 * @param  string     $slug Diagnostic slug.
	 * @param  array|null $finding Finding result (null if no issues).
	 * @return void
	 */
	public function maybe_suggest_learning( $class, $slug, $finding ) {
		if ( null === $finding ) {
			return; // No issue found, no learning needed.
		}

		// Check if user wants education prompts.
		$prompts_enabled = Settings_Registry::get( 'academy_prompts_enabled', true );
		if ( ! $prompts_enabled ) {
			return;
		}

		// Get KB article for this diagnostic.
		$kb_article = $this->get_kb_article_for_diagnostic( $slug );

		if ( $kb_article ) {
			// Store suggestion for display in UI.
			$this->store_learning_suggestion(
				array(
					'type'        => 'kb_article',
					'title'       => __( 'Want to understand what we just checked?', 'wpshadow' ),
					'description' => $kb_article['title'],
					'url'         => $kb_article['url'],
					'context'     => 'post_diagnostic',
					'finding_id'  => $slug,
				)
			);
		}

		// Check if user has struggled with this issue before.
		$struggle_count = $this->get_issue_occurrence_count( $slug );
		if ( $struggle_count >= 3 ) {
			// User is struggling, offer deeper learning.
			$this->suggest_course_for_issue( $slug );
		}
	}

	/**
	 * Maybe suggest learning after treatment application
	 *
	 * @since 1.6093.1200
	 * @param  string $class Treatment class name.
	 * @param  string $finding_id Finding ID treated.
	 * @param  array  $result Treatment result.
	 * @return void
	 */
	public function maybe_suggest_post_treatment_learning( $class, $finding_id, $result ) {
		if ( ! isset( $result['success'] ) || ! $result['success'] ) {
			return; // Treatment failed, don't prompt learning.
		}

		// Get video tutorial for this treatment.
		$video = $this->get_video_for_treatment( $finding_id );

		if ( $video ) {
			$this->store_learning_suggestion(
				array(
					'type'        => 'video',
					'title'       => __( 'Want to see what we just fixed?', 'wpshadow' ),
					'description' => $video['title'],
					'url'         => $video['url'],
					'duration'    => $video['duration'],
					'context'     => 'post_treatment',
					'finding_id'  => $finding_id,
				)
			);
		}
	}

	/**
	 * Detect struggling pattern and offer course
	 *
	 * Triggered when user encounters same issue 3+ times.
	 *
	 * @since 1.6093.1200
	 * @param  string $issue_slug Issue slug.
	 * @param  int    $count Occurrence count.
	 * @return void
	 */
	public function detect_struggling_pattern( $issue_slug, $count ) {
		if ( $count < 3 ) {
			return;
		}

		$this->suggest_course_for_issue( $issue_slug );
	}

	/**
	 * Suggest course for recurring issue
	 *
	 * @since 1.6093.1200
	 * @param  string $issue_slug Issue slug.
	 * @return void
	 */
	private function suggest_course_for_issue( $issue_slug ) {
		$course = $this->get_course_for_issue( $issue_slug );

		if ( $course ) {
			$this->store_learning_suggestion(
				array(
					'type'        => 'course',
					'title'       => sprintf(
						/* translators: %d: occurrence count */
						__( 'You\'ve fixed this %d times. Want to learn how to prevent it?', 'wpshadow' ),
						$this->get_issue_occurrence_count( $issue_slug )
					),
					'description' => $course['title'],
					'url'         => $course['url'],
					'lessons'     => $course['lesson_count'],
					'duration'    => $course['total_duration'],
					'context'     => 'struggling',
					'finding_id'  => $issue_slug,
					'priority'    => 'high',
				)
			);
		}
	}

	/**
	 * Track KB article view
	 *
	 * @since 1.6093.1200
	 * @param  string $article_id Article ID.
	 * @param  int    $user_id User ID.
	 * @return void
	 */
	public function track_article_view( $article_id, $user_id ) {
		$views = get_user_meta( $user_id, 'wpshadow_academy_articles_viewed', true );
		if ( ! is_array( $views ) ) {
			$views = array();
		}

		if ( ! in_array( $article_id, $views, true ) ) {
			$views[] = $article_id;
			update_user_meta( $user_id, 'wpshadow_academy_articles_viewed', $views );

			Activity_Logger::log(
				'academy_article_viewed',
				array(
					'article_id' => $article_id,
					'user_id'    => $user_id,
				)
			);

			// Check for achievements.
			do_action( 'wpshadow_academy_article_viewed', $article_id, $user_id );
		}
	}

	/**
	 * Track video completion
	 *
	 * @since 1.6093.1200
	 * @param  string $video_id Video ID.
	 * @param  int    $user_id User ID.
	 * @return void
	 */
	public function track_video_completion( $video_id, $user_id ) {
		$completed = get_user_meta( $user_id, 'wpshadow_academy_videos_completed', true );
		if ( ! is_array( $completed ) ) {
			$completed = array();
		}

		if ( ! in_array( $video_id, $completed, true ) ) {
			$completed[] = $video_id;
			update_user_meta( $user_id, 'wpshadow_academy_videos_completed', $completed );

			Activity_Logger::log(
				'academy_video_completed',
				array(
					'video_id' => $video_id,
					'user_id'  => $user_id,
				)
			);

			// Check for achievements.
			do_action( 'wpshadow_academy_video_completed', $video_id, $user_id );
		}
	}

	/**
	 * Track course completion
	 *
	 * @since 1.6093.1200
	 * @param  string $course_id Course ID.
	 * @param  int    $user_id User ID.
	 * @return void
	 */
	public function track_course_completion( $course_id, $user_id ) {
		$completed = get_user_meta( $user_id, 'wpshadow_academy_courses_completed', true );
		if ( ! is_array( $completed ) ) {
			$completed = array();
		}

		if ( ! in_array( $course_id, $completed, true ) ) {
			$completed[] = $course_id;
			update_user_meta( $user_id, 'wpshadow_academy_courses_completed', $completed );

			Activity_Logger::log(
				'academy_course_completed',
				array(
					'course_id' => $course_id,
					'user_id'   => $user_id,
				)
			);

			// Check for achievements and rewards.
			do_action( 'wpshadow_academy_course_completed', $course_id, $user_id );

			// Update learning path progress.
			$this->update_learning_path_progress( $user_id, $course_id );
		}
	}

	/**
	 * Get KB article for diagnostic
	 *
	 * @since 1.6093.1200
	 * @param  string $diagnostic_slug Diagnostic slug.
	 * @return array|null Article data or null if not found.
	 */
	private function get_kb_article_for_diagnostic( $diagnostic_slug ) {
		// Get from KB Article Registry.
		if ( class_exists( '\\WPShadow\\Academy\\KB_Article_Registry' ) ) {
			return \WPShadow\Academy\KB_Article_Registry::get_article_for_diagnostic( $diagnostic_slug );
		}

		return null;
	}

	/**
	 * Get video for treatment
	 *
	 * @since 1.6093.1200
	 * @param  string $finding_id Finding ID.
	 * @return array|null Video data or null if not found.
	 */
	private function get_video_for_treatment( $finding_id ) {
		// Get from Training Video Registry.
		if ( class_exists( '\\WPShadow\\Academy\\Training_Video_Registry' ) ) {
			return \WPShadow\Academy\Training_Video_Registry::get_video_for_finding( $finding_id );
		}

		return null;
	}

	/**
	 * Get course for recurring issue
	 *
	 * @since 1.6093.1200
	 * @param  string $issue_slug Issue slug.
	 * @return array|null Course data or null if not found.
	 */
	private function get_course_for_issue( $issue_slug ) {
		// Get from Course Registry.
		if ( class_exists( '\\WPShadow\\Academy\\Course_Registry' ) ) {
			return \WPShadow\Academy\Course_Registry::get_course_for_issue( $issue_slug );
		}

		return null;
	}

	/**
	 * Store learning suggestion for display
	 *
	 * @since 1.6093.1200
	 * @param  array $suggestion Suggestion data.
	 * @return void
	 */
	private function store_learning_suggestion( $suggestion ) {
		$suggestions = get_option( 'wpshadow_academy_pending_suggestions', array() );

		$suggestion['timestamp'] = current_time( 'mysql' );
		$suggestion['shown']     = false;

		$suggestions[] = $suggestion;

		// Keep only last 10 suggestions.
		if ( count( $suggestions ) > 10 ) {
			$suggestions = array_slice( $suggestions, -10 );
		}

		update_option( 'wpshadow_academy_pending_suggestions', $suggestions );
	}

	/**
	 * Get pending learning suggestions
	 *
	 * @since 1.6093.1200
	 * @return array Suggestions array.
	 */
	public function get_pending_suggestions() {
		return get_option( 'wpshadow_academy_pending_suggestions', array() );
	}

	/**
	 * Mark suggestion as shown
	 *
	 * @since 1.6093.1200
	 * @param  int $index Suggestion index.
	 * @return void
	 */
	public function mark_suggestion_shown( $index ) {
		$suggestions = get_option( 'wpshadow_academy_pending_suggestions', array() );

		if ( isset( $suggestions[ $index ] ) ) {
			$suggestions[ $index ]['shown'] = true;
			update_option( 'wpshadow_academy_pending_suggestions', $suggestions );
		}
	}

	/**
	 * Get issue occurrence count
	 *
	 * @since 1.6093.1200
	 * @param  string $issue_slug Issue slug.
	 * @return int Occurrence count.
	 */
	private function get_issue_occurrence_count( $issue_slug ) {
		$occurrences = get_option( 'wpshadow_academy_issue_occurrences', array() );
		return isset( $occurrences[ $issue_slug ] ) ? (int) $occurrences[ $issue_slug ] : 0;
	}

	/**
	 * Increment issue occurrence count
	 *
	 * @since 1.6093.1200
	 * @param  string $issue_slug Issue slug.
	 * @return void
	 */
	public function increment_issue_occurrence( $issue_slug ) {
		$occurrences = get_option( 'wpshadow_academy_issue_occurrences', array() );

		if ( ! isset( $occurrences[ $issue_slug ] ) ) {
			$occurrences[ $issue_slug ] = 0;
		}

		++$occurrences[ $issue_slug ];

		update_option( 'wpshadow_academy_issue_occurrences', $occurrences );

		// Trigger struggling detection.
		do_action( 'wpshadow_issue_recurring', $issue_slug, $occurrences[ $issue_slug ] );
	}

	/**
	 * Get user's learning progress
	 *
	 * @since 1.6093.1200
	 * @param  int $user_id User ID.
	 * @return array {
	 *     Learning progress data.
	 *
	 *     @type int   $articles_viewed Articles viewed count.
	 *     @type int   $videos_completed Videos completed count.
	 *     @type int   $courses_completed Courses completed count.
	 *     @type int   $courses_in_progress Courses in progress count.
	 *     @type array $learning_path Current learning path.
	 *     @type array $next_recommended Next recommended content.
	 * }
	 */
	public function get_user_progress( $user_id ) {
		$articles_viewed   = get_user_meta( $user_id, 'wpshadow_academy_articles_viewed', true );
		$videos_completed  = get_user_meta( $user_id, 'wpshadow_academy_videos_completed', true );
		$courses_completed = get_user_meta( $user_id, 'wpshadow_academy_courses_completed', true );
		$courses_in_progress = get_user_meta( $user_id, 'wpshadow_academy_courses_in_progress', true );

		return array(
			'articles_viewed'      => is_array( $articles_viewed ) ? count( $articles_viewed ) : 0,
			'videos_completed'     => is_array( $videos_completed ) ? count( $videos_completed ) : 0,
			'courses_completed'    => is_array( $courses_completed ) ? count( $courses_completed ) : 0,
			'courses_in_progress'  => is_array( $courses_in_progress ) ? count( $courses_in_progress ) : 0,
			'learning_path'        => $this->get_learning_path( $user_id ),
			'next_recommended'     => $this->get_next_recommended_content( $user_id ),
		);
	}

	/**
	 * Get personalized learning path for user
	 *
	 * @since 1.6093.1200
	 * @param  int $user_id User ID.
	 * @return array Learning path (courses ordered by relevance).
	 */
	private function get_learning_path( $user_id ) {
		// Analyze user's site issues.
		$site_issues = $this->analyze_site_issues();

		// Match issues to courses.
		$recommended_courses = array();

		foreach ( $site_issues as $issue_family => $issue_count ) {
			$course = $this->get_course_for_family( $issue_family );
			if ( $course ) {
				$recommended_courses[] = array(
					'course'   => $course,
					'priority' => $issue_count, // More issues = higher priority.
					'reason'   => sprintf(
						/* translators: 1: issue count, 2: issue family */
						__( 'Your site has %1$d %2$s issues', 'wpshadow' ),
						$issue_count,
						$issue_family
					),
				);
			}
		}

		// Sort by priority.
		usort(
			$recommended_courses,
			function ( $a, $b ) {
				return $b['priority'] - $a['priority'];
			}
		);

		return $recommended_courses;
	}

	/**
	 * Analyze site issues to determine learning needs
	 *
	 * @since 1.6093.1200
	 * @return array Issue families with counts.
	 */
	private function analyze_site_issues() {
		// Get all findings from diagnostics.
		$findings = get_option( 'wpshadow_findings_cache', array() );

		$issue_families = array();

		foreach ( $findings as $finding ) {
			if ( isset( $finding['family'] ) ) {
				$family = $finding['family'];
				if ( ! isset( $issue_families[ $family ] ) ) {
					$issue_families[ $family ] = 0;
				}
				++$issue_families[ $family ];
			}
		}

		return $issue_families;
	}

	/**
	 * Get course for issue family
	 *
	 * @since 1.6093.1200
	 * @param  string $family Issue family.
	 * @return array|null Course data.
	 */
	private function get_course_for_family( $family ) {
		if ( class_exists( '\\WPShadow\\Academy\\Course_Registry' ) ) {
			return \WPShadow\Academy\Course_Registry::get_course_for_family( $family );
		}

		return null;
	}

	/**
	 * Get next recommended content for user
	 *
	 * @since 1.6093.1200
	 * @param  int $user_id User ID.
	 * @return array Next recommended content.
	 */
	private function get_next_recommended_content( $user_id ) {
		$learning_path = $this->get_learning_path( $user_id );

		if ( ! empty( $learning_path ) ) {
			return $learning_path[0]; // Return highest priority.
		}

		// Fallback: Recommend beginner course.
		return array(
			'course' => array(
				'id'    => 'wordpress-basics',
				'title' => __( 'WordPress Basics: Build a Strong Foundation', 'wpshadow' ),
				'url'   => 'https://wpshadow.com/academy/courses/wordpress-basics/',
			),
			'reason' => __( 'Start with the fundamentals', 'wpshadow' ),
		);
	}

	/**
	 * Update learning path progress
	 *
	 * @since 1.6093.1200
	 * @param  int    $user_id User ID.
	 * @param  string $course_id Completed course ID.
	 * @return void
	 */
	private function update_learning_path_progress( $user_id, $course_id ) {
		// Remove from in-progress.
		$in_progress = get_user_meta( $user_id, 'wpshadow_academy_courses_in_progress', true );
		if ( is_array( $in_progress ) ) {
			$in_progress = array_filter(
				$in_progress,
				function ( $id ) use ( $course_id ) {
					return $id !== $course_id;
				}
			);
			update_user_meta( $user_id, 'wpshadow_academy_courses_in_progress', $in_progress );
		}

		// Get next course in path.
		$learning_path = $this->get_learning_path( $user_id );
		if ( ! empty( $learning_path ) ) {
			$next_course = $learning_path[0]['course'];

			// Add to in-progress.
			$in_progress   = get_user_meta( $user_id, 'wpshadow_academy_courses_in_progress', true );
			$in_progress   = is_array( $in_progress ) ? $in_progress : array();
			$in_progress[] = $next_course['id'];
			update_user_meta( $user_id, 'wpshadow_academy_courses_in_progress', $in_progress );
		}
	}
}

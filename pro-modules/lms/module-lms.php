<?php
/**
 * LMS custom post type and block.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\LMS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the LMS post type, taxonomy, meta, and block.
 */
class LMS_Post_Type {
	/**
	 * Bootstrap hooks.
	 * Called from init hook, so we call methods directly instead of registering more hooks.
	 */
	public static function init(): void {
		self::register_post_type();
		self::register_taxonomy();
		self::register_meta();
		self::register_block();
		self::enqueue_styles();
	}

	/**
	 * Enqueue frontend styles for the LMS block.
	 */
	public static function enqueue_styles(): void {
		add_action( 'wp_enqueue_scripts', function() {
			$plugin_dir = plugin_dir_path( __FILE__ );
			$plugin_url = plugin_dir_url( __FILE__ );
			$css_path = $plugin_dir . 'assets/lms-course.css';
			$css_url  = $plugin_url . 'assets/lms-course.css';

			if ( file_exists( $css_path ) ) {
				wp_enqueue_style(
					'wpshadow-lms-course',
					$css_url,
					[],
					filemtime( $css_path )
				);
			}
		} );
	}

	/**
	 * Register the LMS custom post type.
	 */
	public static function register_post_type(): void {
		$labels = [
			'name'               => __( 'Courses', 'wpshadow' ),
			'singular_name'      => __( 'Course', 'wpshadow' ),
			'menu_name'          => __( 'Courses', 'wpshadow' ),
			'add_new'            => __( 'Add New', 'wpshadow' ),
			'add_new_item'       => __( 'Add New Course', 'wpshadow' ),
			'edit_item'          => __( 'Edit Course', 'wpshadow' ),
			'new_item'           => __( 'New Course', 'wpshadow' ),
			'view_item'          => __( 'View Course', 'wpshadow' ),
			'search_items'       => __( 'Search Courses', 'wpshadow' ),
			'not_found'          => __( 'No courses found', 'wpshadow' ),
			'not_found_in_trash' => __( 'No courses found in trash', 'wpshadow' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'course' ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 24,
			'menu_icon'          => 'dashicons-welcome-learn-more',
			'show_in_rest'       => true,
			'supports'           => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ],
		];

		register_post_type( 'wpshadow_course', $args );
	}

	/**
	 * Register LMS taxonomy for course categories.
	 */
	public static function register_taxonomy(): void {
		$labels = [
			'name'          => __( 'Course Categories', 'wpshadow' ),
			'singular_name' => __( 'Course Category', 'wpshadow' ),
			'search_items'  => __( 'Search Course Categories', 'wpshadow' ),
			'all_items'     => __( 'All Course Categories', 'wpshadow' ),
			'edit_item'     => __( 'Edit Course Category', 'wpshadow' ),
			'update_item'   => __( 'Update Course Category', 'wpshadow' ),
			'add_new_item'  => __( 'Add New Course Category', 'wpshadow' ),
			'new_item_name' => __( 'New Course Category Name', 'wpshadow' ),
			'menu_name'     => __( 'Course Categories', 'wpshadow' ),
		];

		register_taxonomy(
			'course_category',
			[ 'wpshadow_course' ],
			[
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'show_in_rest'      => true,
				'rewrite'           => [ 'slug' => 'course-category' ],
			]
		);
	}

	/**
	 * Register meta fields for courses.
	 */
	public static function register_meta(): void {
		$meta = [
			'wpshadow_course_duration'     => 'string',
			'wpshadow_course_level'        => 'string',
			'wpshadow_course_instructor'   => 'string',
			'wpshadow_course_price'        => 'number',
		];

		foreach ( $meta as $key => $type ) {
			register_post_meta(
				'wpshadow_course',
				$key,
				[
					'single'            => true,
					'type'              => $type,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				]
			);
		}
	}

	/**
	 * Register the LMS block and assets.
	 */
	public static function register_block(): void {
		$script_handle = 'wpshadow-lms-block';
		
		// Module is in pro-modules/lms/, script is in pro-modules/lms/assets/lms-block.js
		$plugin_dir = plugin_dir_path( __FILE__ );
		$plugin_url = plugin_dir_url( __FILE__ );
		
		$script_path = $plugin_dir . 'assets/lms-block.js';
		$script_url  = $plugin_url . 'assets/lms-block.js';

		if ( file_exists( $script_path ) ) {
			wp_register_script(
				$script_handle,
				$script_url,
				[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-editor', 'wp-data', 'wp-server-side-render' ],
				'1.0.0',
				true
			);
		}

		register_block_type(
			'wpshadow/course-list',
			[
				'attributes'      => [
					'ids'         => [ 'type' => 'string', 'default' => '' ],
					'category'    => [ 'type' => 'string', 'default' => '' ],
					'showExcerpt' => [ 'type' => 'boolean', 'default' => true ],
				],
				'editor_script'   => $script_handle,
				'render_callback' => [ 'WPShadow\\LMS\\LMS_Post_Type', 'render_block' ],
			]
		);
	}

	/**
	 * Render callback for the Sensei course block.
	 *
	 * @param array $atts Block attributes.
	 * @return string
	 */
	public static function render_block( array $atts ): string {
		// Check if Sensei is active
		if ( ! class_exists( 'Sensei_Main' ) ) {
			return '<p>' . esc_html__( 'Sensei LMS is required for this block.', 'wpshadow' ) . '</p>';
		}

		$ids_raw = isset( $atts['ids'] ) ? $atts['ids'] : '';
		$ids = array_filter( array_map( 'absint', explode( ',', (string) $ids_raw ) ) );

		if ( empty( $ids ) ) {
			return '<p>' . esc_html__( 'Please enter a course ID.', 'wpshadow' ) . '</p>';
		}

		$html = '';
		$user_id = get_current_user_id();

		foreach ( $ids as $course_id ) {
			$course = get_post( $course_id );
			
			if ( ! $course || 'course' !== $course->post_type ) {
				continue;
			}

			// Get course title
			$course_title = get_the_title( $course_id );
			
			// Get modules for this course
			$modules = Sensei()->modules->get_course_modules( $course_id );
			
			// Get all lessons for this course
			$course_lessons = Sensei()->course->course_lessons( $course_id );
			
			// Check if user is logged in and enrolled
			$is_enrolled = $user_id && \Sensei_Course::is_user_enrolled( $course_id, $user_id );

			$html .= '<div class="wpshadow-sensei-course" data-course-id="' . esc_attr( $course_id ) . '">';
			
			// Course Title
			$html .= '<h2 class="course-title">' . esc_html( $course_title ) . '</h2>';

			// If user is not logged in, show registration CTA
			if ( ! $user_id ) {
				$html .= '<div class="course-cta">';
				$html .= '<p>' . esc_html__( 'Register for free to access this course and track your progress.', 'wpshadow' ) . '</p>';
				$html .= '<a href="' . esc_url( wp_registration_url() ) . '" class="button button-primary">' . esc_html__( 'Register for Free', 'wpshadow' ) . '</a>';
				$html .= '</div>';
			}

			// Display modules and lessons
			if ( ! empty( $modules ) ) {
				// Organize lessons by module
				foreach ( $modules as $module ) {
					$module_lessons = Sensei()->modules->get_lessons( $course_id, $module->term_id );
					
					$html .= '<div class="course-module">';
					$html .= '<h3 class="module-title">' . esc_html( $module->name ) . '</h3>';
					
					if ( ! empty( $module_lessons ) ) {
						$html .= '<ul class="module-lessons">';
						
						foreach ( $module_lessons as $lesson ) {
							$lesson_completed = false;
							
							if ( $is_enrolled ) {
								$lesson_completed = \Sensei_Utils::user_completed_lesson( $lesson->ID, $user_id );
							}
							
							$lesson_class = $lesson_completed ? 'lesson-completed' : 'lesson-incomplete';
							
							$html .= '<li class="lesson-item ' . esc_attr( $lesson_class ) . '">';
							
							if ( $is_enrolled && $lesson_completed ) {
								$html .= '<span class="lesson-checkmark">✓</span> ';
							}
							
							$html .= '<a href="' . esc_url( get_permalink( $lesson->ID ) ) . '">' . esc_html( $lesson->post_title ) . '</a>';
							$html .= '</li>';
						}
						
						$html .= '</ul>';
					}
					
					$html .= '</div>';
				}
			} else {
				// No modules - show lessons directly
				if ( ! empty( $course_lessons ) ) {
					$html .= '<div class="course-lessons">';
					$html .= '<h3>' . esc_html__( 'Lessons', 'wpshadow' ) . '</h3>';
					$html .= '<ul class="lesson-list">';
					
					foreach ( $course_lessons as $lesson ) {
						$lesson_completed = false;
						
						if ( $is_enrolled ) {
							$lesson_completed = \Sensei_Utils::user_completed_lesson( $lesson->ID, $user_id );
						}
						
						$lesson_class = $lesson_completed ? 'lesson-completed' : 'lesson-incomplete';
						
						$html .= '<li class="lesson-item ' . esc_attr( $lesson_class ) . '">';
						
						if ( $is_enrolled && $lesson_completed ) {
							$html .= '<span class="lesson-checkmark">✓</span> ';
						}
						
						$html .= '<a href="' . esc_url( get_permalink( $lesson->ID ) ) . '">' . esc_html( $lesson->post_title ) . '</a>';
						$html .= '</li>';
					}
					
					$html .= '</ul>';
					$html .= '</div>';
				}
			}

			// Show progress if enrolled
			if ( $is_enrolled && ! empty( $course_lessons ) ) {
				$completed_lessons = 0;
				foreach ( $course_lessons as $lesson ) {
					if ( \Sensei_Utils::user_completed_lesson( $lesson->ID, $user_id ) ) {
						$completed_lessons++;
					}
				}
				
				$total_lessons = count( $course_lessons );
				$progress_percent = $total_lessons > 0 ? round( ( $completed_lessons / $total_lessons ) * 100 ) : 0;
				
				$html .= '<div class="course-progress">';
				$html .= '<div class="progress-text">';
				$html .= sprintf(
					esc_html__( 'Progress: %d of %d lessons completed (%d%%)', 'wpshadow' ),
					$completed_lessons,
					$total_lessons,
					$progress_percent
				);
				$html .= '</div>';
				$html .= '<div class="progress-bar-container">';
				$html .= '<div class="progress-bar" style="width: ' . esc_attr( $progress_percent ) . '%"></div>';
				$html .= '</div>';
				$html .= '</div>';
			}

			$html .= '</div>';
		}

		return $html;
	}
}

// Don't auto-initialize - let Module::init() handle it

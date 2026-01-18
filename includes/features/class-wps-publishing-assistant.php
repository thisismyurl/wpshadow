<?php
/**
 * Publishing Assistant - Content Review Framework
 *
 * Provides a framework for features to integrate with the post editor
 * and offer pre-publish content review and validation.
 *
 * Multiple features can register reviewers that will be shown to users
 * as a publishing assistant when they attempt to publish/update content.
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Publishing_Assistant {
	/**
	 * Registered content reviewers.
	 *
	 * @var array
	 */
	private static array $reviewers = array();

	/**
	 * Initialize publishing assistant.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Enqueue editor assets
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_classic_editor_assets' ) );

		// Handle AJAX review request
		add_action( 'wp_ajax_wpshadow_run_content_review', array( __CLASS__, 'ajax_run_content_review' ) );

		// Handle AJAX publish callback
		add_action( 'wp_ajax_wpshadow_content_review_callback', array( __CLASS__, 'ajax_content_review_callback' ) );
	}

	/**
	 * Register a content reviewer.
	 *
	 * A reviewer is a feature-specific reviewer that can run checks on post content.
	 *
	 * @param string $reviewer_id Unique reviewer identifier (e.g., 'broken-link-checker').
	 * @param array  $config      Reviewer configuration.
	 *                            - name (string) Display name for the reviewer
	 *                            - description (string) Description of what it checks
	 *                            - priority (int) Display priority (lower = higher priority)
	 *                            - icon (string) Dashicon class
	 *                            - callback (callable) Function to execute review
	 *                            - post_types (array) Post types to show reviewer for
	 *
	 * @return bool True if registered successfully.
	 */
	public static function register_reviewer( string $reviewer_id, array $config ): bool {
		if ( empty( $reviewer_id ) || empty( $config['callback'] ) || ! is_callable( $config['callback'] ) ) {
			return false;
		}

		$reviewer = array_merge(
			array(
				'id'          => $reviewer_id,
				'name'        => $config['name'] ?? $reviewer_id,
				'description' => $config['description'] ?? '',
				'priority'    => $config['priority'] ?? 10,
				'icon'        => $config['icon'] ?? 'dashicons-yes-alt',
				'post_types'  => $config['post_types'] ?? array( 'post', 'page' ),
				'callback'    => $config['callback'],
			)
		);

		self::$reviewers[ $reviewer_id ] = $reviewer;
		return true;
	}

	/**
	 * Get registered reviewers for a post type.
	 *
	 * @param string $post_type Post type to get reviewers for.
	 * @return array Array of registered reviewers for the post type.
	 */
	public static function get_reviewers_for_post_type( string $post_type ): array {
		$reviewers = array_filter(
			self::$reviewers,
			function ( $reviewer ) use ( $post_type ) {
				return in_array( $post_type, (array) $reviewer['post_types'], true );
			}
		);

		// Sort by priority
		uasort(
			$reviewers,
			function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			}
		);

		return $reviewers;
	}

	/**
	 * Get all registered reviewers.
	 *
	 * @return array All registered reviewers.
	 */
	public static function get_all_reviewers(): array {
		uasort(
			self::$reviewers,
			function ( $a, $b ) {
				return $a['priority'] <=> $b['priority'];
			}
		);
		return self::$reviewers;
	}

	/**
	 * Check if publishing assistant is enabled.
	 *
	 * @return bool True if enabled.
	 */
	public static function is_enabled(): bool {
		return get_option( 'wpshadow_publishing_assistant_enabled', true );
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public static function enqueue_editor_assets(): void {
		if ( ! self::is_enabled() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		// Enqueue JavaScript plugin for publishing assistant
		wp_enqueue_script(
			'wpshadow-publishing-assistant',
			WPSHADOW_URL . 'assets/js/publishing-assistant.js',
			array( 'wp-plugins', 'wp-edit-post', 'wp-api-fetch', 'wp-element', 'wp-components' ),
			WPSHADOW_VERSION,
			true
		);

		// Pass reviewer data to JavaScript
		$post_type = $screen->post_type ?? 'post';
		$reviewers = self::get_reviewers_for_post_type( $post_type );

		$reviewer_data = array();
		foreach ( $reviewers as $reviewer ) {
			$reviewer_data[] = array(
				'id'          => $reviewer['id'],
				'name'        => $reviewer['name'],
				'description' => $reviewer['description'],
				'icon'        => $reviewer['icon'],
			);
		}

		wp_localize_script(
			'wpshadow-publishing-assistant',
			'wpsPublishingAssistant',
			array(
				'nonce'    => wp_create_nonce( 'wpshadow_publishing_assistant' ),
				'reviewers' => $reviewer_data,
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			)
		);

		// Enqueue styles
		wp_enqueue_style(
			'wpshadow-publishing-assistant',
			WPSHADOW_URL . 'assets/css/publishing-assistant.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Enqueue classic editor assets.
	 *
	 * @return void
	 */
	public static function enqueue_classic_editor_assets(): void {
		if ( ! self::is_enabled() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base || ! in_array( $screen->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		// Enqueue JavaScript for classic editor
		wp_enqueue_script(
			'wpshadow-publishing-assistant-classic',
			WPSHADOW_URL . 'assets/js/publishing-assistant-classic.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-publishing-assistant-classic',
			'wpsPublishingAssistantClassic',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_publishing_assistant' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'reviewers' => self::get_reviewers_for_post_type( $screen->post_type ?? 'post' ),
			)
		);
	}

	/**
	 * AJAX handler to run content review.
	 *
	 * @return void
	 */
	public static function ajax_run_content_review(): void {
		check_ajax_referer( 'wpshadow_publishing_assistant', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$post    = get_post( $post_id );

		if ( ! $post ) {
			wp_send_json_error( array( 'message' => __( 'Post not found.', 'wpshadow' ) ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You cannot edit this post.', 'wpshadow' ) ) );
		}

		$reviews = self::run_all_reviews( $post );

		wp_send_json_success(
			array(
				'reviews' => $reviews,
			)
		);
	}

	/**
	 * Run all registered content reviews for a post.
	 *
	 * @param \WP_Post $post Post object.
	 * @return array Array of review results.
	 */
	public static function run_all_reviews( \WP_Post $post ): array {
		$reviewers = self::get_reviewers_for_post_type( $post->post_type );
		$reviews   = array();

		foreach ( $reviewers as $reviewer ) {
			$result = call_user_func( $reviewer['callback'], $post );

			if ( is_array( $result ) ) {
				$reviews[ $reviewer['id'] ] = array_merge(
					array(
						'id'    => $reviewer['id'],
						'name'  => $reviewer['name'],
						'icon'  => $reviewer['icon'],
					),
					$result
				);
			}
		}

		return $reviews;
	}

	/**
	 * AJAX handler for publishing assistant callback.
	 *
	 * Used by features to log when user takes action on review results.
	 *
	 * @return void
	 */
	public static function ajax_content_review_callback(): void {
		check_ajax_referer( 'wpshadow_publishing_assistant', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$action    = isset( $_POST['action_type'] ) ? sanitize_key( wp_unslash( $_POST['action_type'] ) ) : '';
		$reviewer  = isset( $_POST['reviewer_id'] ) ? sanitize_key( wp_unslash( $_POST['reviewer_id'] ) ) : '';
		$post_id   = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

		/**
		 * Allow features to handle publishing assistant callbacks.
		 *
		 * @param string $action_type Type of action (e.g., 'dismiss', 'review', 'fix').
		 * @param string $reviewer_id Reviewer ID.
		 * @param int    $post_id     Post ID.
		 */
		do_action( 'wpshadow_publishing_assistant_callback', $action, $reviewer, $post_id );

		wp_send_json_success();
	}

	/**
	 * Get review results for display.
	 *
	 * Formats review results for output in the publishing assistant UI.
	 *
	 * @param array $review Review result from a reviewer callback.
	 * @return array Formatted review result.
	 */
	public static function format_review_result( array $review ): array {
		return array_merge(
			array(
				'status'     => 'info', // 'success', 'warning', 'error', 'info'
				'items'      => array(),
				'count'      => 0,
				'message'    => '',
				'action_url' => '',
				'action_text' => '',
			),
			$review
		);
	}
}

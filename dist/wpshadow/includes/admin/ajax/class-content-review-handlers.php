<?php
/**
 * Content Review AJAX Handlers
 *
 * Handles all AJAX requests for the content review wizard including
 * fetching diagnostics, running AI improvements, and saving preferences.
 *
 * @package    WPShadow
 * @subpackage Admin/AJAX
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\AJAX;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Features\ContentReview\Content_Review_Manager;
use WPShadow\Integration\Cloud\Cloud_Service_Connector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Content Review Data Handler
 *
 * Fetches diagnostics and metadata for content review wizard.
 *
 * @since 1.6093.1200
 */
class Content_Review_Get_Data_Handler extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_content_review', 'edit_posts' );

		$post_id = self::get_post_param( 'post_id', 'int', 0, true );

		// Verify user can edit this post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			self::send_error( __( 'You do not have permission to review this post.', 'wpshadow' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			self::send_error( __( 'Post not found.', 'wpshadow' ) );
		}

		// Get diagnostics.
		$diagnostics = Content_Review_Manager::get_content_diagnostics( $post_id );
		$preferences  = Content_Review_Manager::get_user_preferences();

		// Filter out skipped diagnostics.
		$visible_diagnostics = array();
		foreach ( $diagnostics as $family => $family_diagnostics ) {
			$visible_diagnostics[ $family ] = array_filter(
				$family_diagnostics,
				function( $diagnostic ) use ( $preferences ) {
					return ! in_array(
						$diagnostic['slug'],
						$preferences['skip_diagnostics'] ?? array(),
						true
					);
				}
			);
		}

		// Get related KB articles and training.
		$diagnostic_slugs = array();
		foreach ( $visible_diagnostics as $family_diagnostics ) {
			foreach ( $family_diagnostics as $diagnostic ) {
				$diagnostic_slugs[] = $diagnostic['slug'];
			}
		}

		$kb_articles = Content_Review_Manager::get_related_kb_articles( $diagnostic_slugs );
		$families     = array_keys( $visible_diagnostics );
		$training     = Content_Review_Manager::get_related_training( $families );

		self::send_success(
			array(
				'post'         => array(
					'id'    => $post->ID,
					'title' => $post->post_title,
					'url'   => get_permalink( $post ),
				),
				'diagnostics'  => $visible_diagnostics,
				'kb_articles'  => $kb_articles,
				'training'     => $training,
				'preferences'  => $preferences,
				'cloud_status' => array(
					'is_registered' => Cloud_Service_Connector::is_registered(),
				),
			)
		);
	}
}

/**
 * Hide Tip Handler
 *
 * Marks a tip as hidden for the current user.
 *
 * @since 1.6093.1200
 */
class Content_Review_Hide_Tip_Handler extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_content_review', 'manage_options' );

		$tip_id = self::get_post_param( 'tip_id', 'text', '', true );

		if ( Content_Review_Manager::hide_tip( $tip_id ) ) {
			self::send_success( array( 'message' => __( 'Tip hidden successfully.', 'wpshadow' ) ) );
		} else {
			self::send_error( __( 'Failed to hide tip.', 'wpshadow' ) );
		}
	}
}

/**
 * Skip Diagnostic Handler
 *
 * Marks a diagnostic as skipped for the current user.
 *
 * @since 1.6093.1200
 */
class Content_Review_Skip_Diagnostic_Handler extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_content_review', 'manage_options' );

		$diagnostic_slug = self::get_post_param( 'diagnostic_slug', 'text', '', true );

		if ( Content_Review_Manager::skip_diagnostic( $diagnostic_slug ) ) {
			self::send_success(
				array(
					'message' => sprintf(
						/* translators: %s: diagnostic slug */
						__( 'Diagnostic "%s" will be skipped in future reviews.', 'wpshadow' ),
						$diagnostic_slug
					),
				)
			);
		} else {
			self::send_error( __( 'Failed to skip diagnostic.', 'wpshadow' ) );
		}
	}
}

/**
 * AI Content Improvement Handler
 *
 * Sends content to cloud service for AI improvement suggestions.
 *
 * @since 1.6093.1200
 */
class Content_Review_AI_Improvement_Handler extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_content_review', 'edit_posts' );

		// Check cloud registration.
		if ( ! Cloud_Service_Connector::is_registered() ) {
			self::send_error( __( 'Cloud service not registered. Please connect to WPShadow Cloud first.', 'wpshadow' ) );
		}

		$post_id = self::get_post_param( 'post_id', 'int', 0, true );
		$aspect  = self::get_post_param( 'aspect', 'text', '', true );

		// Verify user can edit this post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			self::send_error( __( 'You do not have permission to review this post.', 'wpshadow' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			self::send_error( __( 'Post not found.', 'wpshadow' ) );
		}

		// Get the content to improve.
		$content = array(
			'title'   => $post->post_title,
			'excerpt' => $post->post_excerpt,
			'content' => $post->post_content,
		);

		// Call cloud service for AI improvement.
		$improvements = self::get_ai_improvements( $aspect, $content, $post_id );

		if ( is_wp_error( $improvements ) ) {
			self::send_error( $improvements->get_error_message() );
		} else {
			self::send_success(
				array(
					'improvements' => $improvements,
					'aspect'       => $aspect,
				)
			);
		}
	}

	/**
	 * Get AI improvements from cloud service
	 *
	 * @since 1.6093.1200
	 * @param  string $aspect   Aspect to improve (seo, readability, accessibility, etc).
	 * @param  array  $content  Content data (title, excerpt, content).
	 * @param  int    $post_id  Post ID.
	 * @return array|\WP_Error Improvements array or error.
	 */
	private static function get_ai_improvements( string $aspect, array $content, int $post_id ) {
		$api_key = Cloud_Service_Connector::get_api_key();
		if ( ! $api_key ) {
			return new \WP_Error(
				'cloud_not_registered',
				__( 'Cloud service not registered.', 'wpshadow' )
			);
		}

		$api_url = 'https://cloud.wpshadow.com/api/v1/improve-content';

		$response = wp_remote_post(
			$api_url,
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'aspect'  => $aspect,
						'content' => $content,
						'post_id' => $post_id,
						'site'    => get_site_url(),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['success'] ) ) {
			return new \WP_Error(
				'cloud_error',
				$body['message'] ?? __( 'Cloud service returned an error.', 'wpshadow' )
			);
		}

		return $body['improvements'] ?? array();
	}
}

/**
 * Generate Content Report Handler
 *
 * Generates a comprehensive report for a post's content quality.
 *
 * @since 1.6093.1200
 */
class Content_Review_Generate_Report_Handler extends AJAX_Handler_Base {

	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_content_review', 'edit_posts' );

		$post_id = self::get_post_param( 'post_id', 'int', 0, true );

		// Verify user can edit this post.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			self::send_error( __( 'You do not have permission to generate report for this post.', 'wpshadow' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			self::send_error( __( 'Post not found.', 'wpshadow' ) );
		}

		// Get all diagnostics (not filtered by user preferences).
		$diagnostics = Content_Review_Manager::get_content_diagnostics( $post_id );

		// Count issues by severity.
		$severity_counts = array(
			'critical' => 0,
			'high'     => 0,
			'medium'   => 0,
			'low'      => 0,
		);

		$total_issues = 0;
		foreach ( $diagnostics as $family_diagnostics ) {
			foreach ( $family_diagnostics as $diagnostic ) {
				$severity = $diagnostic['severity'] ?? 'medium';
				if ( isset( $severity_counts[ $severity ] ) ) {
					$severity_counts[ $severity ]++;
				}
				$total_issues++;
			}
		}

		$report = array(
			'post'             => array(
				'id'      => $post->ID,
				'title'   => $post->post_title,
				'url'     => get_permalink( $post ),
				'status'  => $post->post_status,
				'date'    => $post->post_date,
			),
			'generated_at'     => current_time( 'mysql' ),
			'total_issues'     => $total_issues,
			'severity_counts'  => $severity_counts,
			'diagnostics'      => $diagnostics,
		);

		// Log report generation to activity.
		do_action( 'wpshadow_content_report_generated', $post_id, $report );

		self::send_success( array( 'report' => $report ) );
	}
}

// Register AJAX handlers.
add_action( 'wp_ajax_wpshadow_content_review_get_data', array( 'WPShadow\Admin\AJAX\Content_Review_Get_Data_Handler', 'handle' ) );
add_action( 'wp_ajax_wpshadow_content_review_hide_tip', array( 'WPShadow\Admin\AJAX\Content_Review_Hide_Tip_Handler', 'handle' ) );
add_action( 'wp_ajax_wpshadow_content_review_skip_diagnostic', array( 'WPShadow\Admin\AJAX\Content_Review_Skip_Diagnostic_Handler', 'handle' ) );
add_action( 'wp_ajax_wpshadow_content_review_ai_improvement', array( 'WPShadow\Admin\AJAX\Content_Review_AI_Improvement_Handler', 'handle' ) );
add_action( 'wp_ajax_wpshadow_content_review_generate_report', array( 'WPShadow\Admin\AJAX\Content_Review_Generate_Report_Handler', 'handle' ) );

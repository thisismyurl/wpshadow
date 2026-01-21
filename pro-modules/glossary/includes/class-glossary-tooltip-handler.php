<?php
/**
 * Glossary Tooltip Handler
 *
 * Handles AJAX requests for glossary term tooltips.
 *
 * @package WPShadow
 * @subpackage Glossary
 */

declare(strict_types=1);

namespace WPShadow\Glossary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Glossary_Tooltip_Handler class.
 */
class Glossary_Tooltip_Handler {
	/**
	 * Register AJAX handlers.
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_glossary_tooltip', [ __CLASS__, 'get_tooltip' ] );
		add_action( 'wp_ajax_nopriv_wpshadow_get_glossary_tooltip', [ __CLASS__, 'get_tooltip' ] );
	}

	/**
	 * Get glossary term tooltip.
	 */
	public static function get_tooltip(): void {
		check_ajax_referer( 'wpshadow_glossary_nonce' );

		if ( ! isset( $_POST['term'] ) ) {
			wp_send_json_error( __( 'Missing term parameter', 'wpshadow' ) );
		}

		$term = sanitize_text_field( wp_unslash( $_POST['term'] ) );

		// Search for glossary term
		$args = [
			'post_type'      => 'wpshadow_glossary',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			's'              => $term,
		];

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			wp_send_json_error( __( 'Term not found', 'wpshadow' ) );
		}

		$query->the_post();
		$post_id = get_the_ID();

		$response = [
			'title'   => get_the_title(),
			'excerpt' => wp_strip_all_tags( get_the_excerpt() ),
			'url'     => get_the_permalink(),
			'post_id' => $post_id,
		];

		wp_reset_postdata();

		wp_send_json_success( $response );
	}
}

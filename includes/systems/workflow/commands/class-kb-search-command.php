<?php
/**
 * KB Search AJAX Command
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\KnowledgeBase\KB_Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KB Search Command
 */
class KB_Search_Command extends Command {
	/**
	 * Get command name.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'kb_search';
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function execute() {
		// KB search can be accessed without capability check (public-facing)
		// But verify nonce
		$nonce = $this->get_post_var( 'nonce', '' );
		if ( ! $nonce || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'wpshadow_workflow_nonce' ) ) {
			$this->error( __( 'Security check failed.', 'wpshadow' ) );
			return;
		}

		$query = $this->get_post_var( 'query', '' );

		if ( empty( $query ) ) {
			$this->error( __( 'Search query is required.', 'wpshadow' ) );
			return;
		}

		// Parse filters
		$filters  = array();
		$category = $this->get_post_var( 'category', '' );
		if ( ! empty( $category ) ) {
			$filters['category'] = sanitize_key( $category );
		}

		$type = $this->get_post_var( 'type', '' );
		if ( ! empty( $type ) ) {
			$filters['type'] = sanitize_key( $type );
		}

		// Perform search
		$results = KB_Search::search( $query, $filters, 10 );

		// Track search
		KB_Search::track_search( $query );

		$this->success(
			array(
				'results' => $results,
				'count'   => count( $results ),
			)
		);
	}
}

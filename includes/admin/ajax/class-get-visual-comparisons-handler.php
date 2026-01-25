<?php
/**
 * Get Visual Comparisons AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Visual_Comparator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handler for retrieving visual comparison records
 */
class Get_Visual_Comparisons_Handler extends AJAX_Handler_Base {
	/**
	 * Register the AJAX handler
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_visual_comparisons', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the AJAX request
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_visual_comparisons', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', null );
		$limit      = self::get_post_param( 'limit', 'int', 50 );
		$offset     = self::get_post_param( 'offset', 'int', 0 );

		$args = array(
			'limit'  => min( $limit, 100 ), // Cap at 100
			'offset' => max( $offset, 0 ),
		);

		if ( $finding_id ) {
			$args['finding_id'] = $finding_id;
		}

		$comparisons = Visual_Comparator::get_comparisons( $args );
		$statistics  = Visual_Comparator::get_statistics();

		self::send_success(
			array(
				'comparisons' => $comparisons,
				'statistics'  => $statistics,
			)
		);
	}
}

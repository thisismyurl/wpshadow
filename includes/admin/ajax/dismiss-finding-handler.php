<?php

/**
 * Dismiss Finding AJAX Handler
 *
 * @package ThisIsMyURL\Shadow
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Admin\Ajax;

use ThisIsMyURL\Shadow\Core\AJAX_Handler_Base;
use ThisIsMyURL\Shadow\Core\Options_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dismiss_Finding_Handler extends AJAX_Handler_Base {

	/**
	 * Handle finding dismissal requests.
	 *
	 * @since 0.6095
	 * @return void Sends JSON response and exits.
	 */
	public static function handle(): void {
		self::verify_request( 'thisismyurl_shadow_dismiss_finding', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '', true );
		if ( empty( $finding_id ) ) {
			self::send_error( __( 'Invalid finding ID', 'thisismyurl-shadow' ) );
		}

		$dismissed                = Options_Manager::get_array( 'thisismyurl_shadow_dismissed_findings', array() );
		$dismissed[ $finding_id ] = current_time( 'timestamp' );
		update_option( 'thisismyurl_shadow_dismissed_findings', $dismissed );

		// Log activity (Issue #565)
		\ThisIsMyURL\Shadow\Core\Activity_Logger::log( 'finding_dismissed', "Finding dismissed: {$finding_id}", '', array( 'finding_id' => $finding_id ) );

		self::send_success( array( 'message' => __( 'Finding dismissed', 'thisismyurl-shadow' ) ) );
	}
}

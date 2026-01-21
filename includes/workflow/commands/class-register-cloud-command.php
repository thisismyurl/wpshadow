<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Cloud\Registration_Manager;
use WPShadow\Core\Command_Base;

/**
 * Register Site Command
 * 
 * AJAX endpoint: wp_ajax_wpshadow_register_cloud
 * 
 * Handles user registration with cloud service.
 * Endpoint is admin-only and requires nonce verification.
 */
class Register_Cloud_Command extends Command_Base {
	/**
	 * Use dedicated nonce for registration.
	 *
	 * @var string
	 */
	protected static $nonce_action = 'wpshadow_register_nonce';

	/**
	 * Get command name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'register_cloud';
	}

	/**
	 * Handle registration request.
	 *
	 * @return array Result payload.
	 */
	protected function execute(): array {
		$email = sanitize_email( $this->get_param( 'email' ) );
		if ( empty( $email ) ) {
			$email = get_option( 'admin_email' );
		}

		$result = Registration_Manager::register_user( $email );

		if ( isset( $result['error'] ) ) {
			return $this->error( $result['error'] );
		}

		return $this->success( [
			'message'             => $result['message'] ?? __( 'Registration successful', 'wpshadow' ),
			'cloud_dashboard_url' => $result['cloud_dashboard_url'] ?? '',
			'site_id'             => $result['site_id'] ?? '',
		] );
	}
}

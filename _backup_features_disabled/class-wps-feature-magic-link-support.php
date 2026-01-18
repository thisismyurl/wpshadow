<?php
/**
 * Feature: Magic Link Support Access
 *
 * Secure time-limited login URLs for developers.
 * Provides secure, time-limited (24-hour) login URLs for developers with
 * session tracking and email summaries of changes made during the session.
 *
 * @package WPShadow\Features
 * @since 1.2601.76000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * Magic Link Support Feature
 *
 * Manages secure temporary access for developer support.
 */
final class WPSHADOW_Feature_Magic_Link_Support extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'magic-link-support',
				'name'               => __( 'Magic Link Support Access', 'wpshadow' ),
				'description'        => __( 'Generate secure, time-limited login URLs for developer support with session tracking.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'maintenance-tools',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-admin-network',
				'category'           => 'security',
				'priority'           => 30,
				'sub_features'       => array(
					'log_sessions'        => __( 'Log All Magic Link Sessions', 'wpshadow' ),
					'email_notifications' => __( 'Send Email Notifications', 'wpshadow' ),
					'role_restriction'    => __( 'Restrict to Specific Roles', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'wp_loaded', array( $this, 'initialize' ) );
	}

	/**
	 * Initialize the feature.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Delegate to the original class for backward compatibility.
		WPSHADOW_Magic_Link_Support::init();

		$this->log_activity( 'feature_initialized', 'Magic Link Support feature initialized', 'info' );
	}

	/**
	 * Create a new magic link for developer support.
	 *
	 * @param string $developer_name Developer name.
	 * @param string $developer_email Developer email.
	 * @param string $owner_email Site owner email.
	 * @param string $reason Reason for access.
	 * @return array{success: bool, link?: string, token?: string, error?: string} Result array.
	 */
	public function create_magic_link(
		string $developer_name,
		string $developer_email,
		string $owner_email,
		string $reason = ''
	): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array(
				'success' => false,
				'error'   => __( 'You don\'t have permission to do that', 'wpshadow' ),
			);
		}

		// Delegate to original class.
		$result = WPSHADOW_Magic_Link_Support::create_magic_link(
			$developer_name,
			$developer_email,
			$owner_email,
			$reason
		);

		if ( $result['success'] ) {
			$this->log_activity(
				'magic_link_created',
				sprintf(
					/* translators: %s: developer name */
					__( 'Magic link created for %s', 'wpshadow' ),
					$developer_name
				),
				'security'
			);
		}

		return $result;
	}

	/**
	 * Revoke a magic link.
	 *
	 * @param string $token Magic link token.
	 * @return array{success: bool, error?: string} Result array.
	 */
	public function revoke_magic_link( string $token ): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array(
				'success' => false,
				'error'   => __( 'You don\'t have permission to do that', 'wpshadow' ),
			);
		}

		// Delegate to original class.
		$result = WPSHADOW_Magic_Link_Support::revoke_magic_link( $token );

		if ( $result['success'] ) {
			$this->log_activity(
				'magic_link_revoked',
				'Magic link revoked',
				'security'
			);
		}

		return $result;
	}

	/**
	 * Get active magic links.
	 *
	 * @return array List of active magic links.
	 */
	public function get_active_links(): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array();
		}

		return WPSHADOW_Magic_Link_Support::get_active_links();
	}

	/**
	 * Get active sessions.
	 *
	 * @return array List of active sessions.
	 */
	public function get_active_sessions(): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array();
		}

		return WPSHADOW_Magic_Link_Support::get_active_sessions();
	}
}

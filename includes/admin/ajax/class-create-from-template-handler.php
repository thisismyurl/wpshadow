<?php
declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Workflow\Workflow_Templates;

/**
 * AJAX Handler: Create From Template
 *
 * Creates a workflow from a template.
 * Action: wp_ajax_wpshadow_create_from_template
 * Nonce: wpshadow_workflow
 * Capability: manage_options
 */
class Create_From_Template_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_create_from_template', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle AJAX request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_workflow', 'manage_options' );

		$template_slug = self::get_post_param( 'template_slug', 'key', '', true );
		$custom_name   = self::get_post_param( 'custom_name', 'text', '', false );

		if ( empty( $template_slug ) ) {
			self::send_error( __( 'Invalid template slug.', 'wpshadow' ) );
			return;
		}

		$result = Workflow_Templates::create_from_template( $template_slug, $custom_name );

		if ( isset( $result['error'] ) ) {
			self::send_error( $result['error'] );
			return;
		}

		self::send_success(
			array(
				'message'     => sprintf(
					/* translators: %s: workflow name */
					__( 'Workflow "%s" created from template successfully!', 'wpshadow' ),
					$result['name']
				),
				'workflow_id' => $result['id'],
				'redirect'    => admin_url( 'admin.php?page=wpshadow-workflows&action=edit&workflow=' . $result['id'] ),
			)
		);
	}
}

Create_From_Template_Handler::register();

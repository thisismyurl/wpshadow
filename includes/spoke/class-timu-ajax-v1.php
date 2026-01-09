<?php
/**
 * TIMU AJAX endpoints for spoke plugins.
 *
 * @package TIMU_CORE_SUPPORT
 * @version 1.2601.0819
 */

declare(strict_types=1);

namespace TIMU\Core\Spoke;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TIMU_Ajax_v1 {

	public function __construct( private readonly TIMU_Spoke_Base $core ) {
		\add_action( 'wp_ajax_' . $this->core->plugin_slug . '_clear_cache', array( $this, 'clear_cache' ) );
		\add_action( 'wp_ajax_' . $this->core->plugin_slug . '_refresh_status', array( $this, 'refresh_status' ) );
	}

	public function clear_cache(): void {
		$this->core->verify_ajax_nonce();
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( array( 'message' => 'Unauthorized.' ), 403 );
		}
		$this->core->clear_option_cache();
		\wp_send_json_success( array( 'message' => 'Cache cleared.' ) );
	}

	public function refresh_status(): void {
		$this->core->verify_ajax_nonce();
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( array( 'message' => 'Unauthorized.' ), 403 );
		}
		$data = array(
			'status'      => 'ok',
			'php_version' => PHP_VERSION,
			'wp_version'  => \get_bloginfo( 'version' ),
			'plugin'      => $this->core->plugin_version,
		);
		\wp_send_json_success( $data );
	}
}

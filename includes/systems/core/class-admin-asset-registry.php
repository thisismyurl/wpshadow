<?php
/**
 * Admin Asset Registry
 *
 * Centralizes common admin asset enqueue and localization patterns
 * to reduce duplication across modules.
 *
 * @package WPShadow\Core
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin_Asset_Registry Class
 *
 * Provides reusable helpers for common admin asset registration patterns.
 *
 * @since 0.6093.1200
 */
class Admin_Asset_Registry {

	/**
	 * Enqueue shared modal assets.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function enqueue_modal_assets(): void {
		// Modal-specific bundles are not currently shipped as standalone assets.
		// Keep this helper as a stable integration point for future reintroduction.
	}

	/**
	 * Localize script data with a standard AJAX URL and nonce.
	 *
	 * @since 0.6093.1200
	 * @param  string $handle       Script handle.
	 * @param  string $object_name  JS object name.
	 * @param  string $nonce_action Nonce action key.
	 * @param  array  $data         Additional localization data.
	 * @param  string $nonce_key    Nonce field key. Default 'nonce'.
	 * @param  string $ajax_key     AJAX URL field key. Default 'ajaxUrl'.
	 * @return void
	 */
	public static function localize_with_ajax_nonce(
		string $handle,
		string $object_name,
		string $nonce_action,
		array $data = array(),
		string $nonce_key = 'nonce',
		string $ajax_key = 'ajaxUrl'
	): void {
		$payload = array_merge(
			array(
				$ajax_key => admin_url( 'admin-ajax.php' ),
				$nonce_key => wp_create_nonce( $nonce_action ),
			),
			$data
		);

		wp_localize_script( $handle, $object_name, $payload );
	}
}

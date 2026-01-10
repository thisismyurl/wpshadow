<?php
/**
 * WP-CLI commands for TIMU core.
 *
 * @package TIMU_CORE_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

use WP_CLI;
use WP_CLI\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CLI: Modules.
 */
class TIMU_CLI_Modules {
	/**
	 * List registered modules.
	 *
	 * ## OPTIONS
	 * [--type=<type>]
	 * : Filter by module type (hub, spoke, formats).
	 *
	 * [--suite=<suite>]
	 * : Filter by suite identifier.
	 */
	public function list( array $args, array $assoc_args ): void { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
		$type  = isset( $assoc_args['type'] ) ? sanitize_key( $assoc_args['type'] ) : null;
		$suite = isset( $assoc_args['suite'] ) ? sanitize_text_field( (string) $assoc_args['suite'] ) : null;

		$modules = TIMU_Module_Registry::get_modules_filtered( $type, $suite );

		$rows = array();
		foreach ( $modules as $module ) {
			$rows[] = array(
				'slug'    => $module['slug'] ?? '',
				'name'    => $module['name'] ?? '',
				'type'    => $module['type'] ?? '',
				'suite'   => $module['suite'] ?? '',
				'cap'     => implode( ',', $module['capabilities'] ?? array() ),
				'version' => $module['version'] ?? '',
			);
		}

		Utils\format_items( 'table', $rows, array( 'slug', 'name', 'type', 'suite', 'cap', 'version' ) );
	}
}

/**
 * CLI: Settings.
 */
class TIMU_CLI_Settings {
	/**
	 * Get a setting.
	 *
	 * ## OPTIONS
	 * <module>
	 * : Module slug.
	 *
	 * <key>
	 * : Setting key.
	 *
	 * [--network]
	 * : Use network scope.
	 */
	public function get( array $args, array $assoc_args ): void {
		list( $module, $key ) = $args;
		$network             = isset( $assoc_args['network'] );
		$value               = TIMU_Settings::get( $module, $key, null, $network );

		WP_CLI::line( is_scalar( $value ) ? (string) $value : wp_json_encode( $value ) );
	}

	/**
	 * Set a setting.
	 *
	 * ## OPTIONS
	 * <module>
	 * : Module slug.
	 *
	 * <key>
	 * : Setting key.
	 *
	 * <value>
	 * : Setting value.
	 *
	 * [--network]
	 * : Use network scope.
	 */
	public function set( array $args, array $assoc_args ): void {
		list( $module, $key, $value ) = $args;
		$network                     = isset( $assoc_args['network'] );

		$decoded = json_decode( $value, true );
		$store   = ( JSON_ERROR_NONE === json_last_error() ) ? $decoded : $value;

		TIMU_Settings::update( $module, $key, $store, $network );
		WP_CLI::success( 'Setting updated.' );
	}

	/**
	 * Delete a setting.
	 *
	 * ## OPTIONS
	 * <module>
	 * : Module slug.
	 *
	 * <key>
	 * : Setting key.
	 *
	 * [--network]
	 * : Use network scope.
	 */
	public function delete( array $args, array $assoc_args ): void {
		list( $module, $key ) = $args;
		$network             = isset( $assoc_args['network'] );

		TIMU_Settings::delete( $module, $key, $network );
		WP_CLI::success( 'Setting deleted.' );
	}
}

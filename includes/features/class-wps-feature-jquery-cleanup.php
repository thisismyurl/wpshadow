<?php
/**
 * Feature: jQuery Migrate Removal
 *
 * Remove jQuery Migrate script which provides backward compatibility for very old jQuery code.
 * Modern WordPress sites don't need this script, and removing it saves bandwidth and processing time.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_jQuery_Cleanup
 *
 * Remove jQuery Migrate script for modern sites.
 */
final class WPSHADOW_Feature_jQuery_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'jquery-cleanup',
				'name'            => __( 'Remove Old jQuery Code', 'wpshadow' ),
				'description'     => __( 'Speed up your site by removing code that only old websites need. Your site will work the same but load faster.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => true,
				'version'         => '1.0.0',
				'widget_group'    => 'performance',
				'aliases'         => array( 'jquery migrate', 'remove jquery', 'javascript cleanup', 'jquery optimization', 'legacy jquery', 'old javascript', 'jquery performance', 'remove migrate', 'js optimization', 'javascript performance', 'jquery bloat', 'legacy scripts' ),
				'sub_features'    => array(
					'remove_migrate_frontend' => __( 'Remove old jQuery code from visitor pages', 'wpshadow' ),
					'keep_admin'              => __( 'Keep old jQuery code in admin area', 'wpshadow' ),
					'log_removals'            => __( 'Record when old code is removed', 'wpshadow' ),
				),
			)
		);

		$this->register_default_settings(
			array(
				'remove_migrate_frontend' => true,
				'keep_admin'              => true,
				'log_removals'            => false,
			)
		);

		$this->log_activity( 'feature_initialized', 'jQuery Cleanup feature initialized', 'info' );
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'remove_jquery_migrate' ), 100 );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow jquery-cleanup', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Remove jQuery Migrate from frontend.
	 *
	 * @return void
	 */
	public function remove_jquery_migrate(): void {
		// Keep in admin if enabled.
		if ( is_admin() && $this->is_sub_feature_enabled( 'keep_admin', true ) ) {
			do_action( 'wpshadow_jquery_cleanup_kept_admin' );
			return;
		}

		// Remove jQuery Migrate on frontend.
		if ( ! is_admin() && $this->is_sub_feature_enabled( 'remove_migrate_frontend', true ) ) {
			global $wp_scripts;

			if ( isset( $wp_scripts->registered['jquery'] ) ) {
				$jquery_dependencies = $wp_scripts->registered['jquery']->deps;
				$wp_scripts->registered['jquery']->deps = array_diff( $jquery_dependencies, array( 'jquery-migrate' ) );
			}

			wp_deregister_script( 'jquery-migrate' );
			do_action( 'wpshadow_jquery_cleanup_removed' );

			if ( $this->is_sub_feature_enabled( 'log_removals', false ) ) {
				$this->log_activity( 'jquery_migrate_removed', 'jQuery Migrate removed from frontend', 'info' );
			}
		}
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['jquery_cleanup'] = array(
			'label' => __( 'jQuery Migrate Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_jquery_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for jQuery cleanup.
	 *
	 * @return array Test result.
	 */
	public function test_jquery_cleanup(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'jQuery Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'jQuery cleanup is disabled.', 'wpshadow' ),
				'test'        => 'jquery_cleanup',
			);
		}

		$enabled_features = 0;

		if ( $this->is_sub_feature_enabled( 'remove_migrate_frontend', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'keep_admin', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'log_removals', false ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 2 ? 'good' : 'recommended';

		return array(
			'label'       => __( 'jQuery Migrate cleanup is active', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'%d jQuery cleanup features enabled.',
				(int) $enabled_features
			),
			'test'        => 'jquery_cleanup',
		);
	}

	/**
	 * Handle WP-CLI command for jQuery cleanup.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args (unused).
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow jquery-cleanup status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'jQuery Cleanup status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'remove_migrate_frontend',
			'keep_admin',
			'log_removals',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'jQuery cleanup inspected.', 'wpshadow' ) );
	}
}

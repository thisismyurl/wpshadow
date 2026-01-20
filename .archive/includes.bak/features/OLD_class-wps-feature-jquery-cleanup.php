<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_jQuery_Cleanup extends WPSHADOW_Abstract_Feature {

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
					'remove_migrate_frontend' => array(
						'name'               => __( 'Remove jQuery Migrate', 'wpshadow' ),
						'description_short'  => __( 'Remove backwards-compatibility jQuery code', 'wpshadow' ),
						'description_long'   => __( 'Removes jQuery Migrate, a compatibility library that allows old jQuery code to run on current jQuery versions. Modern sites don\'t need this backwards-compatibility layer since they don\'t use extremely old jQuery syntax. jQuery Migrate adds 3-5KB of unnecessary code. Removing it improves performance without affecting modern jQuery code.', 'wpshadow' ),
						'description_wizard' => __( 'jQuery Migrate is only needed for very old websites. Modern sites don\'t use the old syntax it supports. Remove it to reduce page size with no downside for new WordPress sites.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'keep_admin'              => array(
						'name'               => __( 'Keep Migrate in Admin', 'wpshadow' ),
						'description_short'  => __( 'Keep jQuery Migrate in WordPress admin area', 'wpshadow' ),
						'description_long'   => __( 'Keeps jQuery Migrate in the WordPress admin area even if it\'s removed from the frontend. The WordPress admin uses jQuery extensively and may rely on older jQuery patterns. Keeping Migrate in admin prevents potential admin functionality issues while still removing it from visitor-facing pages.', 'wpshadow' ),
						'description_wizard' => __( 'Safe option that removes jQuery Migrate from visitor pages but keeps it in the admin area to prevent functionality issues.', 'wpshadow' ),
						'default_enabled'    => true,
					),
					'log_removals'            => array(
						'name'               => __( 'Log jQuery Removals', 'wpshadow' ),
						'description_short'  => __( 'Record when jQuery Migrate is removed', 'wpshadow' ),
						'description_long'   => __( 'Enables logging of when jQuery Migrate is removed and what other jQuery dependencies exist. This helps with debugging if any site functionality appears to break. Logs are recorded in the WPShadow activity log and can help identify plugins that might be using old jQuery syntax.', 'wpshadow' ),
						'description_wizard' => __( 'Enable for troubleshooting if you suspect plugins need jQuery Migrate. Helps identify which plugins use old jQuery code that might break.', 'wpshadow' ),
						'default_enabled'    => false,
					),
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

	public function remove_jquery_migrate(): void {

		if ( is_admin() && $this->is_sub_feature_enabled( 'keep_admin', true ) ) {
			do_action( 'wpshadow_jquery_cleanup_kept_admin' );
			return;
		}

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

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['jquery_cleanup'] = array(
			'label' => __( 'jQuery Migrate Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_jquery_cleanup' ),
		);

		return $tests;
	}

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

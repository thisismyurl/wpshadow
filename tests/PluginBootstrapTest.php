<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PluginBootstrapTest extends TestCase {
	public function test_plugin_is_active_in_smoke_site(): void {
		$this->assertTrue( is_plugin_active( 'wpshadow/wpshadow.php' ) );
	}

	public function test_core_classes_are_loaded(): void {
		$this->assertTrue( class_exists( \WPShadow\Core\Bootstrap_Autoloader::class ) );
		$this->assertTrue( class_exists( \WPShadow\Core\Plugin_Bootstrap::class ) );
		$this->assertTrue( class_exists( \WPShadow\Core\Hooks_Initializer::class ) );
	}

	public function test_primary_admin_callbacks_exist(): void {
		$this->assertTrue( function_exists( 'wpshadow_render_dashboard_v2' ) );
		$this->assertTrue( function_exists( 'wpshadow_render_guardian_page' ) );
		$this->assertTrue( function_exists( 'wpshadow_render_settings' ) );
		$this->assertTrue( function_exists( 'wpshadow_run_diagnostic_scan' ) );
		$this->assertTrue( function_exists( 'wpshadow_run_diagnostic' ) );
		$this->assertTrue( function_exists( 'wpshadow_get_readiness_inventory' ) );
		$this->assertTrue( function_exists( 'wpshadow_get_treatment_definitions' ) );
	}

	public function test_key_admin_hooks_are_registered(): void {
		do_action( 'admin_menu' );

		global $menu;

		$top_level_slugs = array();
		foreach ( (array) $menu as $item ) {
			if ( is_array( $item ) && isset( $item[2] ) ) {
				$top_level_slugs[] = (string) $item[2];
			}
		}

		$this->assertContains( 'wpshadow', $top_level_slugs );
		$this->assertNotFalse( has_action( 'wp_ajax_wpshadow_get_dashboard_data' ) );
		$this->assertNotFalse( has_action( 'wp_ajax_wpshadow_post_scan_treatments' ) );
	}

	public function test_runtime_wrappers_can_be_short_circuited_by_filters(): void {
		$scan_hook = static function ( $pre_result, bool $force ): array {
			return array(
				'success' => true,
				'forced'  => $force,
			);
		};
		add_filter( 'wpshadow_pre_run_scan', $scan_hook, 10, 2 );

		$scan_result = wpshadow_run_diagnostic_scan( true );
		remove_filter( 'wpshadow_pre_run_scan', $scan_hook, 10 );

		$this->assertSame(
			array(
				'success' => true,
				'forced'  => true,
			),
			$scan_result
		);

		$diagnostic_hook = static function ( $pre_result, string $diagnostic_id ): array {
			return array(
				'success'    => true,
				'diagnostic' => $diagnostic_id,
				'message'    => 'short-circuited',
			);
		};
		add_filter( 'wpshadow_pre_run_diagnostic', $diagnostic_hook, 10, 4 );

		$diagnostic_result = wpshadow_run_diagnostic( 'fake-diagnostic', false );
		remove_filter( 'wpshadow_pre_run_diagnostic', $diagnostic_hook, 10 );

		$this->assertSame( 'fake-diagnostic', $diagnostic_result['diagnostic'] );
		$this->assertSame( 'short-circuited', $diagnostic_result['message'] );

		$inventory_hook = static function (): array {
			return array(
				'generated_at' => 123,
				'diagnostics'  => array(),
				'treatments'   => array(),
			);
		};
		add_filter( 'wpshadow_pre_readiness_inventory', $inventory_hook );

		$inventory = wpshadow_get_readiness_inventory();
		remove_filter( 'wpshadow_pre_readiness_inventory', $inventory_hook );

		$this->assertSame( 123, $inventory['generated_at'] );
	}

	public function test_top_level_autofix_wrapper_can_be_short_circuited(): void {
		$hook = static function ( $pre_result, string $finding_id, bool $dry_run ): array {
			return array(
				'success'    => true,
				'finding_id' => $finding_id,
				'dry_run'    => $dry_run,
			);
		};
		add_filter( 'wpshadow_pre_attempt_autofix', $hook, 10, 3 );

		$result = wpshadow_attempt_autofix( 'example-finding', true );
		remove_filter( 'wpshadow_pre_attempt_autofix', $hook, 10 );

		$this->assertTrue( $result['success'] );
		$this->assertSame( 'example-finding', $result['finding_id'] );
		$this->assertTrue( $result['dry_run'] );
	}

	public function test_cli_command_class_registers_commands_when_wp_cli_is_present(): void {
		if ( ! class_exists( 'WP_CLI', false ) ) {
			eval(
				'class WP_CLI {' .
				'public static array $commands = array();' .
				'public static function add_command( string $name, $callable ): void {' .
				'self::$commands[ $name ] = $callable;' .
				'}' .
				'public static function line( string $message ): void {}' .
				'public static function print_value( $value, array $args = array() ): void {}' .
				'public static function halt( int $exit_code ): void {}' .
				'public static function error( string $message ): void {' .
				'throw new RuntimeException( $message );' .
				'}' .
				'}'
			);
		}

		require_once __DIR__ . '/../includes/utils/cli/class-wpshadow-cli.php';
		\WPShadow\CLI\WPShadow_CLI_Command::register();

		$this->assertArrayHasKey( 'wpshadow diagnostics list', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'wpshadow diagnostics run', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'wpshadow scan run', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'wpshadow treatments list', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'wpshadow treatments apply', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'wpshadow readiness export', \WP_CLI::$commands );
	}
}
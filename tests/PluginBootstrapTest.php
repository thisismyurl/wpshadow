<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PluginBootstrapTest extends TestCase {
	public function test_plugin_is_active_in_smoke_site(): void {
		$this->assertTrue( is_plugin_active( 'thisismyurl-shadow/thisismyurl-shadow.php' ) );
	}

	public function test_core_classes_are_loaded(): void {
		$this->assertTrue( class_exists( \ThisIsMyURL\Shadow\Core\Bootstrap_Autoloader::class ) );
		$this->assertTrue( class_exists( \ThisIsMyURL\Shadow\Core\Plugin_Bootstrap::class ) );
		$this->assertTrue( class_exists( \ThisIsMyURL\Shadow\Core\Hooks_Initializer::class ) );
	}

	public function test_primary_admin_callbacks_exist(): void {
		$this->assertTrue( function_exists( 'thisismyurl_shadow_render_dashboard_v2' ) );
		$this->assertTrue( function_exists( 'thisismyurl_shadow_render_guardian_page' ) );
		$this->assertTrue( function_exists( 'thisismyurl_shadow_render_settings' ) );
		$this->assertTrue( function_exists( 'thisismyurl_shadow_run_diagnostic_scan' ) );
		$this->assertTrue( function_exists( 'thisismyurl_shadow_run_diagnostic' ) );
		$this->assertTrue( function_exists( 'thisismyurl_shadow_get_readiness_inventory' ) );
		$this->assertTrue( function_exists( 'thisismyurl_shadow_get_treatment_definitions' ) );
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

		$this->assertContains( 'thisismyurl-shadow', $top_level_slugs );
		$this->assertNotFalse( has_action( 'wp_ajax_thisismyurl_shadow_get_dashboard_data' ) );
		$this->assertNotFalse( has_action( 'wp_ajax_thisismyurl_shadow_post_scan_treatments' ) );
	}

	public function test_runtime_wrappers_can_be_short_circuited_by_filters(): void {
		$scan_hook = static function ( $pre_result, bool $force ): array {
			return array(
				'success' => true,
				'forced'  => $force,
			);
		};
		add_filter( 'thisismyurl_shadow_pre_run_scan', $scan_hook, 10, 2 );

		$scan_result = thisismyurl_shadow_run_diagnostic_scan( true );
		remove_filter( 'thisismyurl_shadow_pre_run_scan', $scan_hook, 10 );

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
		add_filter( 'thisismyurl_shadow_pre_run_diagnostic', $diagnostic_hook, 10, 4 );

		$diagnostic_result = thisismyurl_shadow_run_diagnostic( 'fake-diagnostic', false );
		remove_filter( 'thisismyurl_shadow_pre_run_diagnostic', $diagnostic_hook, 10 );

		$this->assertSame( 'fake-diagnostic', $diagnostic_result['diagnostic'] );
		$this->assertSame( 'short-circuited', $diagnostic_result['message'] );

		$inventory_hook = static function (): array {
			return array(
				'generated_at' => 123,
				'diagnostics'  => array(),
				'treatments'   => array(),
			);
		};
		add_filter( 'thisismyurl_shadow_pre_readiness_inventory', $inventory_hook );

		$inventory = thisismyurl_shadow_get_readiness_inventory();
		remove_filter( 'thisismyurl_shadow_pre_readiness_inventory', $inventory_hook );

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
		add_filter( 'thisismyurl_shadow_pre_attempt_autofix', $hook, 10, 3 );

		$result = thisismyurl_shadow_attempt_autofix( 'example-finding', true );
		remove_filter( 'thisismyurl_shadow_pre_attempt_autofix', $hook, 10 );

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

		require_once __DIR__ . '/../includes/utils/cli/class-thisismyurl-shadow-cli.php';
		\ThisIsMyURL\Shadow\CLI\thisismyurl_shadow_CLI_Command::register();

		$this->assertArrayHasKey( 'thisismyurl-shadow diagnostics list', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'thisismyurl-shadow diagnostics run', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'thisismyurl-shadow scan run', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'thisismyurl-shadow treatments list', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'thisismyurl-shadow treatments apply', \WP_CLI::$commands );
		$this->assertArrayHasKey( 'thisismyurl-shadow readiness export', \WP_CLI::$commands );
	}
}
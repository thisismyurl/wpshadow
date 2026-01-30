<?php
/**
 * Missing WP-CLI Commands Diagnostic
 *
 * Checks if theme/plugin provides WP-CLI commands for common administrative
 * tasks. WP-CLI integration improves developer experience and automation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1805
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_WPCLI_Commands Class
 *
 * Detects if theme/plugin lacks WP-CLI command integration.
 *
 * @since 1.6028.1805
 */
class Diagnostic_Missing_WPCLI_Commands extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-wpcli-commands';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing WP-CLI Commands';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme/plugin provides WP-CLI integration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code_quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1805
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only check if WP-CLI is available.
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return null; // WP-CLI not available, skip check.
		}

		$analysis = self::analyze_wpcli_integration();

		if ( ! $analysis['should_have_cli'] ) {
			return null; // Simple theme/plugin, CLI not expected.
		}

		if ( $analysis['has_cli_commands'] ) {
			return null; // Has CLI integration.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: theme or plugin */
				__( 'Active %s lacks WP-CLI command integration', 'wpshadow' ),
				$analysis['type']
			),
			'severity'    => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wpcli-commands',
			'family'      => self::$family,
			'meta'        => array(
				'type'              => $analysis['type'],
				'complexity'        => $analysis['complexity'],
				'recommended'       => __( 'Add WP-CLI commands for common administrative tasks', 'wpshadow' ),
				'impact_level'      => 'low',
				'immediate_actions' => array(
					__( 'Identify common admin tasks', 'wpshadow' ),
					__( 'Create WP-CLI command class', 'wpshadow' ),
					__( 'Register with WP_CLI::add_command()', 'wpshadow' ),
					__( 'Document commands in README', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'WP-CLI integration makes plugins/themes more developer-friendly by enabling automation, scripting, and remote management. Commands help with deployment, debugging, bulk operations, and CI/CD pipelines. Professional plugins provide CLI interfaces for common tasks. This improves user experience for technical users and hosting companies.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Manual Work: Tasks require admin UI interaction', 'wpshadow' ),
					__( 'No Automation: Can\'t script repetitive operations', 'wpshadow' ),
					__( 'Slow Deployment: Manual configuration on each site', 'wpshadow' ),
					__( 'Poor DX: Developers prefer command-line tools', 'wpshadow' ),
				),
				'analysis'      => $analysis,
				'common_use_cases' => array(
					__( 'Bulk data import/export operations', 'wpshadow' ),
					__( 'Cache clearing and regeneration', 'wpshadow' ),
					__( 'Configuration sync across environments', 'wpshadow' ),
					__( 'Diagnostic and health checks', 'wpshadow' ),
					__( 'Database migrations and updates', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Basic Command Class', 'wpshadow' ),
						'description' => __( 'Create simple WP-CLI command for common task', 'wpshadow' ),
						'steps'       => array(
							__( 'Create class in includes/cli/ directory', 'wpshadow' ),
							__( 'Add method with @synopsis docblock', 'wpshadow' ),
							__( 'Register: if (defined(\'WP_CLI\') && WP_CLI) { WP_CLI::add_command(\'myplugin\', \'MyPlugin_CLI\'); }', 'wpshadow' ),
							__( 'Test: wp myplugin <command>', 'wpshadow' ),
							__( 'Document in README.md', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Multiple Commands', 'wpshadow' ),
						'description' => __( 'Add CLI commands for all major features', 'wpshadow' ),
						'steps'       => array(
							__( 'Create command for each major feature', 'wpshadow' ),
							__( 'Add subcommands: wp myplugin import, wp myplugin export, etc.', 'wpshadow' ),
							__( 'Include progress bars: $progress = \\WP_CLI\\Utils\\make_progress_bar()', 'wpshadow' ),
							__( 'Add success/error messages: WP_CLI::success()', 'wpshadow' ),
							__( 'Document all commands with examples', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Full CLI Suite', 'wpshadow' ),
						'description' => __( 'Comprehensive command-line interface', 'wpshadow' ),
						'steps'       => array(
							__( 'Create CLI namespace: wp myplugin <command>', 'wpshadow' ),
							__( 'Add interactive prompts for safety', 'wpshadow' ),
							__( 'Include CSV/JSON export formats', 'wpshadow' ),
							__( 'Add --dry-run flag for testing', 'wpshadow' ),
							__( 'Provide shell completion script', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Use clear command naming: wp myplugin action', 'wpshadow' ),
					__( 'Include progress indicators for long operations', 'wpshadow' ),
					__( 'Provide --help text for all commands', 'wpshadow' ),
					__( 'Use WP_CLI::confirm() before destructive actions', 'wpshadow' ),
					__( 'Return proper exit codes (0 = success)', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Run: wp help myplugin', 'wpshadow' ),
						__( 'Verify commands are listed', 'wpshadow' ),
						__( 'Test each command with --help', 'wpshadow' ),
						__( 'Run commands to ensure they work', 'wpshadow' ),
					),
					'expected_result' => __( 'WP-CLI commands available for common administrative tasks', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze WP-CLI integration level.
	 *
	 * @since  1.6028.1805
	 * @return array Analysis results.
	 */
	private static function analyze_wpcli_integration() {
		$result = array(
			'has_cli_commands' => false,
			'should_have_cli'  => false,
			'type'             => 'theme',
			'complexity'       => 'simple',
			'plugin_count'     => 0,
			'theme_size'       => 0,
		);

		// Check if theme has CLI commands.
		$theme_dir = get_template_directory();
		$theme_has_cli = self::has_cli_integration( $theme_dir );
		$theme_files = self::count_php_files( $theme_dir );

		// Check if any active plugin has CLI commands.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_with_cli = 0;
		$total_plugins = count( $active_plugins );

		foreach ( array_slice( $active_plugins, 0, 10 ) as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			if ( is_dir( $plugin_dir ) && self::has_cli_integration( $plugin_dir ) ) {
				$plugins_with_cli++;
			}
		}

		// Determine if CLI integration is expected.
		$result['theme_size'] = $theme_files;
		$result['plugin_count'] = $total_plugins;

		// Complex themes (>20 files) should have CLI.
		if ( $theme_files > 20 ) {
			$result['should_have_cli'] = true;
			$result['complexity'] = 'complex';
		}

		// If other plugins have CLI, expect theme to have it too.
		if ( $plugins_with_cli > 2 ) {
			$result['should_have_cli'] = true;
		}

		// Check if we found any CLI integration.
		if ( $theme_has_cli || $plugins_with_cli > 0 ) {
			$result['has_cli_commands'] = true;
		}

		// If no CLI found but complexity suggests it should.
		if ( ! $result['has_cli_commands'] && $result['should_have_cli'] ) {
			$result['type'] = $theme_files > 20 ? 'theme' : 'plugin';
		}

		return $result;
	}

	/**
	 * Check if directory has WP-CLI integration.
	 *
	 * @since  1.6028.1805
	 * @param  string $directory Directory to check.
	 * @return bool True if CLI integration found.
	 */
	private static function has_cli_integration( $directory ) {
		if ( ! is_dir( $directory ) ) {
			return false;
		}

		// Check for dedicated CLI directory.
		if ( is_dir( $directory . '/cli' ) || is_dir( $directory . '/includes/cli' ) ) {
			return true;
		}

		// Scan PHP files for WP_CLI::add_command.
		$php_files = glob( $directory . '/*.php' );
		if ( is_dir( $directory . '/includes' ) ) {
			$php_files = array_merge( $php_files, glob( $directory . '/includes/*.php' ) );
		}

		foreach ( array_slice( $php_files, 0, 20 ) as $file ) {
			$content = @file_get_contents( $file );
			if ( $content && strpos( $content, 'WP_CLI::add_command' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count PHP files in directory.
	 *
	 * @since  1.6028.1805
	 * @param  string $directory Directory path.
	 * @return int File count.
	 */
	private static function count_php_files( $directory ) {
		if ( ! is_dir( $directory ) ) {
			return 0;
		}

		$count = 0;
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				// Skip vendor.
				if ( strpos( $file->getPathname(), '/vendor/' ) !== false ) {
					continue;
				}
				$count++;
			}

			// Limit scan for performance.
			if ( $count >= 100 ) {
				break;
			}
		}

		return $count;
	}
}

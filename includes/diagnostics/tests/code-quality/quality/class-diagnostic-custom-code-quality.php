<?php
/**
 * Custom Code Quality Diagnostic
 *
 * Verifies custom code (functions.php, child themes)
 * doesn't have obvious errors or security issues.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Custom_Code_Quality Class
 *
 * Verifies custom code quality.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Custom_Code_Quality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-code-quality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Code Quality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies custom code quality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if code issues found, null otherwise.
	 */
	public static function check() {
		$code_quality = self::check_custom_code();

		if ( ! $code_quality['has_issue'] ) {
			return null; // Custom code healthy
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: code issue */
				__( 'Custom code issue: %s. Bad code = white screens, slow site, security holes. Have developer review or consider plugin alternative.', 'wpshadow' ),
				$code_quality['issue']
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/custom-code-quality',
			'family'       => self::$family,
			'meta'         => array(
				'custom_code_found' => true,
			),
			'details'      => array(
				'custom_code_issues'              => array(
					'Syntax Errors' => array(
						'Example: Missing semicolon, bracket',
						'Result: White screen of death',
						'Detection: Enable WP_DEBUG',
					),
					'Deprecated Functions' => array(
						'Example: each(), create_function()',
						'Breaks in PHP 8.0+',
						'Detection: Update PHP test',
					),
					'Security Issues' => array(
						'SQL Injection: Unsanitized $wpdb queries',
						'XSS: Output not escaped',
						'Detection: Code review',
					),
					'Performance Issues' => array(
						'Loop in action: Runs 100 times/request',
						'Big query: SELECT * without LIMIT',
						'Result: Slow site',
					),
				),
				'dangerous_patterns'              => array(
					'eval() or create_function()' => array(
						'Security: Code injection risk',
						'Performance: Very slow',
						'Solution: Refactor to normal function',
					),
					'global $wpdb; wp_query()' => array(
						'Issue: Unescaped database queries',
						'Fix: Use $wpdb->prepare()',
					),
					'echo $_POST' => array(
						'Security: XSS vulnerability',
						'Fix: Use echo esc_html($_POST)',
					),
					'while loop with no break' => array(
						'Performance: Infinite loop',
						'Result: CPU 100%, site hangs',
					),
				),
				'where_custom_code_lives'         => array(
					'functions.php' => array(
						'File: /wp-content/themes/[theme]/functions.php',
						'Caution: Executes on every page load',
						'Backup: Always backup before editing',
					),
					'Must Use Plugins' => array(
						'Directory: /wp-content/mu-plugins/',
						'Special: Load before regular plugins',
						'Use for: Site-wide functionality',
					),
					'Code Snippets' => array(
						'Plugin: Code Snippets plugin',
						'Safer: UI with syntax highlighting',
						'Easier: Than functions.php',
					),
				),
				'testing_custom_code'             => array(
					'Enable Debug Mode' => array(
						'wp-config.php: define(\'WP_DEBUG\', true);',
						'Log: define(\'WP_DEBUG_LOG\', true);',
						'Display: define(\'WP_DEBUG_DISPLAY\', false);',
					),
					'Check Debug Log' => array(
						'File: /wp-content/debug.log',
						'Errors: Syntax, deprecated, etc.',
					),
					'Test Features' => array(
						'Homepage: Load in browser',
						'Pages: Check custom post types',
						'Checkout: Test if WooCommerce',
					),
				),
				'fixing_custom_code'              => array(
					'Backup First' => array(
						'Files: Back up functions.php',
						'Database: Back up site',
						'Critical: Can\'t undo broken code',
					),
					'Fix Issues' => array(
						'Syntax: Add missing brackets',
						'Security: Escape output, prepare queries',
						'Performance: Remove loop issues',
					),
					'Verify' => array(
						'Test: Reload page',
						'Debug: Check debug.log',
						'Function: Works as expected?',
					),
				),
				'best_practices'                  => array(
					__( 'Use plugins instead: Less risky than custom code' ),
					__( 'Comment code: Explain what it does' ),
					__( 'Security: Always sanitize + escape' ),
					__( 'Performance: Avoid database queries in loops' ),
					__( 'Testing: Test on staging before production' ),
				),
			),
		);
	}

	/**
	 * Check custom code quality.
	 *
	 * @since  1.2601.2148
	 * @return array Custom code status.
	 */
	private static function check_custom_code() {
		$has_issue = false;
		$issue = '';

		// Check functions.php file
		$theme = wp_get_theme();
		$functions_file = $theme->get_stylesheet_directory() . '/functions.php';

		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			// Check for obvious issues
			if ( strpos( $content, 'eval(' ) !== false ) {
				$has_issue = true;
				$issue = 'functions.php contains eval() - security risk';
			} elseif ( strpos( $content, '$wpdb->query' ) !== false && strpos( $content, 'prepare' ) === false ) {
				$has_issue = true;
				$issue = 'functions.php has unescaped database queries';
			} elseif ( strpos( $content, 'echo $_POST' ) !== false || strpos( $content, 'echo $_GET' ) !== false ) {
				$has_issue = true;
				$issue = 'functions.php directly outputs user input - XSS risk';
			}
		}

		return array(
			'has_issue' => $has_issue,
			'issue'    => $issue ?: 'Custom code looks healthy',
		);
	}
}

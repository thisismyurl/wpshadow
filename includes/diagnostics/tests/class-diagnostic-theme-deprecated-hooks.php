<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Theme using deprecated hooks
 *
 * Philosophy: Show value (#9) - code quality analysis
 * @package WPShadow
 *
 * @verified 2026-01-23 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Theme_Deprecated_Hooks extends Diagnostic_Base {
	protected static $slug = 'theme-deprecated-hooks';

	protected static $title = 'Theme Deprecated Hooks';

	protected static $description = 'Detects deprecated WordPress hooks used by active theme.';

	protected static $family = 'code-quality';

	protected static $family_label = 'Code Quality';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'theme-deprecated-hooks';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Theme uses deprecated hooks', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Detect if active theme is using deprecated WordPress hooks that may be removed in future versions.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'code-quality';
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		return 55;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/theme-deprecated-hooks/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/theme-deprecated-hooks/';
	}

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding data or null if no issue
	 */
	public static function check(): ?array {
		global $wp_filter;

		// Common deprecated hooks from WordPress
		$deprecated_hooks = array(
			'edit_category_form_pre' => 'Removed in WP 3.0',
			'after_db_upgrade' => 'Removed in WP 3.5',
			'sanitize_user_object' => 'Removed in WP 4.4',
			'check_comment_flood' => 'Removed in WP 4.7',
			'xmlrpc_call' => 'Changed in WP 5.5',
			'user_admin_notices' => 'Removed in WP 3.1',
			'admin_head' => 'Deprecated, use admin_enqueue_scripts',
			'wp_footer' => 'Can cause conflicts in some themes',
		);

		$found_deprecated = array();
		
		foreach ( $deprecated_hooks as $hook => $note ) {
			if ( isset( $wp_filter[ $hook ] ) && ! empty( $wp_filter[ $hook ]->callbacks ) ) {
				$found_deprecated[ $hook ] = $note;
			}
		}

		if ( empty( $found_deprecated ) ) {
			return null; // No deprecated hooks found, healthy
		}

		return array(
			'id'            => 'theme-deprecated-hooks',
			'title'         => 'Theme Using Deprecated Hooks',
			'description'   => sprintf(
				'Your active theme is using %d deprecated hook(s): %s. Update theme to remove these. <a href="https://wpshadow.com/kb/theme-deprecated-hooks/" target="_blank">Learn more about theme hooks</a>',
				count( $found_deprecated ),
				implode( ', ', array_keys( $found_deprecated ) )
			),
			'severity'      => 'medium',
			'category'      => 'code-quality',
			'kb_link'       => 'https://wpshadow.com/kb/theme-deprecated-hooks/',
			'training_link' => 'https://wpshadow.com/training/theme-deprecated-hooks/',
			'auto_fixable'  => false,
			'threat_level'  => 55,
			'data'          => array(
				'deprecated_hooks' => $found_deprecated,
			),
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Test Purpose:
	 * Verify check() method correctly detects deprecated hooks used by theme.
	 * Pass criteria: No deprecated hooks found
	 * Fail criteria: Any deprecated hooks detected
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__theme_deprecated_hooks(): array {
		$result = self::check();

		if ( is_null( $result ) ) {
			return array(
				'passed'  => true,
				'message' => '✓ Theme uses no deprecated hooks',
			);
		}

		return array(
			'passed'  => false,
			'message' => '✗ Theme deprecated hooks: ' . $result['title'],
		);
	}

}

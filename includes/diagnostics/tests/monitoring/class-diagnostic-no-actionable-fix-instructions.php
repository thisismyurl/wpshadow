<?php
/**
 * No Actionable Fix Instructions
 *
 * Detects whether Site Health provides clear, step-by-step fix instructions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SiteHealth
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Actionable_Fix_Instructions Class
 *
 * Validates actionability of Site Health fix guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Actionable_Fix_Instructions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-actionable-fix-instructions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Site Health Fix Guidance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Site Health provides clear, actionable fix instructions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'site_health';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests for actionable fix instructions.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check for step-by-step instructions
		if ( ! self::has_step_by_step_instructions() ) {
			$issues[] = __( 'Site Health lacks step-by-step fix instructions', 'wpshadow' );
		}

		// 2. Check for documentation links
		if ( ! self::has_documentation_links() ) {
			$issues[] = __( 'Missing links to detailed fix documentation', 'wpshadow' );
		}

		// 3. Check for one-click fixes
		if ( ! self::has_one_click_fixes() ) {
			$issues[] = __( 'No one-click fix option available', 'wpshadow' );
		}

		// 4. Check for contextual help
		if ( ! self::has_contextual_help() ) {
			$issues[] = __( 'Missing contextual help or tooltips', 'wpshadow' );
		}

		// 5. Check for expert vs beginner guidance
		if ( ! self::has_user_level_guidance() ) {
			$issues[] = __( 'No beginner-friendly fix instructions', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of guidance gaps */
					__( '%d Site Health fix guidance gaps identified', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/site-health-fix-instructions?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'recommendations' => array(
					__( 'Add step-by-step fix instructions for each issue', 'wpshadow' ),
					__( 'Link to detailed KB articles from warnings', 'wpshadow' ),
					__( 'Implement one-click fixes where safe', 'wpshadow' ),
					__( 'Provide beginner-friendly guidance', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for step-by-step instructions.
	 *
	 * @since 0.6093.1200
	 * @return bool True if instructions available.
	 */
	private static function has_step_by_step_instructions() {
		// Check if Site Health tests include fix instructions
		if ( class_exists( 'WP_Site_Health' ) ) {
			$site_health = \WP_Site_Health::get_instance();

			// Check for REST API with test details
			if ( method_exists( $site_health, 'get_tests' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for documentation links.
	 *
	 * @since 0.6093.1200
	 * @return bool True if links available.
	 */
	private static function has_documentation_links() {
		// Check if Site Health test descriptions include links
		// This would be checked via examining rendered HTML or REST API

		// WordPress 5.7+ should include links
		global $wp_version;
		if ( version_compare( $wp_version, '5.7', '>=' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for one-click fixes.
	 *
	 * @since 0.6093.1200
	 * @return bool True if one-click fixes available.
	 */
	private static function has_one_click_fixes() {
		// Check for automatic fix implementations
		// Should exist for:
		// - Enable HTTPS (with redirect)
		// - Remove hello.php/hello-dolly.php
		// - Update plugins
		// - Create wp-config backup

		$automatic_fixes = array(
			'remove_insecure_headers',
			'remove_hello_plugin',
			'update_plugins',
			'create_backup',
		);

		// Check for any registered automatic fix
		foreach ( $automatic_fixes as $fix ) {
			if ( has_action( "wpshadow_fix_{$fix}" ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for contextual help.
	 *
	 * @since 0.6093.1200
	 * @return bool True if contextual help available.
	 */
	private static function has_contextual_help() {
		// Check if help screen exists
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();

		if ( $screen && method_exists( $screen, 'get_help_tabs' ) ) {
			$tabs = $screen->get_help_tabs();
			return ! empty( $tabs );
		}

		return false;
	}

	/**
	 * Check for user-level guidance.
	 *
	 * @since 0.6093.1200
	 * @return bool True if user-appropriate guidance available.
	 */
	private static function has_user_level_guidance() {
		// Check if guidance is available for non-technical users
		// Should include:
		// - Plain English explanations
		// - Screenshots
		// - Video tutorials
		// - Alternative "contact support" option

		// Check for education plugin
		if ( is_plugin_active( 'wpshadow-education/wpshadow-education.php' ) ) {
			return true;
		}

		// Check for guidance content
		$guidance = get_option( 'wpshadow_user_guidance', false );

		return false !== $guidance;
	}
}

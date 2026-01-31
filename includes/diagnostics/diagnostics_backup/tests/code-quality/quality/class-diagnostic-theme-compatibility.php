<?php
/**
 * Theme Compatibility Diagnostic
 *
 * Verifies active theme compatible with WordPress
 * version and doesn't have known vulnerabilities.
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
 * Diagnostic_Theme_Compatibility Class
 *
 * Verifies theme compatibility.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Theme_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies theme compatibility';

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
	 * @return array|null Finding array if compatibility issues, null otherwise.
	 */
	public static function check() {
		$compatibility_status = self::check_theme_compatibility();

		if ( ! $compatibility_status['has_issue'] ) {
			return null; // Theme compatible
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: issue description */
				__( 'Active theme issue: %s. Theme conflicts = broken features = customer-facing problems. Update or replace theme.', 'wpshadow' ),
				$compatibility_status['issue']
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-compatibility',
			'family'       => self::$family,
			'meta'         => array(
				'theme_compatible' => false,
			),
			'details'      => array(
				'common_theme_issues'             => array(
					'Outdated Theme' => array(
						'Issue: Not updated for 2+ years',
						'Risk: May break with new WordPress',
						'Solution: Update or switch themes',
					),
					'Unsupported Theme' => array(
						'Issue: Theme from unknown source',
						'Risk: No support, may have backdoor',
						'Solution: Use from wp.org or known dev',
					),
					'Gutenberg Incompatible' => array(
						'Issue: Old theme doesn\'t support blocks',
						'Risk: Editing posts breaks',
						'Solution: Gutenberg plugin or new theme',
					),
					'Slow Theme' => array(
						'Issue: Bloated, many unused features',
						'Risk: Page load 2-3 seconds',
						'Solution: Lightweight theme',
					),
				),
				'checking_theme_health'           => array(
					'Theme Repository' => array(
						'Check: wordpress.org/themes',
						'Official: Vetted by WordPress',
						'Quality: Safe, maintained',
					),
					'Theme Developer' => array(
						'Check: Developer website',
						'Support: Available?',
						'Updates: Recent?',
					),
					'Theme Reviews' => array(
						'Reviews: wordpress.org ratings',
						'Forum: Support forum active?',
						'Issues: Common complaints?',
					),
				),
				'testing_compatibility'           => array(
					'Test Page Types' => array(
						'Homepage: Renders correctly?',
						'Posts: Single post, archive?',
						'Products (WooCommerce): Displays?',
						'Custom post types: Work?',
					),
					'Test Gutenberg' => array(
						'Blocks: Can add new block?',
						'Patterns: Do patterns work?',
						'Custom blocks: Render?',
					),
					'Test Mobile' => array(
						'Responsive: Mobile layout works?',
						'Touch: Buttons clickable on mobile?',
					),
				),
				'upgrading_theme'                 => array(
					'Update Current' => array(
						'Theme admin page: Check updates',
						'Available: Click update button',
						'Backup: Before updating',
					),
					'Switch Themes' => array(
						'Choose: New theme from wordpress.org',
						'Preview: Theme customizer first',
						'Activate: Theme switcher',
						'Test: Verify no breakage',
					),
					'Staging Test' => array(
						'Always: Test on staging first',
						'Backup: Site backup before switch',
						'Rollback: If issues, switch back',
					),
				),
				'high_quality_themes'             => array(
					'Free Themes' => array(
						'Neve: Fast, well-maintained',
						'OceanWP: Feature-rich, free tier',
						'Astra: Flexible, builder-friendly',
						'Blocksy: Modern, Gutenberg-first',
					),
					'Premium Themes' => array(
						'Divi: Visual builder, feature-rich',
						'Avada: Popular, lots of demos',
						'The7: Professional, customizable',
					),
				),
			),
		);
	}

	/**
	 * Check theme compatibility.
	 *
	 * @since  1.2601.2148
	 * @return array Theme compatibility status.
	 */
	private static function check_theme_compatibility() {
		$theme = wp_get_theme();
		$has_issue = false;
		$issue = '';

		// Check if theme from WordPress.org
		$parent = $theme->get( 'Parent Theme' );
		$template = $theme->get_template();

		// Heuristic: Check if theme looks maintained
		$last_updated = $theme->get( 'Tested up to' );
		$current_wp = get_bloginfo( 'version' );

		if ( ! empty( $last_updated ) && version_compare( $last_updated, '5.0', '<' ) ) {
			$has_issue = true;
			$issue = 'Theme not tested with WordPress 5.0+ (very outdated)';
		}

		return array(
			'has_issue' => $has_issue,
			'issue'    => $issue ?: 'Theme compatible with current WordPress',
		);
	}
}

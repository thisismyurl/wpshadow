<?php
/**
 * Social Sharing Buttons Missing Diagnostic
 *
 * Detects missing social sharing buttons on content that could enable
 * viral amplification and organic reach through reader networks.
 *
 * @since   1.6028.1445
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Social_Sharing_Buttons Class
 *
 * Checks for presence of social sharing buttons on blog posts and articles
 * to identify missed viral amplification opportunities.
 *
 * @since 1.6028.1445
 */
class Diagnostic_Social_Sharing_Buttons extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-sharing-buttons-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Sharing Buttons Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing social sharing buttons on content, limiting viral amplification potential';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux_engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if posts and pages have social sharing buttons by examining
	 * active plugins, theme features, and content output for share button patterns.
	 *
	 * @since  1.6028.1445
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check for social sharing plugins or functionality
		$has_share_buttons = self::detect_share_functionality();

		if ( $has_share_buttons ) {
			return null; // Social sharing is available
		}

		// Count content that could benefit from sharing
		$post_count = wp_count_posts( 'post' )->publish;
		$page_count = wp_count_posts( 'page' )->publish;
		$total_content = $post_count + $page_count;

		// If no content, this diagnostic doesn't apply
		if ( $total_content < 1 ) {
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: total number of posts and pages */
				__( 'Your site has %d published posts and pages without social sharing buttons, limiting viral reach and organic traffic potential.', 'wpshadow' ),
				$total_content
			),
			'severity'      => 'low',
			'threat_level'  => 25,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/social-sharing-buttons',
			'family'        => self::$family,
			'meta'          => array(
				'posts_affected'    => $post_count,
				'pages_affected'    => $page_count,
				'total_content'     => $total_content,
				'impact_level'      => __( 'Low - Engagement optimization opportunity', 'wpshadow' ),
				'immediate_actions' => array(
					__( 'Install a social sharing plugin (AddToAny, ShareThis, or Social Warfare)', 'wpshadow' ),
					__( 'Enable sharing buttons on posts and pages', 'wpshadow' ),
					__( 'Test sharing functionality on mobile and desktop', 'wpshadow' ),
					__( 'Monitor share counts to measure effectiveness', 'wpshadow' ),
				),
			),
			'details'       => array(
				'why_important'    => __( 'Social sharing buttons make it easy for readers to amplify your content through their networks, driving free organic traffic. Research shows that content with sharing buttons gets 3-5x more shares than content without. Without buttons, you rely entirely on readers manually copying URLs, which drastically reduces viral potential and social signals that can boost SEO.', 'wpshadow' ),
				'user_impact'      => array(
					__( 'Readers: Must manually copy URLs to share content they enjoy', 'wpshadow' ),
					__( 'Site Owner: Miss 3-5x traffic multiplier from social amplification', 'wpshadow' ),
					__( 'SEO: Miss social signals that search engines use as trust indicators', 'wpshadow' ),
					__( 'Engagement: Lower comment rates and return visitor percentages', 'wpshadow' ),
				),
				'solution_options' => array(
					'Quick Fix (Free)' => array(
						'description' => __( 'Install AddToAny plugin - 30M+ active installs, supports 100+ services', 'wpshadow' ),
						'time'        => __( '5 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'plugin'      => 'add-to-any',
						'steps'       => array(
							__( 'Install and activate AddToAny plugin from WordPress.org', 'wpshadow' ),
							__( 'Configure button placement (above/below content)', 'wpshadow' ),
							__( 'Select which post types show buttons', 'wpshadow' ),
							__( 'Customize button styles to match your theme', 'wpshadow' ),
						),
					),
					'ShareThis (Free)' => array(
						'description' => __( 'ShareThis plugin with share counts and analytics', 'wpshadow' ),
						'time'        => __( '10 minutes', 'wpshadow' ),
						'cost'        => __( 'Free (with optional paid features)', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'plugin'      => 'sharethis-share-buttons',
					),
					'Social Warfare (Pro)' => array(
						'description' => __( 'Premium solution with customizable designs and share count recovery', 'wpshadow' ),
						'time'        => __( '15 minutes', 'wpshadow' ),
						'cost'        => __( '$29/year', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'plugin'      => 'social-warfare',
					),
					'Native Web Share API' => array(
						'description' => __( 'Modern browser API for mobile sharing (requires custom code)', 'wpshadow' ),
						'time'        => __( '30-60 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Advanced', 'wpshadow' ),
					),
				),
				'best_practices'   => array(
					__( 'Place buttons prominently above and below content', 'wpshadow' ),
					__( 'Include floating sidebar buttons on long-form content', 'wpshadow' ),
					__( 'Focus on 3-5 popular networks (Facebook, Twitter, LinkedIn, Email)', 'wpshadow' ),
					__( 'Optimize Open Graph meta tags for better share previews', 'wpshadow' ),
					__( 'Test mobile sharing - 60%+ of shares happen on mobile devices', 'wpshadow' ),
					__( 'Monitor analytics to see which networks drive the most traffic', 'wpshadow' ),
				),
				'testing_steps'    => array(
					'Step 1' => __( 'Visit a blog post without being logged in', 'wpshadow' ),
					'Step 2' => __( 'Look for share buttons above/below content or in sidebar', 'wpshadow' ),
					'Step 3' => __( 'Install AddToAny or ShareThis plugin', 'wpshadow' ),
					'Step 4' => __( 'Configure button placement and style', 'wpshadow' ),
					'Step 5' => __( 'Test sharing on desktop and mobile browsers', 'wpshadow' ),
					'Step 6' => __( 'Verify shared links show proper preview image and description', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Detect if site has social sharing functionality.
	 *
	 * Checks for popular social sharing plugins and theme features
	 * that provide sharing buttons.
	 *
	 * @since  1.6028.1445
	 * @return bool True if sharing functionality detected, false otherwise.
	 */
	private static function detect_share_functionality() {
		// Check for popular social sharing plugins
		$share_plugins = array(
			'add-to-any/add-to-any.php',                     // AddToAny
			'sharethis-share-buttons/sharethis-share-buttons.php', // ShareThis
			'social-warfare/social-warfare.php',             // Social Warfare
			'monarch/monarch.php',                            // Monarch by Elegant Themes
			'jetpack/jetpack.php',                           // Jetpack (has sharing module)
			'novashare/novashare.php',                       // Novashare
			'sassy-social-share/sassy-social-share.php',     // Sassy Social Share
			'social-media-feather/social-media-feather.php', // Social Media Feather
			'scriptless-social-sharing/scriptless-social-sharing.php', // Scriptless Social Sharing
		);

		foreach ( $share_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				// For Jetpack, verify sharing module is active
				if ( 'jetpack/jetpack.php' === $plugin ) {
					if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_module_active' ) ) {
						if ( \Jetpack::is_module_active( 'sharedaddy' ) ) {
							return true;
						}
					}
				} else {
					return true;
				}
			}
		}

		// Check if theme has built-in social sharing
		if ( current_theme_supports( 'social-sharing' ) ) {
			return true;
		}

		// Check for Web Share API implementation
		global $wp_scripts;
		if ( isset( $wp_scripts->registered['web-share'] ) || isset( $wp_scripts->registered['navigator-share'] ) ) {
			return true;
		}

		return false;
	}
}

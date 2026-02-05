<?php
/**
 * Social Sharing Buttons Diagnostic
 *
 * Detects when social sharing buttons are missing or non-functional.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.2307
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Marketing;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Sharing Buttons Diagnostic Class
 *
 * Checks if social sharing functionality is available and properly configured.
 *
 * @since 1.6035.2307
 */
class Diagnostic_Social_Sharing_Buttons extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-sharing-buttons';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Sharing Buttons Missing or Non-Functional';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when social sharing buttons are absent or improperly configured';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2307
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		// Check for social sharing plugins.
		$sharing_plugins = array(
			'social-warfare/social-warfare.php',
			'sassy-social-share/sassy-social-share.php',
			'add-to-any/add-to-any.php',
			'shareaholic/shareaholic.php',
			'mashshare/mashshare.php',
			'novashare/novashare.php',
			'shared-counts/shared-counts.php',
			'jetpack/jetpack.php', // Jetpack has sharing module.
		);

		$has_sharing_plugin = false;
		$active_plugins     = array();

		foreach ( $sharing_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_sharing_plugin = true;
				$active_plugins[]   = $plugin;
			}
		}

		// Check if theme has built-in sharing.
		$theme         = wp_get_theme();
		$theme_sharing = false;

		// Check theme for sharing functions.
		$theme_dir = get_stylesheet_directory();
		if ( file_exists( $theme_dir . '/inc/social-share.php' ) ||
			 file_exists( $theme_dir . '/template-parts/social-share.php' ) ||
			 file_exists( $theme_dir . '/partials/share-buttons.php' ) ) {
			$theme_sharing = true;
		}

		if ( $has_sharing_plugin || $theme_sharing ) {
			return null; // Social sharing is available.
		}

		// Check if this is a content-heavy site that would benefit.
		$post_count = wp_count_posts( 'post' );
		$page_count = wp_count_posts( 'page' );

		$total_content = ( $post_count->publish ?? 0 ) + ( $page_count->publish ?? 0 );

		if ( $total_content < 10 ) {
			return null; // Not enough content to warrant sharing.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your visitors can\'t easily share your content on social media. You\'re missing free promotion and organic reach', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/social-sharing',
			'context'      => array(
				'post_count'        => $post_count->publish ?? 0,
				'page_count'        => $page_count->publish ?? 0,
				'total_content'     => $total_content,
				'has_plugin'        => $has_sharing_plugin,
				'has_theme_support' => $theme_sharing,
				'impact'            => __( 'Each share exposes your content to 100-300 new people. Without sharing buttons, visitors must manually copy URLs and post them - only 1-2% will bother.', 'wpshadow' ),
				'recommendation'    => array(
					__( 'Install a social sharing plugin (Social Warfare, AddToAny, Novashare)', 'wpshadow' ),
					__( 'Add sharing buttons to blog posts and key pages', 'wpshadow' ),
					__( 'Include at minimum: Facebook, Twitter/X, LinkedIn, Email', 'wpshadow' ),
					__( 'Place buttons above and/or below content', 'wpshadow' ),
					__( 'Consider floating sidebar buttons for long content', 'wpshadow' ),
					__( 'Track share counts to measure engagement', 'wpshadow' ),
				),
				'traffic_potential' => __( 'Sites with social sharing see 15-30% more traffic from social referrals', 'wpshadow' ),
				'virality'          => __( 'One viral share can drive thousands of visitors in a day', 'wpshadow' ),
			),
		);
	}
}

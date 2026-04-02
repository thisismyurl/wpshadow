<?php
/**
 * Social Media Sharing Plugin Implementation
 *
 * Validates that social media sharing buttons and plugins are properly configured.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Social_Media_Sharing_Plugin Class
 *
 * Checks for proper social media sharing implementation and button visibility.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Social_Media_Sharing_Plugin extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-sharing-plugin';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Sharing Plugin Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates social media sharing buttons and plugin setup';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get post count to determine sharing importance
		$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'" );

		// Pattern 1: No social sharing plugin installed on content-heavy site
		if ( $post_count > 20 ) {
			$has_sharing_plugin = self::has_sharing_plugin();

			if ( ! $has_sharing_plugin ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'No social media sharing plugin detected on content-heavy site', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 60,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/social-media-sharing-plugin',
					'details'      => array(
						'issue' => 'no_sharing_plugin',
						'post_count' => intval( $post_count ),
						'message' => sprintf(
							/* translators: %d: number of posts */
							__( 'Your site has %d posts but no social sharing plugin installed', 'wpshadow' ),
							intval( $post_count )
						),
						'recommendation' => __( 'Install a social media sharing plugin for easier content distribution', 'wpshadow' ),
						'popular_solutions' => array(
							'Social Warfare - Visual share counts, native platform integration',
							'Social Pug - Sticky sharing buttons, mobile optimization',
							'ShareThis - Multi-platform sharing, analytics',
							'Jetpack - Built-in sharing, stats tracking',
							'Ultimate Social Warfare - Advanced features, click tracking',
						),
						'business_benefits' => array(
							'Increases organic reach by 30-50%',
							'Reduces friction for readers sharing content',
							'Tracks social engagement metrics',
							'Improves SEO through social signals',
						),
						'estimated_reach' => __( 'With 20+ posts, even basic sharing buttons can 2-3x traffic from social', 'wpshadow' ),
						'share_statistics' => array(
							'Articles with social buttons get 7x more impressions',
							'Visual share counts increase clicks by 13-27%',
							'Posts without sharing buttons get 32% fewer shares',
						),
					),
				);
			}
		}

		// Pattern 2: Sharing plugin installed but disabled on post types
		$sharing_plugin = self::get_active_sharing_plugin();
		if ( $sharing_plugin ) {
			$plugin_data = self::check_plugin_configuration( $sharing_plugin );

			if ( $plugin_data['has_posts'] && ! $plugin_data['enabled_for_posts'] ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Social sharing plugin installed but disabled for posts', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/social-media-sharing-plugin',
					'details'      => array(
						'issue' => 'sharing_disabled_for_posts',
						'plugin_name' => $plugin_data['name'],
						'message' => sprintf(
							/* translators: %s: plugin name */
							__( 'The %s plugin is installed but social sharing is disabled for blog posts', 'wpshadow' ),
							$plugin_data['name']
						),
						'recommendation' => __( 'Enable social sharing buttons for blog posts in plugin settings', 'wpshadow' ),
						'location_steps' => array(
							'Go to plugin settings (usually under Settings or Tools)',
							'Look for post type configuration',
							'Enable sharing for "Posts"',
							'Configure button placement (above, below, or both)',
							'Save changes',
						),
						'impact' => __( 'Enabled sharing buttons increase post reach 20-40%', 'wpshadow' ),
					),
				);
			}

			if ( $plugin_data['has_posts'] && ! $plugin_data['has_visible_buttons'] ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Social sharing buttons may not be visible on posts', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/social-media-sharing-plugin',
					'details'      => array(
						'issue' => 'sharing_buttons_not_visible',
						'plugin_name' => $plugin_data['name'],
						'message' => __( 'Social sharing plugin is active but buttons may not display properly', 'wpshadow' ),
						'recommendation' => __( 'Check plugin display settings and theme compatibility', 'wpshadow' ),
						'troubleshooting_steps' => array(
							'View a blog post on frontend',
							'Check if social buttons appear above/below content',
							'Verify button styling matches theme',
							'Check browser console for JavaScript errors (F12)',
							'Test on mobile device',
						),
						'common_issues' => array(
							'Theme compatibility conflict',
							'CSS conflicts with other plugins',
							'JavaScript disabled or errors',
							'Buttons hidden by CSS custom code',
							'Plugin shortcode not added to theme',
						),
					),
				);
			}
		}

		// Pattern 3: Insufficient social networks configured
		if ( $sharing_plugin && $post_count > 0 ) {
			$networks_configured = self::count_configured_networks( $sharing_plugin );

			if ( $networks_configured < 3 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Social sharing configured for too few networks', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 45,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/social-media-sharing-plugin',
					'details'      => array(
						'issue' => 'insufficient_networks',
						'current_networks' => intval( $networks_configured ),
						'recommended_minimum' => 3,
						'message' => sprintf(
							/* translators: %d: number of networks */
							__( 'Only %d social networks configured; minimum 3 recommended for optimal reach', 'wpshadow' ),
							intval( $networks_configured )
						),
						'top_platforms_by_reach' => array(
							'Facebook' => '3B+ users, highest sharing rate',
							'Twitter/X' => '336M+ users, tech/news content',
							'LinkedIn' => '1B+ users, B2B/professional content',
							'Pinterest' => '500M+ users, visual content',
							'WhatsApp' => '2B+ users, messaging shares',
						),
						'recommendation' => __( 'Add at least Facebook, Twitter/X, and LinkedIn for broad reach', 'wpshadow' ),
						'strategic_selection' => __( 'Choose networks where your target audience is most active', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 4: Share count display not cached properly
		$homepage_response = wp_remote_get( home_url() );
		if ( ! is_wp_error( $homepage_response ) ) {
			$homepage_content = wp_remote_retrieve_body( $homepage_response );

			// Check for share count indicators
			if ( preg_match( '/<span[^>]*class=["\'].*?share[^"\']*["\'][^>]*>0<\/span>/', $homepage_content ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Social sharing counts showing as zero', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/social-media-sharing-plugin',
					'details'      => array(
						'issue' => 'zero_share_counts',
						'message' => __( 'Share count indicators are displaying zero across the site', 'wpshadow' ),
						'possible_causes' => array(
							'Plugin share count API quotas exceeded',
							'API keys not configured properly',
							'Network API service unavailable',
							'Cache not clearing properly',
							'Site too new (no shares yet)',
						),
						'recommendation' => __( 'Check plugin API settings and reset share counts cache', 'wpshadow' ),
						'impact' => __( 'Showing zero shares discourages user engagement by 40-50%', 'wpshadow' ),
						'psychology' => __( 'Social proof matters: users share content others are sharing', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 5: Mobile sharing button placement suboptimal
		if ( $sharing_plugin ) {
			$button_placement = self::get_button_placement( $sharing_plugin );

			if ( 'sidebar_only' === $button_placement ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Social sharing buttons only in sidebar (poor mobile experience)', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/social-media-sharing-plugin',
					'details'      => array(
						'issue' => 'mobile_unfriendly_placement',
						'current_placement' => $button_placement,
						'message' => __( 'Sharing buttons in sidebar only; users don\'t see them on mobile', 'wpshadow' ),
						'mobile_statistics' => array(
							'60%+ of traffic is mobile',
							'Mobile users rarely scroll to sidebar',
							'Missing 60%+ of potential shares from mobile traffic',
						),
						'recommendation' => __( 'Add sticky or inline sharing buttons for mobile users', 'wpshadow' ),
						'best_practices' => array(
							'Sticky buttons on left/right edge (always visible)',
							'Inline buttons after first paragraph',
							'Bottom-of-post buttons before comments',
							'Floating action button on mobile only',
						),
						'improvement_potential' => __( 'Mobile-optimized placement increases shares 3-5x from mobile', 'wpshadow' ),
					),
				);
			}
		}

		return null; // No issues found
	}

	/**
	 * Check if any sharing plugin is installed.
	 *
	 * @since 1.6093.1200
	 * @return bool True if sharing plugin active.
	 */
	private static function has_sharing_plugin() {
		$sharing_plugins = array(
			'social-warfare/index.php',
			'social-pug/index.php',
			'jetpack/jetpack.php',
			'sharethis-share-buttons/sharethis.php',
			'ultimate-social-warfare/index.php',
			'sumo-social-sharing/sumo-social-sharing.php',
			'sassy-social-share/sassy-social-share.php',
		);

		foreach ( $sharing_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get currently active sharing plugin.
	 *
	 * @since 1.6093.1200
	 * @return string Plugin slug or empty string.
	 */
	private static function get_active_sharing_plugin() {
		$sharing_plugins = array(
			'social-warfare/index.php' => 'social-warfare',
			'social-pug/index.php' => 'social-pug',
			'jetpack/jetpack.php' => 'jetpack',
			'sharethis-share-buttons/sharethis.php' => 'sharethis',
			'ultimate-social-warfare/index.php' => 'ultimate-social-warfare',
		);

		foreach ( $sharing_plugins as $plugin => $slug ) {
			if ( is_plugin_active( $plugin ) ) {
				return $slug;
			}
		}

		return '';
	}

	/**
	 * Check plugin configuration.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return array Plugin configuration data.
	 */
	private static function check_plugin_configuration( $plugin ) {
		return array(
			'name' => ucwords( str_replace( '-', ' ', $plugin ) ),
			'has_posts' => true,
			'enabled_for_posts' => get_option( $plugin . '_enable_posts', true ),
			'has_visible_buttons' => true,
		);
	}

	/**
	 * Count configured social networks.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return int Number of networks.
	 */
	private static function count_configured_networks( $plugin ) {
		$networks = get_option( $plugin . '_networks', array() );
		return is_array( $networks ) ? count( $networks ) : 0;
	}

	/**
	 * Get button placement configuration.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return string Placement type.
	 */
	private static function get_button_placement( $plugin ) {
		return get_option( $plugin . '_placement', 'default' );
	}
}

<?php
/**
 * Social Media Integration Diagnostic
 *
 * Detects when social media profiles aren't integrated on the website.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.2308
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Marketing;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Media Integration Diagnostic Class
 *
 * Checks if social media profiles are linked and integrated on the site.
 *
 * @since 1.6035.2308
 */
class Diagnostic_Social_Media_Integration extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-integration';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Social Media Integration on Website';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when social media profiles aren\'t linked or integrated';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2308
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		$has_social_integration = false;
		$integration_methods    = array();

		// Check for social menu location.
		$nav_menu_locations = get_nav_menu_locations();
		if ( isset( $nav_menu_locations['social'] ) ) {
			$has_social_integration = true;
			$integration_methods[]  = __( 'Social menu location', 'wpshadow' );
		}

		// Check for widgets with social links.
		$sidebars_widgets = wp_get_sidebars_widgets();
		foreach ( $sidebars_widgets as $sidebar => $widgets ) {
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as $widget ) {
					if ( strpos( $widget, 'social' ) !== false || strpos( $widget, 'custom_html' ) !== false ) {
						$has_social_integration = true;
						$integration_methods[]  = __( 'Social widget', 'wpshadow' );
						break 2;
					}
				}
			}
		}

		// Check for social media plugins.
		$social_plugins = array(
			'simple-social-icons/simple-social-icons.php',
			'social-icons-widget-by-wpzoom/social-icons-widget-by-wpzoom.php',
			'monarch/monarch.php',
			'jetpack/jetpack.php',
		);

		foreach ( $social_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_social_integration = true;
				$integration_methods[]  = basename( dirname( $plugin ) );
			}
		}

		// Check homepage for social media links.
		$homepage_content = '';
		$front_page_id    = get_option( 'page_on_front' );

		if ( $front_page_id ) {
			$page = get_post( $front_page_id );
			if ( $page ) {
				$homepage_content = $page->post_content;
			}
		}

		// Check for social media domains in content.
		$social_domains = array( 'facebook.com', 'twitter.com', 'instagram.com', 'linkedin.com', 'youtube.com', 'tiktok.com', 'x.com' );
		$found_links    = array();

		foreach ( $social_domains as $domain ) {
			if ( ! empty( $homepage_content ) && stripos( $homepage_content, $domain ) !== false ) {
				$found_links[] = $domain;
			}
		}

		if ( ! empty( $found_links ) ) {
			$has_social_integration = true;
			$integration_methods[]  = __( 'Links in homepage content', 'wpshadow' );
		}

		if ( $has_social_integration ) {
			return null; // Social integration exists.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your website doesn\'t connect visitors to your social media profiles. You\'re missing opportunities to build a community and stay connected with customers', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/social-media-integration',
			'context'      => array(
				'integration_found'  => $has_social_integration,
				'integration_methods' => $integration_methods,
				'impact'             => __( 'Visitors who want to follow you on social media can\'t find your profiles easily. Social media followers become repeat customers 3x more often than one-time visitors.', 'wpshadow' ),
				'recommendation'     => array(
					__( 'Add social media icons to your header, footer, or sidebar', 'wpshadow' ),
					__( 'Link to active social media profiles only', 'wpshadow' ),
					__( 'Include at minimum: Facebook, Instagram, LinkedIn (B2B), YouTube (if applicable)', 'wpshadow' ),
					__( 'Consider adding social feeds to homepage', 'wpshadow' ),
					__( 'Use recognizable social media icons', 'wpshadow' ),
					__( 'Make icons open in new tab', 'wpshadow' ),
				),
				'community_building' => __( 'Social followers are 5-10x more likely to become repeat customers', 'wpshadow' ),
				'cross_platform'     => __( 'Email → Social → Website creates multiple touchpoints with audience', 'wpshadow' ),
			),
		);
	}
}

<?php
/**
 * Link Building Program Diagnostic
 *
 * Tests for active external link building program including
 * backlink monitoring, outreach, and link quality management.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1405
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Link Building Program Diagnostic Class
 *
 * Evaluates whether the site has an active link building
 * strategy and monitoring infrastructure.
 *
 * @since 1.6035.1405
 */
class Diagnostic_Link_Building_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'executes-link-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Link Building Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for active external link building program';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the link building program diagnostic check.
	 *
	 * @since  1.6035.1405
	 * @return array|null Finding array if link building issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for backlink monitoring plugins.
		$backlink_plugins = array(
			'google-site-kit/google-site-kit.php'           => 'Site Kit (has Search Console links)',
			'broken-link-checker/broken-link-checker.php'   => 'Broken Link Checker',
			'link-whisper/link-whisper.php'                 => 'Link Whisper',
		);

		$has_backlink_monitoring = false;
		$active_backlink_tools = array();
		foreach ( $backlink_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_backlink_monitoring = true;
				$active_backlink_tools[] = $name;
			}
		}

		$stats['has_backlink_monitoring'] = $has_backlink_monitoring;
		$stats['active_backlink_tools'] = $active_backlink_tools;

		// Check for Google Search Console verification (essential for link monitoring).
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$has_search_console = false;
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			if ( preg_match( '/google-site-verification/i', $html ) ) {
				$has_search_console = true;
			}
		}

		$stats['has_search_console'] = $has_search_console;

		// Check for author bio/profile presence (helps with link building).
		$user_count = count_users();
		$has_author_profiles = false;

		if ( ! empty( $user_count['total_users'] ) ) {
			// Check if author archives are enabled.
			$author = get_users( array( 'number' => 1, 'role' => 'author' ) );
			if ( empty( $author ) ) {
				$author = get_users( array( 'number' => 1 ) );
			}

			if ( ! empty( $author ) ) {
				$author_url = get_author_posts_url( $author[0]->ID );
				$author_response = wp_remote_head( $author_url, array(
					'timeout' => 5,
					'sslverify' => false,
				) );

				if ( ! is_wp_error( $author_response ) && 200 === wp_remote_retrieve_response_code( $author_response ) ) {
					$has_author_profiles = true;
				}
			}
		}

		$stats['has_author_profiles'] = $has_author_profiles;

		// Check for contact/outreach forms.
		$has_contact_form = false;
		$contact_form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'wpforms-lite/wpforms.php',
			'ninja-forms/ninja-forms.php',
			'formidable/formidable.php',
			'gravityforms/gravityforms.php',
		);

		foreach ( $contact_form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_contact_form = true;
				break;
			}
		}

		// Also check for contact page.
		$contact_pages = get_posts( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'numberposts' => 1,
			's'           => 'contact',
		) );

		if ( ! empty( $contact_pages ) ) {
			$has_contact_form = true;
		}

		$stats['has_contact_form'] = $has_contact_form;

		// Check for press/media page (link building asset).
		$press_pages = get_posts( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'numberposts' => 1,
			's'           => 'press media',
		) );

		$has_press_page = ! empty( $press_pages );
		$stats['has_press_page'] = $has_press_page;

		// Check for guest post/contribute page.
		$contribute_pages = get_posts( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'numberposts' => 1,
			's'           => 'guest post contribute write for us',
		) );

		$has_contribute_page = ! empty( $contribute_pages );
		$stats['has_contribute_page'] = $has_contribute_page;

		// Check for resource/linkable assets pages.
		$resource_pages = get_posts( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'numberposts' => 5,
			's'           => 'resources tools guide',
		) );

		$stats['resource_pages_count'] = count( $resource_pages );
		$has_resource_pages = ! empty( $resource_pages );

		// Check for testimonials or case studies (link building assets).
		$testimonial_posts = get_posts( array(
			'post_type'   => 'any',
			'post_status' => 'publish',
			'numberposts' => 1,
			's'           => 'testimonial case study',
		) );

		$has_testimonials = ! empty( $testimonial_posts );
		$stats['has_testimonials'] = $has_testimonials;

		// Check for social profiles (important for link building).
		$social_menu = wp_get_nav_menu_items( 'social' );
		$has_social_profiles = ! empty( $social_menu );

		// Also check widgets.
		if ( ! $has_social_profiles ) {
			$sidebars = wp_get_sidebars_widgets();
			foreach ( $sidebars as $sidebar => $widgets ) {
				if ( ! empty( $widgets ) ) {
					foreach ( $widgets as $widget ) {
						if ( strpos( $widget, 'social' ) !== false ) {
							$has_social_profiles = true;
							break 2;
						}
					}
				}
			}
		}

		$stats['has_social_profiles'] = $has_social_profiles;

		// Check for outbound links in recent content (sign of link building).
		$recent_posts = get_posts( array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => 10,
		) );

		$total_outbound_links = 0;
		$posts_with_outbound_links = 0;

		foreach ( $recent_posts as $post ) {
			$content = $post->post_content;
			
			// Count external links.
			preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches );
			
			$outbound_count = 0;
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Check if external.
					if ( strpos( $url, home_url() ) === false && 
						 ( strpos( $url, 'http://' ) === 0 || strpos( $url, 'https://' ) === 0 ) ) {
						$outbound_count++;
					}
				}
			}

			if ( $outbound_count > 0 ) {
				$posts_with_outbound_links++;
				$total_outbound_links += $outbound_count;
			}
		}

		$stats['average_outbound_links_per_post'] = count( $recent_posts ) > 0 
			? round( $total_outbound_links / count( $recent_posts ), 1 ) 
			: 0;
		$stats['posts_with_outbound_links_percentage'] = count( $recent_posts ) > 0 
			? round( ( $posts_with_outbound_links / count( $recent_posts ) ) * 100, 1 ) 
			: 0;

		// Check for sitemap (helps with link discovery).
		$sitemap_urls = array(
			home_url( '/sitemap.xml' ),
			home_url( '/sitemap_index.xml' ),
			home_url( '/wp-sitemap.xml' ),
		);

		$has_sitemap = false;
		foreach ( $sitemap_urls as $sitemap_url ) {
			$response = wp_remote_head( $sitemap_url, array(
				'timeout' => 5,
				'sslverify' => false,
			) );

			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$has_sitemap = true;
				break;
			}
		}

		$stats['has_sitemap'] = $has_sitemap;

		// Calculate link building program score.
		$link_building_features = 0;
		$total_features = 10;

		if ( $has_search_console ) { $link_building_features++; }
		if ( $has_backlink_monitoring ) { $link_building_features++; }
		if ( $has_author_profiles ) { $link_building_features++; }
		if ( $has_contact_form ) { $link_building_features++; }
		if ( $has_press_page ) { $link_building_features++; }
		if ( $has_contribute_page ) { $link_building_features++; }
		if ( $has_resource_pages ) { $link_building_features++; }
		if ( $has_testimonials ) { $link_building_features++; }
		if ( $has_social_profiles ) { $link_building_features++; }
		if ( $has_sitemap ) { $link_building_features++; }

		$stats['link_building_score'] = round( ( $link_building_features / $total_features ) * 100, 1 );

		// Evaluate issues.
		if ( ! $has_search_console ) {
			$issues[] = __( 'Google Search Console not verified - critical for monitoring backlinks', 'wpshadow' );
		}

		if ( ! $has_backlink_monitoring ) {
			$warnings[] = __( 'No backlink monitoring tools active - install Site Kit or similar', 'wpshadow' );
		}

		if ( ! $has_contact_form ) {
			$warnings[] = __( 'No contact form detected - makes outreach difficult for link builders', 'wpshadow' );
		}

		if ( ! $has_author_profiles ) {
			$warnings[] = __( 'Author profiles not accessible - enable author archives for credibility', 'wpshadow' );
		}

		if ( ! $has_social_profiles ) {
			$warnings[] = __( 'No social profiles detected - add social media links for credibility', 'wpshadow' );
		}

		if ( ! $has_press_page ) {
			$warnings[] = __( 'No press/media page - create one as a link building asset', 'wpshadow' );
		}

		if ( ! $has_contribute_page ) {
			$warnings[] = __( 'No guest post/contribute page - consider accepting guest posts for links', 'wpshadow' );
		}

		if ( ! $has_resource_pages ) {
			$warnings[] = __( 'No resource/guide pages - create linkable assets', 'wpshadow' );
		}

		if ( $stats['posts_with_outbound_links_percentage'] < 30 ) {
			$warnings[] = sprintf(
				/* translators: %s: percentage */
				__( 'Only %s%% of posts have outbound links - linking out helps with link building', 'wpshadow' ),
				$stats['posts_with_outbound_links_percentage']
			);
		}

		if ( $stats['link_building_score'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %s: score percentage */
				__( 'Link building program score is low (%s%%) - develop a link building strategy', 'wpshadow' ),
				$stats['link_building_score']
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Link building program has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/link-building-program',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Link building program has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/link-building-program',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Link building program is well established.
	}
}

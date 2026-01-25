<?php
/**
 * Treatment Hooks - Apply active treatments
 *
 * This file hooks into WordPress to actually apply the treatments
 * when their options are enabled.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to apply active treatments.
 */
class Treatment_Hooks {

	/**
	 * Initialize treatment hooks.
	 */
	public static function init() {
		// Admin Fonts
		if ( get_option( 'wpshadow_admin_fonts_disabled', false ) ) {
			add_action( 'admin_init', array( __CLASS__, 'disable_admin_fonts' ) );
		}

		// RSS Feeds
		if ( get_option( 'wpshadow_rss_feeds_disabled', false ) ) {
			remove_action( 'wp_head', 'feed_links', 2 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}

		// REST API Headers
		if ( get_option( 'wpshadow_rest_api_headers_disabled', false ) ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
			remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
		}

		// WP Generator Tag
		if ( get_option( 'wpshadow_wp_generator_disabled', false ) ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_empty_string' );
		}

		// Emoji Scripts
		if ( get_option( 'wpshadow_emoji_scripts_disabled', false ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'tiny_mce_plugins', array( __CLASS__, 'disable_emojis_tinymce' ) );
			add_filter( 'wp_resource_hints', array( __CLASS__, 'disable_emojis_dns_prefetch' ), 10, 2 );
		}

		// External Fonts (frontend)
		if ( get_option( 'wpshadow_block_external_fonts', false ) ) {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'block_external_fonts' ), 999 );
			// Also strip Google Fonts resource hints to avoid unnecessary DNS lookups.
			add_filter( 'wp_resource_hints', array( __CLASS__, 'filter_resource_hints_fonts' ), 10, 2 );
		}

		// Head Cleanup
		if ( get_option( 'wpshadow_head_cleanup_enabled', false ) ) {
			// Remove RSD link
			remove_action( 'wp_head', 'rsd_link' );
			// Remove Windows Live Writer link
			remove_action( 'wp_head', 'wlwmanifest_link' );
			// Remove shortlink
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
			// Remove oEmbed discovery
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		}

		// HTML Cleanup
		if ( get_option( 'wpshadow_html_cleanup_enabled', false ) ) {
			add_action( 'template_redirect', array( __CLASS__, 'start_html_cleanup' ) );
		}

		// Strip ALL resource hints (dns-prefetch, preconnect, preload, prefetch)
		if ( get_option( 'wpshadow_strip_resource_hints', false ) ) {
			add_filter( 'wp_resource_hints', array( __CLASS__, 'strip_all_resource_hints' ), 999, 2 );
		}

		// Strip Speculation Rules scripts
		if ( get_option( 'wpshadow_strip_speculationrules', false ) ) {
			add_action( 'template_redirect', array( __CLASS__, 'start_strip_speculationrules' ) );
		}

		// Strip JSON-LD schema scripts
		if ( get_option( 'wpshadow_strip_json_ld', false ) ) {
			add_action( 'template_redirect', array( __CLASS__, 'start_strip_json_ld' ) );
		}

		// Strip OpenGraph/Twitter social meta tags
		if ( get_option( 'wpshadow_strip_social_meta', false ) ) {
			add_action( 'template_redirect', array( __CLASS__, 'start_strip_social_meta' ) );
		}

		// Block common analytics hosts (scripts, resource hints, and obvious pixels)
		if ( get_option( 'wpshadow_block_analytics_hosts', false ) ) {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'block_analytics_scripts' ), 1000 );
			add_filter( 'script_loader_tag', array( __CLASS__, 'filter_script_loader_tag_block_analytics' ), 10, 3 );
			add_filter( 'wp_resource_hints', array( __CLASS__, 'filter_resource_hints_block_analytics' ), 10, 2 );
			add_action( 'template_redirect', array( __CLASS__, 'start_strip_analytics_tags' ) );
		}

		// Disable jQuery Migrate on frontend if requested
		if ( get_option( 'wpshadow_disable_jquery_migrate', false ) ) {
			add_action( 'wp_default_scripts', array( __CLASS__, 'disable_jquery_migrate_core' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_jquery_migrate' ), 1000 );
		}

		// CSS Class Cleanup
		if ( get_option( 'wpshadow_css_class_cleanup_enabled', false ) ) {
			add_filter( 'body_class', array( __CLASS__, 'cleanup_body_classes' ), 999 );
			add_filter( 'post_class', array( __CLASS__, 'cleanup_post_classes' ), 999 );
			add_filter( 'nav_menu_css_class', array( __CLASS__, 'cleanup_nav_classes' ), 999 );
		}

		// Image Lazy Load
		if ( get_option( 'wpshadow_force_lazyload', false ) ) {
			add_filter( 'wp_lazy_loading_enabled', '__return_true' );
			add_filter( 'wp_img_tag_add_loading_attr', array( __CLASS__, 'force_lazy_loading' ), 10, 3 );
		}

		// Embed Disable
		if ( get_option( 'wpshadow_embed_disable_enabled', false ) ) {
			add_action( 'wp_footer', array( __CLASS__, 'dequeue_embed_script' ) );
		}

		// Navigation ARIA
		if ( get_option( 'wpshadow_nav_accessibility_enabled', false ) ) {
			add_filter( 'nav_menu_link_attributes', array( __CLASS__, 'add_nav_aria_current' ), 10, 4 );
		}

		// Paste Cleanup (handled in editor, not frontend)
		// Content Optimizer (handled in editor, not frontend)
	}

	/**
	 * Disable Google Fonts in admin.
	 */
	public static function disable_admin_fonts() {
		wp_deregister_style( 'open-sans' );
		wp_register_style( 'open-sans', false );
		wp_enqueue_style( 'open-sans' );
	}

	/**
	 * Disable emoji TinyMCE plugin.
	 *
	 * @param array $plugins TinyMCE plugins.
	 * @return array
	 */
	public static function disable_emojis_tinymce( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		}
		return array();
	}

	/**
	 * Remove emoji DNS prefetch.
	 *
	 * @param array  $urls          URLs to prefetch.
	 * @param string $relation_type Relation type.
	 * @return array
	 */
	public static function disable_emojis_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
			$urls          = array_diff( $urls, array( $emoji_svg_url ) );
		}
		return $urls;
	}

	/**
	 * Block external font loading.
	 */
	public static function block_external_fonts() {
		global $wp_styles;
		if ( ! isset( $wp_styles->registered ) ) {
			return;
		}

		foreach ( $wp_styles->registered as $handle => $style ) {
			if ( isset( $style->src ) && is_string( $style->src ) ) {
				if ( strpos( $style->src, 'fonts.googleapis.com' ) !== false ||
					strpos( $style->src, 'fonts.gstatic.com' ) !== false ) {
					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
				}
			}
		}
	}

	/**
	 * Remove Google Fonts resource hints (dns-prefetch/preconnect) when blocking external fonts.
	 *
	 * @param array  $urls          URLs to hint.
	 * @param string $relation_type Relation type.
	 * @return array
	 */
	public static function filter_resource_hints_fonts( $urls, $relation_type ) {
		if ( ! is_array( $urls ) || empty( $urls ) ) {
			return $urls;
		}
		if ( in_array( $relation_type, array( 'dns-prefetch', 'preconnect' ), true ) ) {
			$urls = array_values(
				array_filter(
					$urls,
					function ( $url ) {
						return ( false === strpos( (string) $url, 'fonts.googleapis.com' ) && false === strpos( (string) $url, 'fonts.gstatic.com' ) );
					}
				)
			);
		}
		return $urls;
	}

	/**
	 * Start HTML output buffering for cleanup.
	 */
	public static function start_html_cleanup() {
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		ob_start( array( __CLASS__, 'cleanup_html_output' ) );
	}

	/**
	 * Remove jquery-migrate dependency from core jQuery registration on frontend.
	 *
	 * @param \WP_Scripts $scripts WP Scripts instance.
	 */
	public static function disable_jquery_migrate_core( $scripts ) {
		if ( is_admin() ) {
			return;
		}
		if ( isset( $scripts->registered['jquery'] ) && isset( $scripts->registered['jquery']->deps ) && is_array( $scripts->registered['jquery']->deps ) ) {
			$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
		}
	}

	/**
	 * Dequeue/deregister jquery-migrate if enqueued separately by a theme/plugin.
	 */
	public static function dequeue_jquery_migrate() {
		if ( is_admin() ) {
			return;
		}
		if ( wp_script_is( 'jquery-migrate', 'enqueued' ) || wp_script_is( 'jquery-migrate', 'registered' ) ) {
			wp_dequeue_script( 'jquery-migrate' );
			wp_deregister_script( 'jquery-migrate' );
		}
	}

	/**
	 * Clean up HTML output.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public static function cleanup_html_output( $html ) {
		// Remove HTML comments (but not IE conditional comments)
		$html = preg_replace( '/<!--(?!\[if\s)(?!<!)[^\[>].*?-->/s', '', $html );
		// Remove whitespace between tags
		$html = preg_replace( '/>\s+</', '><', $html );
		// Remove outdated X-UA-Compatible meta (legacy IE directive)
		$html = preg_replace( '/<meta[^>]*http-equiv\s*=\s*(["\'])X-UA-Compatible\1[^>]*>/i', '', $html );
		return $html;
	}

	/**
	 * Strip ALL resource hints regardless of relation type.
	 *
	 * @param array  $urls          URLs to hint.
	 * @param string $relation_type Relation type.
	 * @return array
	 */
	public static function strip_all_resource_hints( $urls, $relation_type ) {
		return array();
	}

	/**
	 * Start buffer to strip speculationrules scripts.
	 */
	public static function start_strip_speculationrules() {
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		ob_start( array( __CLASS__, 'strip_speculationrules_output' ) );
	}

	/**
	 * Remove <script type="speculationrules"> blocks.
	 */
	public static function strip_speculationrules_output( $html ) {
		return preg_replace( '/<script[^>]*type\s*=\s*("|")speculationrules\1[^>]*>.*?<\/script>/is', '', $html );
	}

	/**
	 * Start buffer to strip JSON-LD schema.
	 */
	public static function start_strip_json_ld() {
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		ob_start( array( __CLASS__, 'strip_json_ld_output' ) );
	}

	/**
	 * Remove <script type="application/ld+json"> blocks.
	 */
	public static function strip_json_ld_output( $html ) {
		return preg_replace( '/<script[^>]*type\s*=\s*("|")application\/ld\+json\1[^>]*>.*?<\/script>/is', '', $html );
	}

	/**
	 * Start buffer to strip OpenGraph/Twitter meta tags.
	 */
	public static function start_strip_social_meta() {
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		ob_start( array( __CLASS__, 'strip_social_meta_output' ) );
	}

	/**
	 * Remove OG/Twitter meta tags.
	 */
	public static function strip_social_meta_output( $html ) {
		return preg_replace( '/<meta[^>]*(property|name)\s*=\s*("|")(og:[^"\']+|twitter:[^"\']+)\2[^>]*>/i', '', $html );
	}

	/**
	 * Get configured analytics hosts to block.
	 *
	 * @return array
	 */
	private static function get_blocked_analytics_hosts() {
		$hosts = get_option( 'wpshadow_analytics_hosts', array() );
		if ( ! is_array( $hosts ) ) {
			$hosts = array();
		}
		/**
		 * Allow filtering of analytics host blocklist at runtime.
		 *
		 * @param array $hosts Hostnames to block.
		 */
		$hosts = apply_filters( 'wpshadow_analytics_hosts', $hosts );
		return array_values( array_filter( array_map( 'strval', $hosts ) ) );
	}

	/**
	 * Check if a URL should be blocked based on configured analytics hosts.
	 *
	 * @param string $url URL to check.
	 * @return bool
	 */
	private static function is_blocked_analytics_url( $url ) {
		if ( ! is_string( $url ) || $url === '' ) {
			return false;
		}
		$hosts = self::get_blocked_analytics_hosts();
		if ( empty( $hosts ) ) {
			return false;
		}
		$host = parse_url( $url, PHP_URL_HOST );
		if ( ! is_string( $host ) || $host === '' ) {
			return false;
		}
		$host = strtolower( $host );
		foreach ( $hosts as $blocked ) {
			$blocked = strtolower( $blocked );
			if ( $blocked === $host ) {
				return true;
			}
			// Suffix match: block subdomains like www.googletagmanager.com when list has googletagmanager.com.
			if ( substr( $host, -strlen( $blocked ) ) === $blocked ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Dequeue/deregister scripts registered or enqueued from blocked analytics hosts.
	 */
	public static function block_analytics_scripts() {
		if ( is_admin() ) {
			return;
		}
		global $wp_scripts;
		if ( ! isset( $wp_scripts->registered ) || ! is_array( $wp_scripts->registered ) ) {
			return;
		}
		foreach ( $wp_scripts->registered as $handle => $script ) {
			$src = isset( $script->src ) ? (string) $script->src : '';
			if ( $src && self::is_blocked_analytics_url( $src ) ) {
				wp_dequeue_script( $handle );
				wp_deregister_script( $handle );
			}
		}
	}

	/**
	 * Remove script tag output if its src is on a blocked analytics host.
	 *
	 * @param string $tag    The `<script>` tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @param string $src    The script's source URL.
	 * @return string
	 */
	public static function filter_script_loader_tag_block_analytics( $tag, $handle, $src ) {
		if ( self::is_blocked_analytics_url( (string) $src ) ) {
			return '';
		}
		return $tag;
	}

	/**
	 * Remove resource hints that reference blocked analytics hosts.
	 *
	 * @param array  $urls          URLs to hint.
	 * @param string $relation_type Relation type.
	 * @return array
	 */
	public static function filter_resource_hints_block_analytics( $urls, $relation_type ) {
		if ( ! is_array( $urls ) || empty( $urls ) ) {
			return $urls;
		}
		return array_values(
			array_filter(
				$urls,
				function ( $url ) {
					return ! self::is_blocked_analytics_url( (string) $url );
				}
			)
		);
	}

	/**
	 * Start buffer to strip obvious blocked analytics tags rendered outside WP enqueue system.
	 */
	public static function start_strip_analytics_tags() {
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		ob_start( array( __CLASS__, 'strip_analytics_tags_output' ) );
	}

	/**
	 * Strip <script src> and <img src> tags that point to blocked analytics hosts.
	 * Conservative removal: only external src matches.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public static function strip_analytics_tags_output( $html ) {
		$hosts = self::get_blocked_analytics_hosts();
		if ( empty( $hosts ) ) {
			return $html;
		}
		$escaped       = array_map(
			function ( $h ) {
				return preg_quote( $h, '/' );
			},
			$hosts
		);
		$pattern_hosts = implode( '|', $escaped );
		// Remove script tags with src to blocked hosts.
		$html = preg_replace( "/<script[^>]*\\bsrc\\s*=\\s*(\"|')[^\"']*(?:$pattern_hosts)[^\"']*\\1[^>]*>\\s*<\\/script>/i", '', $html );
		// Remove tracking pixels (img) to blocked hosts.
		$html = preg_replace( "/<img[^>]*\\bsrc\\s*=\\s*(\"|')[^\"']*(?:$pattern_hosts)[^\"']*\\1[^>]*>/i", '', $html );
		return $html;
	}

	/**
	 * Clean up body classes.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public static function cleanup_body_classes( $classes ) {
		// Keep only essential classes
		$keep = array( 'home', 'blog', 'archive', 'single', 'page', 'logged-in', 'admin-bar' );
		return array_intersect( $classes, $keep );
	}

	/**
	 * Clean up post classes.
	 *
	 * @param array $classes Post classes.
	 * @return array
	 */
	public static function cleanup_post_classes( $classes ) {
		// Keep only essential classes
		$keep     = array( 'post', 'page', 'attachment', 'type-post', 'type-page', 'status-publish' );
		$filtered = array();
		foreach ( $classes as $class ) {
			foreach ( $keep as $pattern ) {
				if ( strpos( $class, $pattern ) === 0 ) {
					$filtered[] = $class;
					break;
				}
			}
		}
		return $filtered;
	}

	/**
	 * Clean up navigation classes.
	 *
	 * @param array $classes Nav menu classes.
	 * @return array
	 */
	public static function cleanup_nav_classes( $classes ) {
		// Keep only essential classes
		$keep = array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor', 'menu-item' );
		return array_intersect( $classes, $keep );
	}

	/**
	 * Force lazy loading on images.
	 *
	 * @param string $value   Loading attribute value.
	 * @param string $image   Image HTML.
	 * @param string $context Context.
	 * @return string
	 */
	public static function force_lazy_loading( $value, $image, $context ) {
		return 'lazy';
	}

	/**
	 * Dequeue embed script.
	 */
	public static function dequeue_embed_script() {
		wp_deregister_script( 'wp-embed' );
	}

	/**
	 * Add ARIA current to navigation links.
	 *
	 * @param array    $atts  Link attributes.
	 * @param object   $item  Menu item.
	 * @param stdClass $args  Menu args.
	 * @param int      $depth Depth.
	 * @return array
	 */
	public static function add_nav_aria_current( $atts, $item, $args, $depth ) {
		if ( in_array( 'current-menu-item', $item->classes, true ) ||
			in_array( 'current-menu-parent', $item->classes, true ) ||
			in_array( 'current-menu-ancestor', $item->classes, true ) ) {
			$atts['aria-current'] = 'page';
		}
		return $atts;
	}
}

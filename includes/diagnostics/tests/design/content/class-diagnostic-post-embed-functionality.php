<?php
/**
 * Post Embed Functionality Diagnostic
 *
 * Verifies oEmbed embeds work in posts (YouTube, Twitter, etc.). Tests embed
 * providers and validates embed rendering.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Embed Functionality Diagnostic Class
 *
 * Checks for issues with oEmbed functionality in posts.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Post_Embed_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-embed-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Embed Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates oEmbed functionality and embed provider configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if oEmbed is enabled.
		require_once ABSPATH . WPINC . '/class-wp-oembed.php';
		$oembed = _wp_oembed_get_object();

		if ( ! $oembed ) {
			$issues[] = __( 'oEmbed object not initialized (core WordPress issue)', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-embed-functionality',
			);
		}

		// Get registered oEmbed providers.
		$providers = $oembed->providers;
		
		if ( empty( $providers ) ) {
			$issues[] = __( 'No oEmbed providers registered (embeds will not work)', 'wpshadow' );
		} elseif ( count( $providers ) < 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of providers */
				__( 'Only %d oEmbed providers registered (expected 20+)', 'wpshadow' ),
				count( $providers )
			);
		}

		// Check for critical providers (YouTube, Vimeo, Twitter, etc.).
		$critical_providers = array(
			'youtube.com',
			'youtu.be',
			'vimeo.com',
			'twitter.com',
			'instagram.com',
			'facebook.com',
		);

		$missing_providers = array();
		foreach ( $critical_providers as $provider_domain ) {
			$found = false;
			foreach ( $providers as $pattern => $provider_data ) {
				if ( strpos( $pattern, $provider_domain ) !== false ) {
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				$missing_providers[] = $provider_domain;
			}
		}

		if ( ! empty( $missing_providers ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of missing providers */
				__( 'Critical oEmbed providers missing: %s', 'wpshadow' ),
				implode( ', ', $missing_providers )
			);
		}

		// Find posts with embed URLs.
		$posts_with_embeds = $wpdb->get_results(
			"SELECT ID, post_title, post_content
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			AND (
				post_content LIKE '%youtube.com%'
				OR post_content LIKE '%youtu.be%'
				OR post_content LIKE '%vimeo.com%'
				OR post_content LIKE '%twitter.com%'
				OR post_content LIKE '%instagram.com%'
			)
			LIMIT 50",
			ARRAY_A
		);

		if ( ! empty( $posts_with_embeds ) ) {
			// Check if embeds are likely to work.
			$unprocessed_urls = 0;
			$naked_urls = 0;

			foreach ( $posts_with_embeds as $post ) {
				$content = $post['post_content'];

				// Check for URLs in paragraphs (should auto-embed).
				if ( preg_match( '/<p>\s*(https?:\/\/(www\.)?(youtube\.com|youtu\.be|vimeo\.com|twitter\.com)\/[^\s<]+)\s*<\/p>/', $content ) ) {
					++$naked_urls;
				}

				// Check for URLs in [embed] shortcode that might not process.
				if ( preg_match( '/\[embed\][^\]]*\[\/embed\]/', $content ) ) {
					// This is fine, proper usage.
					continue;
				}

				// Check for bare URLs not in paragraphs or embed tags.
				if ( preg_match( '/https?:\/\/(www\.)?(youtube\.com|youtu\.be|vimeo\.com|twitter\.com)\/[^\s<]+/', $content ) &&
				     ! preg_match( '/<p>.*https?:\/\/(www\.)?(youtube\.com|youtu\.be|vimeo\.com)/', $content ) &&
				     ! preg_match( '/\[embed\].*https?:\/\/(www\.)?(youtube\.com|youtu\.be|vimeo\.com)/', $content ) ) {
					++$unprocessed_urls;
				}
			}

			if ( $unprocessed_urls > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts with unprocessed URLs */
					__( '%d posts have embed URLs not properly formatted (may not auto-embed)', 'wpshadow' ),
					$unprocessed_urls
				);
			}
		}

		// Check if autoembed is enabled.
		$autoembed_enabled = has_filter( 'the_content', array( $GLOBALS['wp_embed'], 'autoembed' ) );
		
		if ( ! $autoembed_enabled ) {
			$issues[] = __( 'Auto-embed disabled on the_content (URLs will not convert to embeds)', 'wpshadow' );
		}

		// Check if run_shortcode is enabled.
		$run_shortcode_enabled = has_filter( 'the_content', array( $GLOBALS['wp_embed'], 'run_shortcode' ) );
		
		if ( ! $run_shortcode_enabled ) {
			$issues[] = __( '[embed] shortcode handler disabled (embed shortcodes will not work)', 'wpshadow' );
		}

		// Test a sample embed.
		$test_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
		$cached_result = wp_oembed_get( $test_url );
		
		if ( false === $cached_result ) {
			$issues[] = __( 'Sample YouTube embed test failed (oEmbed may be broken)', 'wpshadow' );
		}

		// Check for oembed_result filter modifications.
		$oembed_filters = $GLOBALS['wp_filter']['oembed_result'] ?? null;
		if ( $oembed_filters && count( $oembed_filters->callbacks ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on oembed_result (may break embed rendering)', 'wpshadow' ),
				count( $oembed_filters->callbacks )
			);
		}

		// Check if embed_oembed_html filter is heavily modified.
		$embed_html_filters = $GLOBALS['wp_filter']['embed_oembed_html'] ?? null;
		if ( $embed_html_filters && count( $embed_html_filters->callbacks ) > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of filters */
				__( '%d filters on embed_oembed_html (potential embed conflicts)', 'wpshadow' ),
				count( $embed_html_filters->callbacks )
			);
		}

		// Check for posts with [embed] shortcodes.
		$embed_shortcodes = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_content LIKE '%[embed]%'"
		);

		if ( $embed_shortcodes > 0 ) {
			// Verify embed shortcode is registered.
			if ( ! shortcode_exists( 'embed' ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts using embed shortcode */
					__( '%d posts use [embed] shortcode but it is not registered', 'wpshadow' ),
					$embed_shortcodes
				);
			}
		}

		// Check if oEmbed discovery is disabled.
		$discovery_enabled = get_option( 'embed_autourls', true );
		if ( ! $discovery_enabled ) {
			$issues[] = __( 'oEmbed auto-discovery disabled (some embeds may not work)', 'wpshadow' );
		}

		// Check for excessive embed cache size.
		$embed_cache_count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_key LIKE '_oembed_%'"
		);

		if ( $embed_cache_count > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of cached embeds */
				__( '%d oEmbed cache entries (consider cleanup for performance)', 'wpshadow' ),
				$embed_cache_count
			);
		}

		// Check for failed embed attempts (cached failures).
		$failed_embeds = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_key LIKE '_oembed_%%'
				AND meta_value = %s",
				'{{unknown}}'
			)
		);

		if ( $failed_embeds > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failed embeds */
				__( '%d cached embed failures (URLs that could not be embedded)', 'wpshadow' ),
				$failed_embeds
			);
		}

		// Check if wp_oembed_add_provider filter is used.
		$add_provider_filters = $GLOBALS['wp_filter']['wp_oembed_add_provider'] ?? null;
		if ( $add_provider_filters && count( $add_provider_filters->callbacks ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom providers */
				__( '%d custom oEmbed providers registered (verify they work)', 'wpshadow' ),
				count( $add_provider_filters->callbacks )
			);
		}

		// Check for iframes that might be embeds.
		$iframe_count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_content LIKE '%<iframe%'"
		);

		if ( $iframe_count > 10 && empty( $posts_with_embeds ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with iframes */
				__( '%d posts use manual iframes (could use oEmbed instead)', 'wpshadow' ),
				$iframe_count
			);
		}

		// Check if oembed_ttl filter is set too low or too high.
		$ttl_filters = $GLOBALS['wp_filter']['oembed_ttl'] ?? null;
		if ( $ttl_filters && count( $ttl_filters->callbacks ) > 0 ) {
			// Test TTL value.
			$test_ttl = apply_filters( 'oembed_ttl', DAY_IN_SECONDS, 'https://youtube.com/test', array(), 0 );
			
			if ( $test_ttl < HOUR_IN_SECONDS ) {
				$issues[] = __( 'oEmbed cache TTL very short (frequent re-fetching, performance issue)', 'wpshadow' );
			} elseif ( $test_ttl > MONTH_IN_SECONDS ) {
				$issues[] = __( 'oEmbed cache TTL very long (stale embed data may persist)', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-embed-functionality',
			);
		}

		return null;
	}
}

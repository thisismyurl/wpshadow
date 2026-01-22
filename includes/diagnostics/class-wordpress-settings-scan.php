<?php
declare(strict_types=1);
/**
 * WPShadow WordPress Settings Scan
 *
 * Validates WordPress configuration against best practices including:
 * - General settings (site URL, timezone, admin email)
 * - Writing settings
 * - Reading settings (posts per page, visibility)
 * - Discussion settings (comment moderation)
 * - Media settings
 * - Permalinks (URL structure)
 * - Privacy settings
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WordPress_Settings_Scan {

	/**
	 * Get all WordPress settings findings.
	 *
	 * @return array Array of finding arrays with 'id', 'title', 'description', 'severity', 'category', etc.
	 */
	public static function run_scan() {
		$findings = array();

		// General Settings Tests
		$findings = array_merge( $findings, self::test_site_url() );
		$findings = array_merge( $findings, self::test_timezone() );
		$findings = array_merge( $findings, self::test_admin_email() );

		// Reading Settings Tests
		$findings = array_merge( $findings, self::test_search_engine_visibility() );
		$findings = array_merge( $findings, self::test_posts_per_page() );
		$findings = array_merge( $findings, self::test_homepage_setup() );

		// Writing Settings Tests
		$findings = array_merge( $findings, self::test_writing_default_category() );
		$findings = array_merge( $findings, self::test_writing_default_format() );
		$findings = array_merge( $findings, self::test_post_revisions() );

		// Discussion Settings Tests
		$findings = array_merge( $findings, self::test_comment_moderation() );
		$findings = array_merge( $findings, self::test_default_comments() );
		$findings = array_merge( $findings, self::test_pingbacks_trackbacks() );

		// Permalink Settings Tests
		$findings = array_merge( $findings, self::test_permalink_structure() );

		// Privacy Settings Tests
		$findings = array_merge( $findings, self::test_privacy_policy() );

		// User Registration Tests
		$findings = array_merge( $findings, self::test_user_registration() );
		$findings = array_merge( $findings, self::test_admin_username() );
		$findings = array_merge( $findings, self::test_multiple_admins() );

		// Media Settings Tests
		$findings = array_merge( $findings, self::test_media_sizes() );
		$findings = array_merge( $findings, self::test_media_organize_uploads() );

		// Appearance Tests
		$findings = array_merge( $findings, self::test_site_icon() );
		$findings = array_merge( $findings, self::test_menus_assigned() );

		// Content Tests
		$findings = array_merge( $findings, self::test_draft_posts() );
		$findings = array_merge( $findings, self::test_published_content() );

		// Tools Tests
		$findings = array_merge( $findings, self::test_debug_mode() );
		$findings = array_merge( $findings, self::test_xmlrpc_enabled() );

		return $findings;
	}

	/**
	 * Test: Site URL is using HTTPS
	 */
	private static function test_site_url() {
		$site_url = get_option( 'siteurl' );
		$home_url = get_option( 'home' );

		if ( strpos( $site_url, 'https://' ) === 0 && strpos( $home_url, 'https://' ) === 0 ) {
			return array(
				array(
					'id'           => 'site-url-https',
					'title'        => __( 'Site URL - HTTPS Enabled', 'wpshadow' ),
					'description'  => __( 'Your site URL is configured to use HTTPS, which is essential for security and SEO.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'settings',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-shield-alt',
				),
			);
		}

		return array(
			array(
				'id'           => 'site-url-https',
				'title'        => __( 'Site URL - HTTPS Not Enabled', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: Current site URL */
					__( 'Your site is not using HTTPS. Current URL: %s. HTTPS is critical for security and SEO. Contact your hosting provider about SSL certificates.', 'wpshadow' ),
					esc_html( $site_url )
				),
				'severity'     => 'critical',
				'category'     => 'security',
				'threat_level' => 95,
				'color'        => '#f44336',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Test: Timezone is configured
	 */
	private static function test_timezone() {
		$timezone = get_option( 'timezone_string' );

		if ( ! empty( $timezone ) && 'UTC' !== $timezone ) {
			return array(
				array(
					'id'           => 'timezone-configured',
					'title'        => __( 'Timezone - Properly Configured', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %s: Current timezone */
						__( 'Your timezone is set to %s. This ensures scheduled posts publish at the correct local time.', 'wpshadow' ),
						esc_html( $timezone )
					),
					'severity'     => 'pass',
					'category'     => 'settings',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-clock',
				),
			);
		}

		return array(
			array(
				'id'           => 'timezone-configured',
				'title'        => __( 'Timezone - Not Configured', 'wpshadow' ),
				'description'  => __( 'Your timezone is set to UTC (default). This may cause scheduled posts to publish at unexpected times. Set your timezone in Settings → General.', 'wpshadow' ),
				'severity'     => 'warning',
				'category'     => 'settings',
				'threat_level' => 25,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-clock',
			),
		);
	}

	/**
	 * Test: Admin email is configured
	 */
	private static function test_admin_email() {
		$admin_email = get_option( 'admin_email' );
		$is_valid    = is_email( $admin_email );

		if ( $is_valid ) {
			return array(
				array(
					'id'           => 'admin-email',
					'title'        => __( 'Admin Email - Valid', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %s: Admin email address */
						__( 'Your admin email is properly configured: %s. This receives important notifications.', 'wpshadow' ),
						esc_html( $admin_email )
					),
					'severity'     => 'pass',
					'category'     => 'settings',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-email-alt',
				),
			);
		}

		return array(
			array(
				'id'           => 'admin-email',
				'title'        => __( 'Admin Email - Invalid', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: Current admin email */
					__( 'Your admin email is not valid: %s. Update it in Settings → General. This address receives critical notifications.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'severity'     => 'warning',
				'category'     => 'settings',
				'threat_level' => 50,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Test: Search engine visibility
	 */
	private static function test_search_engine_visibility() {
		$indexing = get_option( 'blog_public' );

		if ( 1 === intval( $indexing ) ) {
			return array(
				array(
					'id'           => 'search-visibility',
					'title'        => __( 'Search Engine Visibility - Enabled', 'wpshadow' ),
					'description'  => __( 'Your site is set to be visible to search engines. This is correct for most sites.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'seo',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-search',
				),
			);
		}

		return array(
			array(
				'id'           => 'search-visibility',
				'title'        => __( 'Search Engine Visibility - Disabled', 'wpshadow' ),
				'description'  => __( 'Your site is hidden from search engines. If this is unintentional, enable it in Settings → Reading. Check the box "Allow search engines to index this site".', 'wpshadow' ),
				'severity'     => 'critical',
				'category'     => 'seo',
				'threat_level' => 90,
				'color'        => '#f44336',
				'icon'         => 'dashicons-visibility',
			),
		);
	}

	/**
	 * Test: Posts per page (reading performance)
	 */
	private static function test_posts_per_page() {
		$posts_per_page = intval( get_option( 'posts_per_page' ) );

		if ( $posts_per_page >= 5 && $posts_per_page <= 20 ) {
			return array(
				array(
					'id'           => 'posts-per-page',
					'title'        => __( 'Posts Per Page - Optimal', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %d: Current posts per page */
						__( 'Your blog displays %d posts per page. This is optimal for balancing content visibility with page performance.', 'wpshadow' ),
						$posts_per_page
					),
					'severity'     => 'pass',
					'category'     => 'performance',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-admin-post',
				),
			);
		}

		$recommendation = $posts_per_page < 5 ? __( 'Too low; increase for better content visibility.', 'wpshadow' ) : __( 'Too high; decrease for better page performance.', 'wpshadow' );

		return array(
			array(
				'id'           => 'posts-per-page',
				'title'        => __( 'Posts Per Page - Not Optimal', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %1$d: Current posts per page, %2$s: Recommendation */
					__( 'Currently showing %1$d posts per page. %2$s Update in Settings → Reading (recommended range: 5-20).', 'wpshadow' ),
					$posts_per_page,
					$recommendation
				),
				'severity'     => 'warning',
				'category'     => 'performance',
				'threat_level' => 30,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-admin-post',
			),
		);
	}

	/**
	 * Writing: Default post category exists
	 */
	private static function test_writing_default_category() {
		$default_cat_id = intval( get_option( 'default_category' ) );
		$term = $default_cat_id ? get_category( $default_cat_id ) : null;

		if ( $term && ! is_wp_error( $term ) ) {
			return array(
				array(
					'id'           => 'writing-default-category',
					'title'        => __( 'Writing: Default Category Set', 'wpshadow' ),
					'description'  => sprintf( __( 'Default post category is set to "%s" (ID %d). Posts without a chosen category will use this.', 'wpshadow' ), esc_html( $term->name ), $default_cat_id ),
					'severity'     => 'pass',
					'category'     => 'settings',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-category',
				),
			);
		}

		return array(
			array(
				'id'           => 'writing-default-category',
				'title'        => __( 'Writing: Default Category Not Found', 'wpshadow' ),
				'description'  => __( 'Default post category is not set or missing. Set a default in Settings → Writing to avoid uncategorized content.', 'wpshadow' ),
				'severity'     => 'warning',
				'category'     => 'settings',
				'threat_level' => 30,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Writing: Default post format is Standard/None
	 */
	private static function test_writing_default_format() {
		$format = get_option( 'default_post_format' );
		$normalized = is_string( $format ) ? strtolower( trim( $format ) ) : '';

		if ( '' === $normalized || '0' === $normalized || 'standard' === $normalized ) {
			return array(
				array(
					'id'           => 'writing-default-format',
					'title'        => __( 'Writing: Default Format is Standard', 'wpshadow' ),
					'description'  => __( 'Default post format is Standard/None, which is the most flexible for most sites.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'settings',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-admin-settings',
				),
			);
		}

		return array(
			array(
				'id'           => 'writing-default-format',
				'title'        => __( 'Writing: Non-Standard Default Format', 'wpshadow' ),
				'description'  => sprintf( __( 'Default post format is "%s". Unless you publish a specific format regularly, Standard/None is recommended.', 'wpshadow' ), esc_html( $format ) ),
				'severity'     => 'warning',
				'category'     => 'settings',
				'threat_level' => 20,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-admin-settings',
			),
		);
	}

	/**
	 * Media: Validate common image sizes (thumbnail/medium/large)
	 */
	private static function test_media_sizes() {
		$thumb_w  = intval( get_option( 'thumbnail_size_w' ) );
		$thumb_h  = intval( get_option( 'thumbnail_size_h' ) );
		$thumb_cr = intval( get_option( 'thumbnail_crop' ) );
		$med_w    = intval( get_option( 'medium_size_w' ) );
		$med_h    = intval( get_option( 'medium_size_h' ) );
		$lg_w     = intval( get_option( 'large_size_w' ) );
		$lg_h     = intval( get_option( 'large_size_h' ) );

		$issues = array();

		// Thumbnail checks (recommended 150x150 crop on)
		if ( $thumb_w === 150 && $thumb_h === 150 && $thumb_cr === 1 ) {
			$issues[] = array(
				'id'           => 'media-thumbnail',
				'title'        => __( 'Media: Thumbnail Size Optimal (150×150, crop)', 'wpshadow' ),
				'description'  => __( 'Thumbnails are configured for 150×150 with cropping enabled. This is optimal for consistent grid displays.', 'wpshadow' ),
				'severity'     => 'pass',
				'category'     => 'performance',
				'threat_level' => 5,
				'color'        => '#2e7d32',
				'icon'         => 'dashicons-images-alt2',
			);
		} else {
			$issues[] = array(
				'id'           => 'media-thumbnail',
				'title'        => __( 'Media: Thumbnail Size Not Optimal', 'wpshadow' ),
				'description'  => sprintf( __( 'Current thumbnail size is %1$d×%2$d; crop %3$s. Recommended: 150×150 with crop enabled for consistent thumbnails.', 'wpshadow' ), $thumb_w, $thumb_h, $thumb_cr ? __( 'on', 'wpshadow' ) : __( 'off', 'wpshadow' ) ),
				'severity'     => 'warning',
				'category'     => 'performance',
				'threat_level' => 25,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-images-alt2',
			);
		}

		// Medium checks (recommended around 300×300)
		if ( $med_w >= 280 && $med_w <= 400 && $med_h >= 280 && $med_h <= 400 ) {
			$issues[] = array(
				'id'           => 'media-medium',
				'title'        => __( 'Media: Medium Size Reasonable (~300×300)', 'wpshadow' ),
				'description'  => sprintf( __( 'Medium image size is %1$d×%2$d. This is within a sensible range for content images.', 'wpshadow' ), $med_w, $med_h ),
				'severity'     => 'pass',
				'category'     => 'performance',
				'threat_level' => 5,
				'color'        => '#2e7d32',
				'icon'         => 'dashicons-format-image',
			);
		} else {
			$issues[] = array(
				'id'           => 'media-medium',
				'title'        => __( 'Media: Medium Size Unusual', 'wpshadow' ),
				'description'  => sprintf( __( 'Medium image size is %1$d×%2$d. Consider ~300×300 to balance quality and performance.', 'wpshadow' ), $med_w, $med_h ),
				'severity'     => 'warning',
				'category'     => 'performance',
				'threat_level' => 20,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-format-image',
			);
		}

		// Large checks (recommended around 1024×1024)
		if ( $lg_w >= 900 && $lg_w <= 1400 && $lg_h >= 900 && $lg_h <= 1400 ) {
			$issues[] = array(
				'id'           => 'media-large',
				'title'        => __( 'Media: Large Size Reasonable (~1024×1024)', 'wpshadow' ),
				'description'  => sprintf( __( 'Large image size is %1$d×%2$d, suitable for full-width content without excessive file size.', 'wpshadow' ), $lg_w, $lg_h ),
				'severity'     => 'pass',
				'category'     => 'performance',
				'threat_level' => 5,
				'color'        => '#2e7d32',
				'icon'         => 'dashicons-format-image',
			);
		} else {
			$issues[] = array(
				'id'           => 'media-large',
				'title'        => __( 'Media: Large Size Unusual', 'wpshadow' ),
				'description'  => sprintf( __( 'Large image size is %1$d×%2$d. Consider ~1024×1024 to avoid overly large images that hurt performance.', 'wpshadow' ), $lg_w, $lg_h ),
				'severity'     => 'warning',
				'category'     => 'performance',
				'threat_level' => 20,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-format-image',
			);
		}

		return $issues;
	}

	/**
	 * Media: Organize uploads by year/month
	 */
	private static function test_media_organize_uploads() {
		$organize = intval( get_option( 'uploads_use_yearmonth_folders' ) );

		if ( 1 === $organize ) {
			return array(
				array(
					'id'           => 'media-organize-uploads',
					'title'        => __( 'Media: Uploads Organized by Year/Month', 'wpshadow' ),
					'description'  => __( 'Uploads are organized into year/month folders, which keeps media tidy and manageable.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'settings',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-archive',
				),
			);
		}

		return array(
			array(
				'id'           => 'media-organize-uploads',
				'title'        => __( 'Media: Uploads Not Organized by Date', 'wpshadow' ),
				'description'  => __( 'Uploads are stored in a single folder. Consider enabling year/month organization in Settings → Media for better management.', 'wpshadow' ),
				'severity'     => 'warning',
				'category'     => 'settings',
				'threat_level' => 15,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-archive',
			),
		);
	}

	/**
	 * Test: Comment moderation enabled
	 * CRITICAL: This must be enabled to prevent spam
	 */
	private static function test_comment_moderation() {
		$moderation_enabled = get_option( 'comment_moderation' );

		if ( $moderation_enabled ) {
			return array(
				array(
					'id'           => 'comment-moderation',
					'title'        => __( 'Comment Moderation - Enabled', 'wpshadow' ),
					'description'  => __( 'Comment moderation is enabled. First-time comments will be held for approval. This is excellent for spam prevention.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'settings',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-editor-quote',
				),
			);
		}

		return array(
			array(
				'id'           => 'comment-moderation',
				'title'        => __( 'Comment Moderation - DISABLED (Critical)', 'wpshadow' ),
				'description'  => __( '⚠️ CRITICAL: Comment moderation is disabled. All visitor comments post immediately without approval. This creates a severe spam vulnerability. Enable it now: Settings → Discussion → Check "Hold a comment in the queue if it contains...". This is a security risk.', 'wpshadow' ),
				'severity'     => 'critical',
				'category'     => 'security',
				'threat_level' => 95,
				'color'        => '#f44336',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Test: Default comments setting
	 */
	private static function test_default_comments() {
		$comments_on = get_option( 'default_comment_status' );

		if ( 'closed' === $comments_on ) {
			return array(
				array(
					'id'           => 'default-comments',
					'title'        => __( 'Comments Default - Disabled', 'wpshadow' ),
					'description'  => __( 'Comments are disabled by default on new posts. This reduces moderation workload while allowing you to enable per-post.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'settings',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-format-chat',
				),
			);
		}

		return array(
			array(
				'id'           => 'default-comments',
				'title'        => __( 'Comments Default - Enabled on New Posts', 'wpshadow' ),
				'description'  => __( 'Comments are enabled by default on all new posts. This increases moderation workload. Consider disabling them by default in Settings → Discussion and enabling per-post as needed.', 'wpshadow' ),
				'severity'     => 'warning',
				'category'     => 'settings',
				'threat_level' => 35,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-format-chat',
			),
		);
	}

	/**
	 * Test: Permalink structure (SEO)
	 */
	private static function test_permalink_structure() {
		$permalink_structure = get_option( 'permalink_structure' );

		// Ideal: /%postname%/ or /%post_id%/
		if ( empty( $permalink_structure ) ) {
			return array(
				array(
					'id'           => 'permalink-structure',
					'title'        => __( 'Permalink Structure - Using Default', 'wpshadow' ),
					'description'  => __( 'Your site is using default post URLs (?p=123). This is not SEO-friendly. Change to /%postname%/ in Settings → Permalinks for better search rankings.', 'wpshadow' ),
					'severity'     => 'warning',
					'category'     => 'seo',
					'threat_level' => 40,
					'color'        => '#ff9800',
					'icon'         => 'dashicons-link',
				),
			);
		}

		if ( strpos( $permalink_structure, '%postname%' ) !== false ) {
			return array(
				array(
					'id'           => 'permalink-structure',
					'title'        => __( 'Permalink Structure - SEO Optimized', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %s: Current permalink structure */
						__( 'Your permalink structure is SEO-friendly: %s. This helps search engines understand your content.', 'wpshadow' ),
						esc_html( $permalink_structure )
					),
					'severity'     => 'pass',
					'category'     => 'seo',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-link',
				),
			);
		}

		return array(
			array(
				'id'           => 'permalink-structure',
				'title'        => __( 'Permalink Structure - Not Optimal for SEO', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: Current permalink structure */
					__( 'Your permalink structure (%s) is not optimal for SEO. Consider changing to /%% postname %% in Settings → Permalinks.', 'wpshadow' ),
					esc_html( $permalink_structure )
				),
				'severity'     => 'warning',
				'category'     => 'seo',
				'threat_level' => 30,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-link',
			),
		);
	}

	/**
	 * Test: Privacy policy page exists
	 */
	private static function test_privacy_policy() {
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );

		if ( $privacy_page_id ) {
			$privacy_page = get_post( $privacy_page_id );

			if ( $privacy_page && ! empty( $privacy_page->post_content ) ) {
				return array(
					array(
						'id'           => 'privacy-policy',
						'title'        => __( 'Privacy Policy - Present and Has Content', 'wpshadow' ),
						'description'  => __( 'Your privacy policy page is set up with content. This is required for GDPR and privacy compliance.', 'wpshadow' ),
						'severity'     => 'pass',
						'category'     => 'settings',
						'threat_level' => 5,
						'color'        => '#2e7d32',
						'icon'         => 'dashicons-text-page',
					),
				);
			}

			return array(
				array(
					'id'           => 'privacy-policy',
					'title'        => __( 'Privacy Policy - Page Exists but Empty', 'wpshadow' ),
					'description'  => __( 'Your privacy policy page exists but has no content. Add content that explains how you collect, use, and protect visitor data. This is required for legal compliance.', 'wpshadow' ),
					'severity'     => 'warning',
					'category'     => 'settings',
					'threat_level' => 50,
					'color'        => '#ff9800',
					'icon'         => 'dashicons-warning',
				),
			);
		}

		return array(
			array(
				'id'           => 'privacy-policy',
				'title'        => __( 'Privacy Policy - Not Set', 'wpshadow' ),
				'description'  => __( 'No privacy policy page is configured. This is required for legal compliance. Create a new page, add privacy policy content, then set it in Settings → Privacy.', 'wpshadow' ),
				'severity'     => 'critical',
				'category'     => 'settings',
				'threat_level' => 75,
				'color'        => '#f44336',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Test: User registration setting
	 */
	private static function test_user_registration() {
		$allow_registration = get_option( 'users_can_register' );

		if ( ! $allow_registration ) {
			return array(
				array(
					'id'           => 'user-registration',
					'title'        => __( 'User Registration - Disabled (Secure)', 'wpshadow' ),
					'description'  => __( 'New user registration is disabled. Only administrators can create user accounts. This is the secure default.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'security',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-admin-users',
				),
			);
		}

		return array(
			array(
				'id'           => 'user-registration',
				'title'        => __( 'User Registration - Enabled', 'wpshadow' ),
				'description'  => __( 'New user registration is enabled. Anyone can create an account. Only enable if you intentionally allow public registrations. Otherwise, disable it in Settings → General.', 'wpshadow' ),
				'severity'     => 'warning',
				'category'     => 'security',
				'threat_level' => 40,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Reading: Static homepage is configured
	 */
	private static function test_homepage_setup() {
		$show_on_front = get_option( 'show_on_front' );
		$page_on_front = intval( get_option( 'page_on_front' ) );

		if ( 'page' === $show_on_front && $page_on_front > 0 ) {
			$homepage = get_post( $page_on_front );
			if ( $homepage ) {
				return array(
					array(
						'id'           => 'homepage-setup',
						'title'        => __( 'Homepage - Static Page Configured', 'wpshadow' ),
						'description'  => sprintf( __( 'Your homepage is set to a static page: "%s". This gives you more control over the front page content.', 'wpshadow' ), esc_html( $homepage->post_title ) ),
						'severity'     => 'pass',
						'category'     => 'settings',
						'threat_level' => 5,
						'color'        => '#2e7d32',
						'icon'         => 'dashicons-admin-home',
					),
				);
			}
		}

		if ( 'posts' === $show_on_front ) {
			return array(
				array(
					'id'           => 'homepage-setup',
					'title'        => __( 'Homepage - Shows Latest Posts', 'wpshadow' ),
					'description'  => __( 'Your homepage displays latest blog posts. This is fine for a blog, but consider creating a static homepage for a professional appearance. Set it in Settings → Reading.', 'wpshadow' ),
					'severity'     => 'warning',
					'category'     => 'settings',
					'threat_level' => 15,
					'color'        => '#ff9800',
					'icon'         => 'dashicons-admin-home',
				),
			);
		}

		return array();
	}

	/**
	 * Writing: Post revisions configured reasonably
	 */
	private static function test_post_revisions() {
		$revisions = defined( 'WP_POST_REVISIONS' ) ? WP_POST_REVISIONS : 0;

		// Default is true (unlimited), which can cause database bloat
		if ( true === $revisions || ( is_int( $revisions ) && $revisions > 10 ) ) {
			return array(
				array(
					'id'           => 'post-revisions',
					'title'        => __( 'Post Revisions - Unlimited (Database Risk)', 'wpshadow' ),
					'description'  => __( 'Post revisions are unlimited, which can bloat your database over time. Consider limiting to 5-10 revisions per post by adding WP_POST_REVISIONS to wp-config.php.', 'wpshadow' ),
					'severity'     => 'warning',
					'category'     => 'performance',
					'threat_level' => 30,
					'color'        => '#ff9800',
					'icon'         => 'dashicons-backup',
				),
			);
		}

		if ( false === $revisions ) {
			return array(
				array(
					'id'           => 'post-revisions',
					'title'        => __( 'Post Revisions - Disabled', 'wpshadow' ),
					'description'  => __( 'Post revisions are completely disabled. You won\'t be able to recover previous versions of posts. Consider allowing 3-5 revisions for safety.', 'wpshadow' ),
					'severity'     => 'warning',
					'category'     => 'settings',
					'threat_level' => 20,
					'color'        => '#ff9800',
					'icon'         => 'dashicons-backup',
				),
			);
		}

		return array(
			array(
				'id'           => 'post-revisions',
				'title'        => __( 'Post Revisions - Limited', 'wpshadow' ),
				'description'  => sprintf( __( 'Post revisions are limited to %d per post. This balances database size with recovery capability.', 'wpshadow' ), intval( $revisions ) ),
				'severity'     => 'pass',
				'category'     => 'settings',
				'threat_level' => 5,
				'color'        => '#2e7d32',
				'icon'         => 'dashicons-backup',
			),
		);
	}

	/**
	 * Discussion: Pingbacks and trackbacks disabled
	 */
	private static function test_pingbacks_trackbacks() {
		$pingback = intval( get_option( 'default_ping_status' ) );

		if ( 'open' !== get_option( 'default_ping_status' ) ) {
			return array(
				array(
					'id'           => 'pingbacks-trackbacks',
					'title'        => __( 'Pingbacks/Trackbacks - Disabled (Good)', 'wpshadow' ),
					'description'  => __( 'Pingbacks and trackbacks are disabled. These are outdated features that can be exploited for spam and DDoS attacks. Keeping them disabled is best practice.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'security',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-shield-alt',
				),
			);
		}

		return array(
			array(
				'id'           => 'pingbacks-trackbacks',
				'title'        => __( 'Pingbacks/Trackbacks - Enabled (Legacy)', 'wpshadow' ),
				'description'  => __( 'Pingbacks and trackbacks are enabled. These are outdated and can be exploited for spam/DDoS attacks. Disable them in Settings → Discussion → Uncheck "Allow link notifications from other blogs".', 'wpshadow' ),
				'severity'     => 'warning',
				'category'     => 'security',
				'threat_level' => 35,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Users: Admin username is not "admin"
	 */
	private static function test_admin_username() {
		$admin_user = get_user_by( 'ID', 1 );

		if ( ! $admin_user ) {
			return array();
		}

		if ( 'admin' === $admin_user->user_login ) {
			return array(
				array(
					'id'           => 'admin-username-weak',
					'title'        => __( 'Admin Username - Weak ("admin")', 'wpshadow' ),
					'description'  => __( 'Your primary admin account uses the username "admin". This is a common target for brute-force attacks. Rename this user or create a new admin account with a unique username and delete this one.', 'wpshadow' ),
					'severity'     => 'warning',
					'category'     => 'security',
					'threat_level' => 50,
					'color'        => '#ff9800',
					'icon'         => 'dashicons-warning',
				),
			);
		}

		return array(
			array(
				'id'           => 'admin-username-weak',
				'title'        => __( 'Admin Username - Unique', 'wpshadow' ),
				'description'  => sprintf( __( 'Your primary admin account uses a unique username: "%s". This is more secure than the default "admin".', 'wpshadow' ), esc_html( $admin_user->user_login ) ),
				'severity'     => 'pass',
				'category'     => 'security',
				'threat_level' => 5,
				'color'        => '#2e7d32',
				'icon'         => 'dashicons-admin-users',
			),
		);
	}

	/**
	 * Users: Multiple admin accounts
	 */
	private static function test_multiple_admins() {
		$admins = count_users();
		$admin_count = isset( $admins['avail_roles']['administrator'] ) ? intval( $admins['avail_roles']['administrator'] ) : 0;

		if ( $admin_count <= 1 ) {
			return array(
				array(
					'id'           => 'multiple-admins',
					'title'        => __( 'Admin Accounts - Single Admin', 'wpshadow' ),
					'description'  => __( 'Only one administrator account is active. This is appropriate for single-owner sites.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'security',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-admin-users',
				),
			);
		}

		return array(
			array(
				'id'           => 'multiple-admins',
				'title'        => __( 'Admin Accounts - Multiple', 'wpshadow' ),
				'description'  => sprintf( __( 'You have %d administrator accounts. If you don\'t need all of them, consider removing unnecessary admin accounts to reduce security risk. Check Users → Administrators.', 'wpshadow' ), $admin_count ),
				'severity'     => 'warning',
				'category'     => 'security',
				'threat_level' => 30,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Appearance: Site icon (favicon) is set
	 */
	private static function test_site_icon() {
		$site_icon = get_option( 'site_icon' );

		if ( $site_icon ) {
			$icon_data = wp_get_attachment_image_src( $site_icon, 'full' );
			if ( $icon_data ) {
				return array(
					array(
						'id'           => 'site-icon-set',
						'title'        => __( 'Site Icon - Configured', 'wpshadow' ),
						'description'  => __( 'Your site has a favicon/icon set. This improves brand recognition in browser tabs and bookmarks.', 'wpshadow' ),
						'severity'     => 'pass',
						'category'     => 'branding',
						'threat_level' => 5,
						'color'        => '#2e7d32',
						'icon'         => 'dashicons-format-image',
					),
				);
			}
		}

		return array(
			array(
				'id'           => 'site-icon-set',
				'title'        => __( 'Site Icon - Missing', 'wpshadow' ),
				'description'  => __( 'No site favicon/icon is configured. Add one in Appearance → Site Icon to improve branding and user recognition.', 'wpshadow' ),
				'severity'     => 'warning',
				'category'     => 'branding',
				'threat_level' => 10,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Appearance: Primary menu is assigned to a location
	 */
	private static function test_menus_assigned() {
		$locations = get_nav_menu_locations();

		if ( ! empty( $locations ) && is_array( $locations ) ) {
			$assigned_count = 0;
			foreach ( $locations as $location => $menu_id ) {
				if ( $menu_id > 0 ) {
					$assigned_count++;
				}
			}

			if ( $assigned_count > 0 ) {
				return array(
					array(
						'id'           => 'menus-assigned',
						'title'        => __( 'Navigation Menus - Assigned', 'wpshadow' ),
						'description'  => sprintf( __( '%d navigation menu location(s) are assigned. This ensures proper navigation display on your site.', 'wpshadow' ), $assigned_count ),
						'severity'     => 'pass',
						'category'     => 'settings',
						'threat_level' => 5,
						'color'        => '#2e7d32',
						'icon'         => 'dashicons-menu',
					),
				);
			}
		}

		return array(
			array(
				'id'           => 'menus-assigned',
				'title'        => __( 'Navigation Menus - Not Assigned', 'wpshadow' ),
				'description'  => __( 'No navigation menus are assigned to theme locations. Your theme may not display navigation correctly. Create a menu in Appearance → Menus and assign it to a location.', 'wpshadow' ),
				'severity'     => 'warning',
				'category'     => 'settings',
				'threat_level' => 25,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Content: Many draft posts (editorial issue)
	 */
	private static function test_draft_posts() {
		$draft_posts = count_user_posts( get_current_user_id(), 'draft' );

		if ( $draft_posts === 0 ) {
			return array();
		}

		if ( $draft_posts <= 5 ) {
			return array(
				array(
					'id'           => 'draft-posts',
					'title'        => __( 'Draft Posts - Few', 'wpshadow' ),
					'description'  => sprintf( __( 'You have %d draft post(s). This is normal for ongoing work.', 'wpshadow' ), $draft_posts ),
					'severity'     => 'pass',
					'category'     => 'content',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-admin-post',
				),
			);
		}

		return array(
			array(
				'id'           => 'draft-posts',
				'title'        => __( 'Draft Posts - Many Pending', 'wpshadow' ),
				'description'  => sprintf( __( 'You have %d draft post(s). Consider finishing or deleting old drafts to keep your content library organized. Check Posts → All Posts → Draft.', 'wpshadow' ), $draft_posts ),
				'severity'     => 'warning',
				'category'     => 'content',
				'threat_level' => 15,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-warning',
			),
		);
	}

	/**
	 * Content: At least some content is published
	 */
	private static function test_published_content() {
		$published_posts = count_user_posts( get_current_user_id(), 'publish' );

		if ( $published_posts === 0 ) {
			return array(
				array(
					'id'           => 'published-content',
					'title'        => __( 'Published Content - None Found', 'wpshadow' ),
					'description'  => __( 'No published posts or pages are on your site. Publish your first post or page to get started!', 'wpshadow' ),
					'severity'     => 'warning',
					'category'     => 'content',
					'threat_level' => 40,
					'color'        => '#ff9800',
					'icon'         => 'dashicons-warning',
				),
			);
		}

		return array(
			array(
				'id'           => 'published-content',
				'title'        => __( 'Published Content - Active', 'wpshadow' ),
				'description'  => sprintf( __( 'Your site has %d published post(s). Keep publishing regular content to engage your audience.', 'wpshadow' ), $published_posts ),
				'severity'     => 'pass',
				'category'     => 'content',
				'threat_level' => 5,
				'color'        => '#2e7d32',
				'icon'         => 'dashicons-admin-post',
			),
		);
	}

	/**
	 * Tools: Debug mode is not enabled in production
	 */
	private static function test_debug_mode() {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array(
				array(
					'id'           => 'debug-mode',
					'title'        => __( 'Debug Mode - ENABLED (Production Risk)', 'wpshadow' ),
					'description'  => __( '⚠️ WP_DEBUG is enabled in wp-config.php. This should only be used during development. Disable it on production sites to prevent exposing sensitive errors. Set WP_DEBUG to false.', 'wpshadow' ),
					'severity'     => 'warning',
					'category'     => 'security',
					'threat_level' => 40,
					'color'        => '#ff9800',
					'icon'         => 'dashicons-warning',
				),
			);
		}

		return array(
			array(
				'id'           => 'debug-mode',
				'title'        => __( 'Debug Mode - Disabled (Good)', 'wpshadow' ),
				'description'  => __( 'WP_DEBUG is disabled. This is correct for production sites. Keep error logging disabled in front-end production environments.', 'wpshadow' ),
				'severity'     => 'pass',
				'category'     => 'security',
				'threat_level' => 5,
				'color'        => '#2e7d32',
				'icon'         => 'dashicons-admin-settings',
			),
		);
	}

	/**
	 * Tools: XML-RPC enabled (legacy, security risk)
	 */
	private static function test_xmlrpc_enabled() {
		// Check if XML-RPC is accessible by looking at the rewrite rules or direct access
		$xmlrpc_response = wp_remote_get( get_home_url() . '/xmlrpc.php', array( 'blocking' => false ) );

		// If we can reach it without 404, it's likely enabled
		$is_enabled = ! is_wp_error( $xmlrpc_response ) && isset( $xmlrpc_response['response']['code'] ) && 404 !== intval( $xmlrpc_response['response']['code'] );

		if ( ! $is_enabled ) {
			return array(
				array(
					'id'           => 'xmlrpc-enabled',
					'title'        => __( 'XML-RPC - Disabled (Secure)', 'wpshadow' ),
					'description'  => __( 'XML-RPC is disabled. This legacy API is rarely needed and can be exploited for attacks. Keeping it disabled is best practice.', 'wpshadow' ),
					'severity'     => 'pass',
					'category'     => 'security',
					'threat_level' => 5,
					'color'        => '#2e7d32',
					'icon'         => 'dashicons-shield-alt',
				),
			);
		}

		return array(
			array(
				'id'           => 'xmlrpc-enabled',
				'title'        => __( 'XML-RPC - Enabled (Legacy Risk)', 'wpshadow' ),
				'description'  => __( 'XML-RPC is enabled. This is a legacy API that can be exploited for brute-force attacks and DDoS. Disable it via a security plugin or by blocking /xmlrpc.php at the server level if not needed.', 'wpshadow' ),
				'severity'     => 'warning',
				'category'     => 'security',
				'threat_level' => 35,
				'color'        => '#ff9800',
				'icon'         => 'dashicons-warning',
			),
		);
	}
}

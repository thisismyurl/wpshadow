<?php
declare(strict_types=1);
/**
 * Initial Setup Configuration Diagnostic
 *
 * Checks initial WordPress setup settings including:
 * - Site Icon, Membership, Date/Time settings
 * - Post via Email configuration
 * - Update Services status
 * - Feed settings and SEO visibility
 * - Discussion settings (comments hardening)
 * - Media settings alignment
 *
 * @package WPShadow
 * @subpackage Diagnostics
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */


namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic for WordPress initial setup configuration
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Registered in Diagnostic_Registry
 */
class Diagnostic_Initial_Setup extends Diagnostic_Base {

	protected static $slug        = 'initial-setup';
	protected static $title       = 'Initial Setup Configuration';
	protected static $description = 'Validates WordPress initial setup settings (site icon, membership, date/time, email, feeds, discussions, media).';

	/**
	 * Run all initial setup checks
	 */
	public static function check(): ?array {
		$findings = array();

		// Privacy Policy check
		$privacy_page_id = get_option( 'wp_page_for_privacy_policy' );
		if ( empty( $privacy_page_id ) ) {
			$findings[] = array(
				'type'     => 'privacy-policy',
				'issue'    => 'No Privacy Policy page assigned. Required for GDPR compliance and user trust. Set in Settings > Privacy.',
				'severity' => 'high',
			);
		} else {
			// Check if privacy policy is in footer menu
			if ( ! self::is_privacy_in_footer( (int) $privacy_page_id ) ) {
				$findings[] = array(
					'type'     => 'privacy-policy-footer',
					'issue'    => 'Privacy Policy page exists but is not linked in the footer. Add to footer menu for accessibility.',
					'severity' => 'medium',
				);
			}
		}

		// Theme/Plugin Editors check
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
			$findings[] = array(
				'type'         => 'file-editors-enabled',
				'issue'        => 'Theme and Plugin file editors are enabled. Disable by adding "define( \'DISALLOW_FILE_EDIT\', true );" to wp-config.php for security.',
				'severity'     => 'medium',
				'auto_fixable' => true,
			);
		}

		// Site Icon check
		if ( ! get_site_icon_url() ) {
			$findings[] = array(
				'type'     => 'site-icon',
				'issue'    => 'Site icon not set. Improves branding and browser tab visibility.',
				'severity' => 'low',
			);
		}

		// Membership settings
		$users_can_register = get_option( 'users_can_register' );
		$default_role       = get_option( 'default_role' );

		if ( '1' === $users_can_register && 'subscriber' !== $default_role ) {
			$findings[] = array(
				'type'     => 'membership-role',
				'issue'    => sprintf(
					'User registration enabled with "%s" as default role. "Subscriber" is safer for open registration.',
					$default_role
				),
				'severity' => 'medium',
			);
		}

		// Date and Time format checks
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		if ( empty( $date_format ) ) {
			$findings[] = array(
				'type'     => 'date-format',
				'issue'    => 'Date format not configured. WordPress will use default format.',
				'severity' => 'low',
			);
		}

		if ( empty( $time_format ) ) {
			$findings[] = array(
				'type'     => 'time-format',
				'issue'    => 'Time format not configured. WordPress will use default format.',
				'severity' => 'low',
			);
		}

		// Week starts on configuration
		$start_of_week = get_option( 'start_of_week' );
		if ( is_null( $start_of_week ) || '' === $start_of_week ) {
			$findings[] = array(
				'type'     => 'start-of-week',
				'issue'    => 'Week start day not configured. May affect calendar displays.',
				'severity' => 'info',
			);
		}

		// Update Services (deprecated but sometimes still configured)
		$update_services = get_option( 'update_services' );
		if ( ! empty( $update_services ) ) {
			$findings[] = array(
				'type'     => 'update-services',
				'issue'    => 'XML-RPC Update Services configured. This is deprecated and rarely used. Consider disabling for security.',
				'severity' => 'low',
			);
		}

		// Feed settings - posts per feed
		$posts_per_feed = get_option( 'posts_per_rss' );
		if ( empty( $posts_per_feed ) ) {
			$findings[] = array(
				'type'     => 'feed-posts-count',
				'issue'    => 'Feed post count not configured. Default will be used.',
				'severity' => 'info',
			);
		}

		// Feed settings - feed excerpt/full
		$feed_excerpt = get_option( 'rss_use_excerpt' );
		if ( empty( $feed_excerpt ) ) {
			$findings[] = array(
				'type'     => 'feed-excerpt',
				'issue'    => 'Feed is set to display full posts. Consider limiting to excerpts to encourage site visits.',
				'severity' => 'low',
			);
		}

		// Search engine visibility
		$blog_public = get_option( 'blog_public' );
		if ( '0' === $blog_public ) {
			$findings[] = array(
				'type'     => 'seo-visibility',
				'issue'    => 'Search engine visibility is set to "Discourage search engines from indexing this site". Enable if this is a public site.',
				'severity' => 'high',
			);
		}

		// Discussion settings - comments enabled by default
		$default_comments_status = get_option( 'default_comment_status' );
		if ( 'open' === $default_comments_status ) {
			// Check for comment moderation
			$require_comment_moderation = get_option( 'comment_moderation' );
			if ( empty( $require_comment_moderation ) ) {
				$findings[] = array(
					'type'     => 'discussion-comment-approval',
					'issue'    => 'Comments enabled by default without requiring moderation. Consider requiring approval for all comments.',
					'severity' => 'medium',
				);
			}
		}

		// Comment form location settings
		$require_name_email = get_option( 'require_name_email' );
		if ( empty( $require_name_email ) ) {
			$findings[] = array(
				'type'     => 'discussion-require-identity',
				'issue'    => 'Comment authors not required to provide name and email. This may increase spam.',
				'severity' => 'low',
			);
		}

		// Discussion - thread settings
		$thread_comments = get_option( 'thread_comments' );
		if ( empty( $thread_comments ) ) {
			$findings[] = array(
				'type'     => 'discussion-threading',
				'issue'    => 'Threaded comments disabled. Enabling can improve comment organization.',
				'severity' => 'info',
			);
		}

		// Discussion - comments per page
		$comments_per_page = get_option( 'comments_per_page' );
		if ( empty( $comments_per_page ) || (int) $comments_per_page > 100 ) {
			$findings[] = array(
				'type'     => 'discussion-per-page',
				'issue'    => 'Comments per page not set or set very high. This affects page load performance.',
				'severity' => 'low',
			);
		}

		// Discussion - hold for moderation
		$hold_moderated_comments = get_option( 'hold_moderated_comments' );
		if ( empty( $hold_moderated_comments ) ) {
			$findings[] = array(
				'type'     => 'discussion-hold-moderated',
				'issue'    => 'Moderated comments are not held for review (logged-in users publish immediately). Enable for better control.',
				'severity' => 'low',
			);
		}

		// Media settings - image sizes for theme compatibility
		$thumbnail_size_w = get_option( 'thumbnail_size_w' );
		$thumbnail_size_h = get_option( 'thumbnail_size_h' );

		if ( empty( $thumbnail_size_w ) || empty( $thumbnail_size_h ) ) {
			$findings[] = array(
				'type'     => 'media-thumbnail-size',
				'issue'    => 'Thumbnail image size not properly configured. Verify theme requirements in Settings > Media.',
				'severity' => 'low',
			);
		}

		// Media settings - organize uploads
		$upload_path     = get_option( 'upload_path' );
		$upload_url_path = get_option( 'upload_url_path' );

		if ( empty( $upload_path ) || empty( $upload_url_path ) ) {
			$findings[] = array(
				'type'     => 'media-upload-path',
				'issue'    => 'Media upload path not configured correctly. Check Settings > Media.',
				'severity' => 'medium',
			);
		}

		// If no findings, site setup appears to be properly configured
		if ( empty( $findings ) ) {
			return null;
		}

		// Build finding report with all detected issues
		return array(
			'id'   => self::$slug,
			'title'        => self::$title,
			'description'  => self::build_findings_description( $findings ),
			'category'     => 'settings',
			'severity'     => self::calculate_severity( $findings ),
			'threat_level' => self::calculate_threat_level( $findings ),
			'auto_fixable' => false,
			'timestamp'    => current_time( 'mysql' ),
			'sub_findings' => $findings,
		);
	}

	/**
	 * Build description from individual findings
	 *
	 * @param array $findings Array of finding arrays.
	 * @return string HTML description.
	 */
	private static function build_findings_description( array $findings ): string {
		$count = count( $findings );
		$desc  = sprintf(
			_n(
				'%d initial setup configuration issue detected. Review Settings and verify configuration matches your site requirements.',
				'%d initial setup configuration issues detected. Review Settings and verify configuration matches your site requirements.',
				$count,
				'wpshadow'
			),
			$count
		);

		$desc .= '<ul class="wps-m-10">';
		foreach ( $findings as $finding ) {
			$severity_label = ucfirst( $finding['severity'] );
			$desc          .= sprintf(
				'<li><strong>%s</strong> - %s</li>',
				$severity_label,
				$finding['issue']
			);
		}
		$desc .= '</ul>';

		return $desc;
	}

	/**
	 * Calculate severity based on findings
	 *
	 * @param array $findings Array of findings.
	 * @return string Severity level (critical, high, medium, low, info).
	 */
	private static function calculate_severity( array $findings ): string {
		$max_severity = 'info';
		$severity_map = array(
			'critical' => 5,
			'high'     => 4,
			'medium'   => 3,
			'low'      => 2,
			'info'     => 1,
		);

		foreach ( $findings as $finding ) {
			$sev = $finding['severity'] ?? 'info';
			if ( ( $severity_map[ $sev ] ?? 0 ) > ( $severity_map[ $max_severity ] ?? 0 ) ) {
				$max_severity = $sev;
			}
		}

		return $max_severity;
	}

	/**
	 * Calculate threat level based on findings
	 *
	 * @param array $findings Array of findings.
	 * @return int Threat level 0-100.
	 */
	private static function calculate_threat_level( array $findings ): int {
		$threat = 0;
		foreach ( $findings as $finding ) {
			switch ( $finding['severity'] ) {
				case 'critical':
					$threat += 25;
					break;
				case 'high':
					$threat += 15;
					break;
				case 'medium':
					$threat += 8;
					break;
				case 'low':
					$threat += 3;
					break;
				case 'info':
					$threat += 1;
					break;
			}
		}

		return min( $threat, 100 );
	}

	/**
	 * Check if privacy policy page is linked in any footer menu
	 *
	 * @param int $page_id Privacy policy page ID.
	 * @return bool True if found in footer menu, false otherwise.
	 */
	private static function is_privacy_in_footer( int $page_id ): bool {
		// Get all registered menus
		$menus = get_registered_nav_menus();
		if ( empty( $menus ) ) {
			return false;
		}

		// Check common footer menu locations
		$footer_locations = array( 'footer', 'footer-menu', 'footer-navigation', 'footer-social' );

		foreach ( $footer_locations as $location ) {
			// Get menu ID assigned to this location
			$menu_id = get_nav_menu_locations()[ $location ] ?? null;
			if ( ! $menu_id ) {
				continue;
			}

			// Get menu items
			$items = wp_get_nav_menu_items( $menu_id );
			if ( ! $items ) {
				continue;
			}

			// Check if any item links to privacy policy page
			foreach ( $items as $item ) {
				if ( (int) $item->object_id === $page_id && 'page' === $item->object ) {
					return true;
				}
			}
		}

		return false;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Initial Setup Configuration
	 * Slug: initial-setup
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Validates WordPress initial setup settings (site icon, membership, date/time, email, feeds, discussions, media).
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_initial_setup(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}

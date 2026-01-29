<?php
/**
 * Robots.txt Validation Diagnostic
 *
 * Validates robots.txt file exists, has proper syntax,
 * and doesn't block search engines from crawling.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5028.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Robots.txt Validation Class
 *
 * Checks robots.txt configuration to ensure search engines can crawl properly.
 * Prevents accidental blocking and validates syntax.
 *
 * @since 1.5028.1630
 */
class Diagnostic_Robots_Txt_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'robots-txt-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Robots.txt Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates robots.txt configuration for search engines';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Fetches and analyzes robots.txt using WordPress HTTP API.
	 * Checks for blocking rules, syntax errors, and proper configuration.
	 *
	 * @since  1.5028.1630
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_robots_txt_validation_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Fetch robots.txt using WordPress HTTP API (NO $wpdb).
		$robots_url = home_url( '/robots.txt' );
		$response   = wp_remote_get( $robots_url, array( 'timeout' => 10 ) );

		$issues = array();

		// Check if robots.txt exists.
		if ( is_wp_error( $response ) ) {
			$issues[] = __( 'Unable to fetch robots.txt file', 'wpshadow' );
		} else {
			$status_code = wp_remote_retrieve_response_code( $response );
			$body        = wp_remote_retrieve_body( $response );

			if ( 404 === $status_code ) {
				$issues[] = __( 'Robots.txt file does not exist', 'wpshadow' );
			} elseif ( 200 === $status_code && ! empty( $body ) ) {
				// Analyze content.
				$analysis_issues = self::analyze_robots_content( $body );
				$issues          = array_merge( $issues, $analysis_issues );
			}
		}

		// Check if search engines are discouraged via WordPress settings.
		$blog_public = (int) get_option( 'blog_public', 1 );
		if ( 0 === $blog_public ) {
			$issues[] = __( 'Search engines discouraged in WordPress settings', 'wpshadow' );
		}

		// If any issues found, flag it.
		if ( ! empty( $issues ) ) {
			$threat_level = 35;
			if ( in_array( __( 'Search engines discouraged in WordPress settings', 'wpshadow' ), $issues, true ) ) {
				$threat_level = 50;
			}
			if ( in_array( __( 'Blocks all search engines with Disallow: /', 'wpshadow' ), $issues, true ) ) {
				$threat_level = 60;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Robots.txt configuration has %d issues that may prevent search engine crawling.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => $threat_level > 50 ? 'high' : 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/seo-robots-txt-validation',
				'data'         => array(
					'issues'      => $issues,
					'robots_url'  => $robots_url,
					'blog_public' => $blog_public,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Analyze robots.txt content for issues.
	 *
	 * @since  1.5028.1630
	 * @param  string $content Robots.txt content.
	 * @return array List of detected issues.
	 */
	private static function analyze_robots_content( $content ) {
		$issues = array();
		$lines  = explode( "\n", $content );

		$has_user_agent = false;
		$blocks_all     = false;

		foreach ( $lines as $line ) {
			$line = trim( $line );

			// Skip comments and empty lines.
			if ( empty( $line ) || 0 === strpos( $line, '#' ) ) {
				continue;
			}

			// Check for User-agent directive.
			if ( preg_match( '/^User-agent:\s*(.+)$/i', $line, $matches ) ) {
				$has_user_agent = true;
				$user_agent     = trim( $matches[1] );

				// Check if blocking all user agents.
				if ( '*' === $user_agent ) {
					$blocks_all = true;
				}
			}

			// Check for Disallow: /.
			if ( preg_match( '/^Disallow:\s*\/\s*$/i', $line ) ) {
				if ( $blocks_all || ! $has_user_agent ) {
					$issues[] = __( 'Blocks all search engines with Disallow: /', 'wpshadow' );
				}
			}

			// Check for syntax errors (missing colons).
			if ( ! preg_match( '/^(User-agent|Disallow|Allow|Crawl-delay|Sitemap):/i', $line ) ) {
				$issues[] = sprintf(
					/* translators: %s: invalid line */
					__( 'Invalid syntax: %s', 'wpshadow' ),
					esc_html( substr( $line, 0, 50 ) )
				);
			}
		}

		if ( ! $has_user_agent ) {
			$issues[] = __( 'No User-agent directive found', 'wpshadow' );
		}

		return $issues;
	}
}

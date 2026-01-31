<?php
/**
 * All In One Seo Robots Txt Diagnostic
 *
 * All In One Seo Robots Txt configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.704.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Seo Robots Txt Diagnostic Class
 *
 * @since 1.704.0000
 */
class Diagnostic_AllInOneSeoRobotsTxt extends Diagnostic_Base {

	protected static $slug = 'all-in-one-seo-robots-txt';
	protected static $title = 'All In One Seo Robots Txt';
	protected static $description = 'All In One Seo Robots Txt configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Robots.txt enabled.
		$robots_enabled = get_option( 'aioseo_robots_txt_enabled', '0' );
		if ( '0' === $robots_enabled ) {
			$issues[] = 'AIOSEO robots.txt management disabled (using WordPress default)';
		}

		// Check 2: Blocking important resources.
		$robots_content = get_option( 'aioseo_robots_txt_content', '' );
		if ( strpos( $robots_content, 'Disallow: /wp-content/' ) !== false ) {
			$issues[] = 'robots.txt blocks /wp-content/ (CSS/JS may be blocked from indexing)';
		}
		if ( strpos( $robots_content, 'Disallow: /wp-includes/' ) !== false ) {
			$issues[] = 'robots.txt blocks /wp-includes/ (core resources blocked)';
		}

		// Check 3: Sitemap reference.
		if ( ! empty( $robots_content ) && strpos( $robots_content, 'Sitemap:' ) === false ) {
			$issues[] = 'no sitemap reference in robots.txt (search engines may not find sitemap)';
		}

		// Check 4: Physical robots.txt file conflict.
		$robots_file = ABSPATH . 'robots.txt';
		if ( file_exists( $robots_file ) && '1' === $robots_enabled ) {
			$issues[] = 'physical robots.txt file exists (AIOSEO virtual file will be ignored)';
		}

		// Check 5: Search engine visibility.
		$blog_public = get_option( 'blog_public', '1' );
		if ( '0' === $blog_public ) {
			$issues[] = 'site set to discourage search engines (WordPress setting overrides robots.txt)';
		}

		// Check 6: User-agent specific rules.
		if ( ! empty( $robots_content ) ) {
			$has_user_agent = preg_match( '/User-agent:\s*\*/i', $robots_content );
			if ( ! $has_user_agent ) {
				$issues[] = 'robots.txt missing User-agent directive (rules may not apply)';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'All-in-One SEO robots.txt issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-seo-robots-txt',
			);
		}

		return null;
	}
}

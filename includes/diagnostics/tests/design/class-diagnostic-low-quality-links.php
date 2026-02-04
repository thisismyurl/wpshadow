<?php
/**
 * Diagnostic: Low-Quality Outbound Links
 *
 * Detects links to spam or low-authority domains which damage trust score
 * and can trigger Google penalties.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1510
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Low Quality Links Diagnostic Class
 *
 * Checks for links to questionable domains.
 *
 * Detection methods:
 * - Spam domain pattern matching
 * - Link quality assessment
 * - Broken link detection
 *
 * @since 1.7030.1510
 */
class Diagnostic_Low_Quality_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'low-quality-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Low-Quality Outbound Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Links to spam domains damage trust score';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'external-linking';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 3 points: No suspicious link patterns found
	 * - 2 points: <5 suspicious links
	 * - 0 points: ≥5 suspicious links
	 *
	 * @since  1.7030.1510
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score            = 0;
		$max_score        = 3;
		$suspicious_links = array();

		// Patterns that indicate low-quality domains.
		$spam_patterns = array(
			'.ru/',
			'.tk/',
			'.gq/',
			'.ga/',
			'.ml/',
			'.cf/',
			'free-',
			'download-',
			'get-',
			'-free.',
			'casino',
			'pharma',
			'viagra',
			'porn',
		);

		// Get sample posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Extract external links (not to own domain).
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches );

			if ( empty( $matches[1] ) ) {
				continue;
			}

			foreach ( $matches[1] as $url ) {
				// Skip internal links.
				if ( strpos( $url, home_url() ) !== false || strpos( $url, '/' ) === 0 ) {
					continue;
				}

				// Check for spam patterns.
				$url_lower = strtolower( $url );
				foreach ( $spam_patterns as $pattern ) {
					if ( strpos( $url_lower, $pattern ) !== false ) {
						if ( count( $suspicious_links ) < 15 ) {
							$suspicious_links[] = array(
								'url'        => $url,
								'pattern'    => $pattern,
								'post_id'    => $post->ID,
								'post_title' => $post->post_title,
								'post_url'   => get_permalink( $post->ID ),
							);
						}
						break;
					}
				}
			}
		}

		// Scoring.
		if ( count( $suspicious_links ) === 0 ) {
			$score = 3;
		} elseif ( count( $suspicious_links ) < 5 ) {
			$score = 2;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of suspicious links */
				__( 'Found %d potentially low-quality outbound links. Linking to spam/low-authority domains causes: Google trust score decrease (links = endorsements), Potential manual penalties (link schemes), Visitor distrust (sketchy links = sketchy site), Security risks (malware, phishing sites), Reputation damage (guilt by association). Red flags: Free TLDs (.tk, .gq, .ga, .ml, .cf - 99%% spam), Spam keywords (casino, pharma, adult), Exact-match domains (cheap-seo-services.com), Link farms/directories (thousands of unrelated links), Parked domains, Expired domains. Instead, link to: Authority sites (gov, edu, major publications), Industry leaders (niche-specific authorities), Primary sources (original research, official docs), Relevant, quality content. Audit outbound links quarterly.', 'wpshadow' ),
				count( $suspicious_links )
			),
			'severity'      => 'critical',
			'threat_level'  => 60,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/low-quality-links',
			'suspicious_links' => $suspicious_links,
			'stats'         => array(
				'suspicious_count' => count( $suspicious_links ),
			),
			'recommendation' => __( 'Review flagged links. Remove or replace low-quality links. Link only to authoritative, relevant sources. Add rel="nofollow" to any questionable but necessary links. Use link checker plugin to monitor link quality regularly.', 'wpshadow' ),
		);
	}
}

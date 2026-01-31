<?php
/**
 * Seo Framework Meta Tags Diagnostic
 *
 * Seo Framework Meta Tags configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.706.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seo Framework Meta Tags Diagnostic Class
 *
 * @since 1.706.0000
 */
class Diagnostic_SeoFrameworkMetaTags extends Diagnostic_Base {

	protected static $slug = 'seo-framework-meta-tags';
	protected static $title = 'Seo Framework Meta Tags';
	protected static $description = 'Seo Framework Meta Tags configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'THE_SEO_FRAMEWORK_VERSION' ) && ! class_exists( 'The_SEO_Framework\Load' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify meta tags enabled
		$meta_tags = get_option( 'tsf_meta_tags', 0 );
		if ( ! $meta_tags ) {
			$issues[] = 'Meta tags not enabled';
		}

		// Check 2: Check for Open Graph tags
		$open_graph = get_option( 'tsf_open_graph', 0 );
		if ( ! $open_graph ) {
			$issues[] = 'Open Graph tags not enabled';
		}

		// Check 3: Verify Twitter card tags
		$twitter_cards = get_option( 'tsf_twitter_cards', 0 );
		if ( ! $twitter_cards ) {
			$issues[] = 'Twitter card tags not enabled';
		}

		// Check 4: Check for schema markup
		$schema = get_option( 'tsf_schema', 0 );
		if ( ! $schema ) {
			$issues[] = 'Schema markup not enabled';
		}

		// Check 5: Verify canonical tags
		$canonical = get_option( 'tsf_canonical', 0 );
		if ( ! $canonical ) {
			$issues[] = 'Canonical tags not enabled';
		}

		// Check 6: Check for noindex settings
		$noindex = get_option( 'tsf_noindex', 0 );
		if ( ! $noindex ) {
			$issues[] = 'Noindex settings not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d SEO Framework meta tag issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/seo-framework-meta-tags',
			);
		}

		return null;
	}
}

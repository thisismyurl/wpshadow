<?php
/**
 * Structured Data Diagnostic
 *
 * Tests if schema markup is properly applied to site content.
 * Checks for JSON-LD structured data implementation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Structured Data Diagnostic Class
 *
 * Verifies that the site implements structured data (schema markup)
 * for better search engine understanding and rich snippets.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Structured_Data extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-structured-data';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Structured Data Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if schema markup is properly applied';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the structured data diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if structured data issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for schema plugins.
		$schema_plugins = array(
			'schema-and-structured-data-for-wp/structured-data-for-wp.php' => 'Schema & Structured Data for WP',
			'wp-seopress/seopress.php'           => 'SEOPress',
			'wordpress-seo/wp-seo.php'           => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'rank-math-seo/rank-math-seo.php'    => 'Rank Math',
		);

		$active_schema_plugin = null;
		foreach ( $schema_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_schema_plugin = $name;
				break;
			}
		}

		$stats['schema_plugin'] = $active_schema_plugin;

		// Check homepage for structured data.
		$homepage_url = home_url( '/' );
		$response = wp_remote_get( $homepage_url, array(
			'timeout' => 10,
			'sslverify' => false,
		) );

		$has_jsonld = false;
		$has_microdata = false;
		$has_rdfa = false;
		$schema_types = array();

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Check for JSON-LD (most recommended format).
			if ( preg_match_all( '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches ) ) {
				$has_jsonld = true;
				
				// Parse schema types.
				foreach ( $matches[1] as $json ) {
					$data = json_decode( $json, true );
					if ( $data && isset( $data['@type'] ) ) {
						$schema_types[] = $data['@type'];
					} elseif ( $data && isset( $data['@graph'] ) && is_array( $data['@graph'] ) ) {
						foreach ( $data['@graph'] as $item ) {
							if ( isset( $item['@type'] ) ) {
								$schema_types[] = $item['@type'];
							}
						}
					}
				}
			}

			// Check for Microdata.
			if ( preg_match( '/itemscope|itemprop|itemtype/i', $html ) ) {
				$has_microdata = true;
			}

			// Check for RDFa.
			if ( preg_match( '/vocab=|typeof=|property=/i', $html ) ) {
				$has_rdfa = true;
			}
		}

		$stats['has_jsonld'] = $has_jsonld;
		$stats['has_microdata'] = $has_microdata;
		$stats['has_rdfa'] = $has_rdfa;
		$stats['schema_types'] = array_unique( $schema_types );

		// Check sample posts for structured data.
		$posts = get_posts( array(
			'posts_per_page' => 5,
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$posts_with_schema = 0;
		$total_posts_checked = count( $posts );

		foreach ( $posts as $post ) {
			$post_url = get_permalink( $post->ID );
			$response = wp_remote_get( $post_url, array(
				'timeout' => 10,
				'sslverify' => false,
			) );

			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$html = wp_remote_retrieve_body( $response );
				
				if ( preg_match( '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>/i', $html ) ||
					 preg_match( '/itemscope|itemtype/i', $html ) ) {
					$posts_with_schema++;
				}
			}
		}

		$stats['total_posts_checked'] = $total_posts_checked;
		$stats['posts_with_schema'] = $posts_with_schema;

		if ( $total_posts_checked > 0 ) {
			$stats['schema_coverage'] = round( ( $posts_with_schema / $total_posts_checked ) * 100, 1 );
		} else {
			$stats['schema_coverage'] = 0;
		}

		// Evaluate issues.
		if ( ! $has_jsonld && ! $has_microdata && ! $has_rdfa ) {
			$issues[] = __( 'No structured data detected on homepage', 'wpshadow' );
		}

		if ( ! $has_jsonld && ( $has_microdata || $has_rdfa ) ) {
			$warnings[] = __( 'Using older structured data format - JSON-LD is recommended', 'wpshadow' );
		}

		if ( empty( $schema_types ) ) {
			$issues[] = __( 'No schema types detected - add Organization, WebSite, or Article schema', 'wpshadow' );
		}

		if ( $total_posts_checked > 0 && $posts_with_schema < $total_posts_checked * 0.5 ) {
			$issues[] = sprintf(
				/* translators: %1$d: posts with schema, %2$d: total posts */
				__( 'Only %1$d of %2$d posts have structured data (<50%% coverage)', 'wpshadow' ),
				$posts_with_schema,
				$total_posts_checked
			);
		}

		if ( ! $active_schema_plugin && ! $has_jsonld ) {
			$warnings[] = __( 'No schema plugin active - consider installing one for easier management', 'wpshadow' );
		}

		// Recommendations based on detected schema.
		$recommended_types = array( 'Organization', 'WebSite', 'Article', 'BreadcrumbList' );
		$missing_types = array_diff( $recommended_types, $schema_types );

		if ( ! empty( $missing_types ) && count( $missing_types ) > 2 ) {
			$warnings[] = sprintf(
				/* translators: %s: missing schema types */
				__( 'Consider adding these schema types: %s', 'wpshadow' ),
				implode( ', ', $missing_types )
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Structured data implementation has issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/structured-data',
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
				'description'  => __( 'Structured data has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/structured-data',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Structured data is well implemented.
	}
}

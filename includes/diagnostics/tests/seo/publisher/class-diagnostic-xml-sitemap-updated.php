<?php
/**
 * XML Sitemap Updated Diagnostic
 *
 * Checks if XML sitemap is current and valid.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XML Sitemap Updated Diagnostic Class
 *
 * Verifies that the XML sitemap is current, valid, and accessible
 * to search engines.
 *
 * @since 0.6093.1200
 */
class Diagnostic_XML_Sitemap_Updated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-updated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Updated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XML sitemap is current and valid';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the XML sitemap diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if sitemap issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for sitemap existence.
		$sitemap_url = home_url( '/sitemap.xml' );
		$response = wp_remote_get( $sitemap_url, array(
			'sslverify' => false,
			'timeout'   => 5,
		) );

		if ( is_wp_error( $response ) ) {
			$issues[] = sprintf(
				/* translators: %s: error */
				__( 'XML sitemap not accessible: %s', 'wpshadow' ),
				$response->get_error_message()
			);
			return null;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			$issues[] = sprintf(
				/* translators: %d: HTTP code */
				__( 'XML sitemap returned HTTP %d (should be 200)', 'wpshadow' ),
				$response_code
			);
		}

		$sitemap_content = wp_remote_retrieve_body( $response );
		$stats['sitemap_accessible'] = ( $response_code === 200 );

		// Check if content is valid XML.
		if ( ! $sitemap_content ) {
			$issues[] = __( 'XML sitemap is empty', 'wpshadow' );
			return null;
		}

		$previous_errors = libxml_use_internal_errors( true );
		$xml = simplexml_load_string( $sitemap_content );
		libxml_use_internal_errors( $previous_errors );

		if ( ! $xml ) {
			$issues[] = __( 'XML sitemap is not valid XML', 'wpshadow' );
			return null;
		}

		$stats['valid_xml'] = true;

		// Check sitemap format.
		$is_sitemap_index = isset( $xml->sitemapindex );
		$is_url_set = isset( $xml->url );

		$stats['is_index'] = $is_sitemap_index;
		$stats['is_urlset'] = $is_url_set;

		if ( ! $is_sitemap_index && ! $is_url_set ) {
			$warnings[] = __( 'XML sitemap format not recognized', 'wpshadow' );
		}

		// Count URLs in sitemap.
		if ( $is_url_set ) {
			$url_count = count( $xml->url );
			$stats['url_count'] = $url_count;

			if ( $url_count === 0 ) {
				$warnings[] = __( 'XML sitemap has no URLs', 'wpshadow' );
			} elseif ( $url_count > 50000 ) {
				// Sitemap index should be used.
				$warnings[] = __( 'Sitemap exceeds 50,000 URLs - should use sitemap index', 'wpshadow' );
			}
		}

		// Check URLs in sitemap index.
		if ( $is_sitemap_index ) {
			$sitemap_count = count( $xml->sitemap );
			$stats['sitemap_count'] = $sitemap_count;

			if ( $sitemap_count === 0 ) {
				$warnings[] = __( 'Sitemap index has no sitemaps', 'wpshadow' );
			}
		}

		// Check sitemap modification date.
		$lastmod = null;
		if ( $is_url_set && isset( $xml->url[0]->lastmod ) ) {
			$lastmod = strtotime( (string) $xml->url[0]->lastmod );
		}

		if ( $lastmod ) {
			$stats['last_modified'] = date( 'Y-m-d H:i:s', $lastmod );
			$days_ago = ( time() - $lastmod ) / ( 24 * 3600 );
			$stats['days_since_update'] = round( $days_ago, 1 );

			if ( $days_ago > 30 ) {
				$warnings[] = sprintf(
					/* translators: %d: days */
					__( 'XML sitemap not updated in %d days', 'wpshadow' ),
					intval( $days_ago )
				);
			}
		}

		// Check for priority values.
		$priority_values = array();
		if ( $is_url_set ) {
			foreach ( $xml->url as $url ) {
				if ( isset( $url->priority ) ) {
					$priority_values[] = floatval( $url->priority );
				}
			}
		}

		if ( ! empty( $priority_values ) ) {
			$avg_priority = array_sum( $priority_values ) / count( $priority_values );
			$stats['average_priority'] = round( $avg_priority, 2 );

			// All1.0 or all 0.5 indicates not properly set.
			$unique_priorities = count( array_unique( $priority_values ) );
			if ( $unique_priorities <= 2 ) {
				$warnings[] = __( 'All URLs have same priority - consider varying priorities', 'wpshadow' );
			}
		}

		// Check for changefreq values.
		$changefreq_values = array();
		if ( $is_url_set ) {
			foreach ( $xml->url as $url ) {
				if ( isset( $url->changefreq ) ) {
					$changefreq_values[] = (string) $url->changefreq;
				}
			}
		}

		if ( ! empty( $changefreq_values ) ) {
			$freq_distribution = array_count_values( $changefreq_values );
			$stats['changefreq_distribution'] = $freq_distribution;
		}

		// Check robots.txt has sitemap reference.
		$robots_txt = ABSPATH . 'robots.txt';
		if ( file_exists( $robots_txt ) ) {
			$robots_content = file_get_contents( $robots_txt );
			if ( strpos( $robots_content, 'Sitemap:' ) === false ) {
				$warnings[] = __( 'robots.txt does not reference XML sitemap', 'wpshadow' );
			}
		}

		// Check for SEO plugin.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'rank-math-seo/rank-math-seo.php',
		);

		$has_seo_plugin = false;
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_seo_plugin = true;
				break;
			}
		}

		$stats['seo_plugin'] = $has_seo_plugin;

		if ( ! $has_seo_plugin ) {
			$warnings[] = __( 'No SEO plugin managing sitemap - ensure it\'s manually updated', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'XML sitemap has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/xml-sitemap-updated?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
				'description'  => __( 'XML sitemap has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/xml-sitemap-updated?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // XML sitemap is good.
	}
}

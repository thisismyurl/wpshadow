<?php
/**
 * Industry Credentials Diagnostic
 *
 * Checks whether professional credentials or certifications are displayed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Industry Credentials Diagnostic Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Industry_Credentials extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'industry-credentials';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Industry Credentials or Certifications Displayed';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether professional credentials are visible to visitors';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'trust-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$signals = array();
		$stats   = array();

		$html = Diagnostic_HTML_Helper::fetch_homepage_html_cached(
			'wpshadow_diagnostic_credentials_html',
			300,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		$keywords = array(
			'certified',
			'certification',
			'accredited',
			'licensed',
			'award',
			'awards',
			'featured in',
			'as seen in',
			'press',
			'better business bureau',
			'bbb',
			'iso',
			'pmp',
			'cpa',
			'soc 2',
			'hipaa',
			'pci',
			'member of',
			'association',
			'accreditation',
		);

		if ( $html ) {
			foreach ( $keywords as $keyword ) {
				if ( false !== stripos( $html, $keyword ) ) {
					$signals[] = $keyword;
				}
			}
		}

		$credential_pages = self::find_pages_by_keywords(
			array(
				'certification',
				'certifications',
				'credentials',
				'awards',
				'press',
				'accreditation',
				'memberships',
			)
		);

		$stats['homepage_signals'] = array_values( array_unique( $signals ) );
		$stats['credential_pages'] = $credential_pages;

		if ( ! empty( $signals ) || ! empty( $credential_pages ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Credentials and certifications are like diplomas on the wall. They show visitors you are qualified and trustworthy. Adding these signals can help people feel more confident choosing you over competitors.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/industry-credentials',
			'context'      => array(
				'stats' => $stats,
			),
		);
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since  1.6050.0000
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}

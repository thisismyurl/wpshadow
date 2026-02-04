<?php
/**
 * No Robots.txt File Diagnostic
 *
 * Detects when robots.txt is missing or improperly configured,
 * affecting search engine crawling efficiency.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Robots.txt File
 *
 * Checks whether robots.txt file exists and
 * is properly configured for crawlers.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Robots_Txt_File extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-robots-txt-file';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Robots.txt File';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether robots.txt is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for robots.txt
		$robots_url = home_url( '/robots.txt' );
		$robots_check = wp_remote_get( $robots_url );
		$has_robots = ! is_wp_error( $robots_check ) && wp_remote_retrieve_response_code( $robots_check ) === 200;

		if ( ! $has_robots ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your site doesn\'t have a robots.txt file, which means you\'re not giving search engines crawling instructions. Robots.txt tells crawlers: what to crawl and what to ignore. Without it, Google wastes crawl budget on admin pages, duplicate content, and private areas. A good robots.txt blocks: /wp-admin/, /wp-includes/, duplicate URL parameters. It also points to your sitemap. This improves crawl efficiency and SEO.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Crawl Efficiency',
					'potential_gain' => 'Better crawl budget allocation',
					'roi_explanation' => 'Robots.txt guides crawlers to important content and blocks waste, improving crawl efficiency and SEO.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/robots-txt-file',
			);
		}

		return null;
	}
}

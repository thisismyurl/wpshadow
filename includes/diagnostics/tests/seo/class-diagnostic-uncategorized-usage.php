<?php
/**
 * Uncategorized Usage Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the seo gauge.
 *
 * @package WPShadow
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
 * Diagnostic_Uncategorized_Usage_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Uncategorized_Usage extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'uncategorized-usage';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Uncategorized Usage';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether any published posts are still using the WordPress default Uncategorized category, which signals poor content organization to visitors and search engines.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check default_category and post assignments to uncategorized.
	 *
	 * TODO Fix Plan:
	 * - Rename or replace uncategorized and classify content intentionally.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$default_cat_id = (int) get_option( 'default_category', 1 );

		// If the default category term no longer exists, nothing to flag.
		$default_cat = get_term( $default_cat_id, 'category' );
		if ( is_wp_error( $default_cat ) || empty( $default_cat ) ) {
			return null;
		}

		// If the category has been renamed away from "uncategorized", it's been reviewed.
		if ( 'uncategorized' !== $default_cat->slug ) {
			return null;
		}

		// Count published posts that are ONLY in the default (Uncategorized) category.
		$posts_in_uncategorized = (int) $default_cat->count;
		if ( $posts_in_uncategorized === 0 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of posts in uncategorized */
				_n(
					'%d published post is in the default "Uncategorized" category. The slug "uncategorized" appears in URLs and signals to search engines that content has not been organised intentionally. Rename the category or reassign posts to meaningful categories.',
					'%d published posts are in the default "Uncategorized" category. The slug "uncategorized" appears in URLs and signals to search engines that content has not been organised intentionally. Rename the category or reassign posts to meaningful categories.',
					$posts_in_uncategorized,
					'wpshadow'
				),
				$posts_in_uncategorized
			),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/uncategorized-usage',
			'details'      => array(
				'category_slug'  => $default_cat->slug,
				'category_name'  => $default_cat->name,
				'post_count'     => $posts_in_uncategorized,
			),
		);
	}
}

<?php
/**
 * Sitemap Submission To Search Engines Not Configured Treatment
 *
 * Checks if sitemaps are submitted.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sitemap Submission To Search Engines Not Configured Treatment Class
 *
 * Detects missing sitemap submission.
 *
 * @since 1.6093.1200
 */
class Treatment_Sitemap_Submission_To_Search_Engines_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'sitemap-submission-to-search-engines-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Sitemap Submission To Search Engines Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if sitemaps are submitted';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Sitemap_Submission_To_Search_Engines_Not_Configured' );
	}
}

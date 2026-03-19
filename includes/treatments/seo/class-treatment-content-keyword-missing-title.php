<?php
/**
 * Content Missing Primary Keyword in Title Treatment
 *
 * Detects when primary keyword is missing from the title.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Missing Primary Keyword in Title Treatment Class
 *
 * Target keyword not in H1 is a basic SEO issue. This is
 * 100% auto-detectable and highly impactful.
 *
 * @since 1.6093.1200
 */
class Treatment_Content_Keyword_Missing_Title extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-keyword-missing-title';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Primary Keyword in Title';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing primary keyword in title (H1)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Content_Keyword_Missing_Title' );
	}
}

<?php
/**
 * Meta Robots and Canonical Tags Treatment
 *
 * Tests if pages have proper robots directives and canonical URLs set.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1450
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta Robots and Canonical Tags Treatment Class
 *
 * Validates that pages have proper robots meta tags and canonical URLs
 * to prevent duplicate content and guide search engine crawling.
 *
 * @since 1.7034.1450
 */
class Treatment_Meta_Robots_Canonical_Tags extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'meta-robots-canonical-tags';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Meta Robots and Canonical Tags';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if pages have proper robots directives and canonical URLs set';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Tests meta robots tags and canonical URLs including duplicate
	 * content detection and crawler directives.
	 *
	 * @since  1.7034.1450
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Meta_Robots_Canonical_Tags' );
	}
}

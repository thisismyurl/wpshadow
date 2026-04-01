<?php
/**
 * Local Business Schema Markup Treatment
 *
 * Issue #4803: Missing Local Business Schema Markup
 * Family: business-performance
 *
 * Checks if local business has Schema.org LocalBusiness markup.
 * Local schema helps Google show business info in search results.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Local_Business_Schema Class
 *
 * Checks for LocalBusiness schema markup.
 *
 * @since 0.6093.1200
 */
class Treatment_Local_Business_Schema extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'local-business-schema';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Local Business Schema Markup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Schema.org LocalBusiness markup is present for local businesses';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Local_Business_Schema' );
	}
}

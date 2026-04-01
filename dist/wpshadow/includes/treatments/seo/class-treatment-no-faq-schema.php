<?php
/**
 * Treatment: No FAQ Schema
 *
 * Detects FAQ sections without proper schema markup.
 * FAQ schema enables featured snippets and rich results.
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
 * No FAQ Schema Treatment Class
 *
 * Checks FAQ content for schema markup.
 *
 * Detection methods:
 * - FAQ section identification (headings with ?)
 * - Schema markup checking
 * - Plugin detection
 *
 * @since 0.6093.1200
 */
class Treatment_No_FAQ_Schema extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-faq-schema';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No FAQ Schema';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'FAQ sections without schema miss featured snippet opportunities';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Schema plugin installed
	 * - 1 point: FAQ content uses schema markup
	 * - 1 point: <20% FAQ posts missing schema
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_FAQ_Schema' );
	}
}

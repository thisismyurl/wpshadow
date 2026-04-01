<?php
/**
 * FAQ Schema Implemented Treatment
 *
 * Tests whether the site implements FAQ structured data for voice assistant
 * compatibility. FAQ schema markup helps content appear in voice search results,
 * Google's featured snippets, and voice assistant responses.
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
 * Treatment_Implements_FAQ_Schema Class
 *
 * Treatment #34: FAQ Schema Implemented from Specialized & Emerging Success Habits.
 * Checks if the website implements FAQ structured data (schema.org/FAQPage) to
 * optimize for voice assistants and featured snippets.
 *
 * @since 0.6093.1200
 */
class Treatment_Implements_FAQ_Schema extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-faq-schema';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'FAQ Schema Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site implements FAQ structured data for voice assistant compatibility';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the treatment check.
	 *
	 * FAQ schema (schema.org/FAQPage) makes FAQ content machine-readable for
	 * voice assistants, search engines, and rich results. This treatment checks
	 * for schema plugins, FAQ markup in content, and proper implementation.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Implements_FAQ_Schema' );
	}
}

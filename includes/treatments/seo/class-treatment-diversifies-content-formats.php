<?php
/**
 * Content Format Diversity Treatment
 *
 * Tests for mix of content formats and types to maintain audience
 * engagement and reach different learning styles.
 *
 * @package    WPShadow
 * @subpackage Treatments\Content
 * @since      1.6034.2325
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Format Diversity Treatment Class
 *
 * Analyzes published content to detect variety in formats (text, video,
 * lists, how-tos, interviews, etc.) for engagement and accessibility.
 *
 * **Why This Matters:**
 * - 65% of people are visual learners
 * - Video increases engagement by 80%
 * - Mixed formats increase reach by 120%
 * - Different formats appeal to different audiences
 * - Reduces content monotony and fatigue
 *
 * **Content Format Types:**
 * - How-to guides and tutorials
 * - List posts (top 10, best of)
 * - Video content
 * - Infographics
 * - Case studies and interviews
 * - Long-form articles
 *
 * @since 1.6034.2325
 */
class Treatment_Diversifies_Content_Formats extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'diversifies-content-formats';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Content Format Diversity';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for mix of content formats and types';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2325
	 * @return array|null Finding array if low format diversity, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Diversifies_Content_Formats' );
	}
}

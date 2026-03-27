<?php
/**
 * No Long-Form Content Treatment
 *
 * Detects lack of comprehensive pillar content, missing SEO and
 * authority-building opportunities.
 *
 * @package    WPShadow
 * @subpackage Treatments\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Long-Form Content Treatment Class
 *
 * Analyzes content strategy to ensure inclusion of comprehensive
 * long-form content that establishes authority and drives SEO.
 *
 * **Why This Matters:**
 * - Long-form content ranks 77% better in Google
 * - Average #1 result is 1,890 words
 * - Builds topic authority and trust
 * - Generates more backlinks (3.5x more)
 * - Keeps visitors engaged longer
 *
 * **Long-Form Benefits:**
 * - Ranks for more keywords
 * - Higher social shares
 * - Better conversion rates
 * - Establishes expertise
 * - Comprehensive answers to queries
 *
 * **Ideal Content Mix:**
 * - 30% short-form (< 600 words)
 * - 50% medium (600-1500 words)
 * - 20% long-form (1500+ words)
 *
 * @since 1.6093.1200
 */
class Treatment_No_Long_Form_Content extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-long-form-content';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Long-Form Content';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Missing comprehensive pillar content that drives SEO and authority';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if no long-form content, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_Long_Form_Content' );
	}
}

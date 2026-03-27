<?php
/**
 * Import Lost Shortcodes and Formatting Treatment
 *
 * Tests whether page builder shortcodes, Gutenberg blocks, and custom formatting
 * survive the import process. Shortcodes often power layouts, galleries, and
 * page builder content. If they are stripped or mangled, pages break visually.
 *
 * **What This Check Does:**
 * - Scans imported content for shortcode integrity
 * - Validates Gutenberg block markers remain intact
 * - Detects HTML formatting loss (paragraphs, lists, embeds)
 * - Flags content where shortcodes are removed or escaped
 *
 * **Why This Matters:**
 * Many sites rely on builder shortcodes for entire page layouts. Losing them
 * means broken pages, missing CTAs, and lost conversions. Restoring formatting
 * manually after a failed import can take days.
 *
 * **Real-World Failure Scenario:**
 * - Site uses page builder shortcodes for landing pages
 * - Import process strips bracketed shortcodes
 * - Pages render as plain text and broken markup
 * - Campaign launch fails due to missing layouts
 *
 * Result: Lost revenue and emergency manual rebuilds.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Preserves site structure during migration
 * - #9 Show Value: Prevents expensive manual rework
 * - Helpful Neighbor: Highlights high‑risk content before launch
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/import-shortcodes
 * or https://wpshadow.com/training/migrating-page-builder-sites
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
 * Import Lost Shortcodes Formatting Treatment Class
 *
 * Inspects post content for shortcode and block markers after import.
 *
 * **Implementation Pattern:**
 * 1. Scan content for shortcode patterns
 * 2. Validate Gutenberg block comment syntax
 * 3. Identify escaped or stripped shortcode brackets
 * 4. Return findings with remediation guidance
 *
 * **Related Treatments:**
 * - Import Custom Field Mapping Failures
 * - Import Taxonomy Mismatches
 * - Import Character Encoding Corruption
 *
 * @since 1.6093.1200
 */
class Treatment_Import_Lost_Shortcodes_Formatting extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-lost-shortcodes-formatting';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Import Lost Shortcodes and Formatting';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether page builder shortcodes and blocks survive import';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Import_Lost_Shortcodes_Formatting' );
	}
}

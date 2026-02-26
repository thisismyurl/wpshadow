<?php
/**
 * Skimmable Content Format Treatment
 *
 * Tests if content uses headings, lists, and formatting elements for easy scanning.
 * Well-formatted content improves user engagement and readability.
 *
 * @package    WPShadow
 * @subpackage Treatments\Content
 * @since      1.6034.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Formats_Content_For_Scanning Class
 *
 * Analyzes recent posts for formatting elements that make content easy to scan:
 * - H2/H3 headings for structure
 * - Bullet and numbered lists for clarity
 * - Bold text for emphasis
 * - Short paragraphs for readability
 *
 * @since 1.6034.1200
 */
class Treatment_Formats_Content_For_Scanning extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6034.1200
	 * @var   string
	 */
	protected static $slug = 'formats-content-for-scanning';

	/**
	 * The treatment title
	 *
	 * @since 1.6034.1200
	 * @var   string
	 */
	protected static $title = 'Skimmable Content Format';

	/**
	 * The treatment description
	 *
	 * @since 1.6034.1200
	 * @var   string
	 */
	protected static $description = 'Tests if content uses headings, lists, and formatting for easy scanning';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6034.1200
	 * @var   string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * Analyzes recent posts for formatting elements that improve scannability:
	 * - Headings (H2/H3) every ~300 words
	 * - Lists (bullet or numbered)
	 * - Bold text for emphasis
	 * - Short paragraphs
	 *
	 * @since  1.6034.1200
	 * @return array|null {
	 *     Finding array if issue found, null otherwise.
	 *
	 *     @type string $id           Treatment identifier.
	 *     @type string $title        Issue title.
	 *     @type string $description  Detailed description with recommendations.
	 *     @type string $severity     Issue severity level.
	 *     @type int    $threat_level Numeric threat level (0-100).
	 *     @type bool   $auto_fixable Whether issue can be auto-fixed.
	 *     @type string $kb_link      Link to knowledge base article.
	 *     @type array  $meta         Additional treatment data.
	 * }
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Formats_Content_For_Scanning' );
	}
}

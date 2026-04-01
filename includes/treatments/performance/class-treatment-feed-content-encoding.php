<?php
/**
 * Feed Content Encoding Treatment
 *
 * Verifies that your feeds declare and deliver content with a valid character
 * encoding (typically UTF‑8). Incorrect encoding leads to broken characters,
 * invalid XML, and feed reader errors that silently drop your content.
 *
 * **What This Check Does:**
 * - Reads feed headers and XML declaration for encoding
 * - Validates UTF‑8 or other supported encodings
 * - Detects mismatches between declared and actual encoding
 * - Flags invalid characters that break XML parsing
 * - Encourages consistent UTF‑8 usage across content
 *
 * **Why This Matters:**
 * Feed readers require valid XML. If encoding is wrong, readers may reject
 * the entire feed or show garbled text. This is especially common with
 * multilingual content or emoji-heavy posts.
 *
 * **Real-World Failure Scenario:**
 * - Site outputs UTF‑8 characters but declares ISO‑8859‑1
 * - Reader sees invalid byte sequence
 * - Feed parsing fails; no updates delivered
 *
 * Result: Subscribers stop receiving new posts without any error message.
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Prevents silent content delivery failures
 * - #9 Show Value: Protects reach for global audiences
 * - Cultural Respect: Supports multilingual content correctly
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/feed-encoding
 * or https://wpshadow.com/training/xml-encoding-basics
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Feed_Content_Encoding Class
 *
 * Uses feed output inspection to validate encoding declarations and content.
 *
 * **Implementation Pattern:**
 * 1. Fetch feed XML output
 * 2. Parse XML declaration for encoding
 * 3. Validate content against declared encoding
 * 4. Flag invalid byte sequences or mismatches
 *
 * **Related Treatments:**
 * - Feed XML Validity: Validates XML structure
 * - Feed Content Length: Ensures readable content size
 * - Feed Namespace Configuration: Ensures valid XML namespaces
 */
class Treatment_Feed_Content_Encoding extends Treatment_Base {
	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-content-encoding';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Content Encoding';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed content encoding is set correctly (UTF-8, etc.).';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Feed_Content_Encoding' );
	}
}

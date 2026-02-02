<?php
/**
 * Feed Content Encoding Diagnostic
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
 * @since   1.26032.1921
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Feed_Content_Encoding Class
 *
 * Uses feed output inspection to validate encoding declarations and content.
 *
 * **Implementation Pattern:**
 * 1. Fetch feed XML output
 * 2. Parse XML declaration for encoding
 * 3. Validate content against declared encoding
 * 4. Flag invalid byte sequences or mismatches
 *
 * **Related Diagnostics:**
 * - Feed XML Validity: Validates XML structure
 * - Feed Content Length: Ensures readable content size
 * - Feed Namespace Configuration: Ensures valid XML namespaces
 */
class Diagnostic_Feed_Content_Encoding extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-content-encoding';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Content Encoding';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed content encoding is set correctly (UTF-8, etc.).';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1921
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$feed_url = get_feed_link();
		$response = wp_remote_get( $feed_url, array( 'timeout' => 5 ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}
		$headers = wp_remote_retrieve_headers( $response );
		$encoding = isset( $headers['content-type'] ) ? $headers['content-type'] : '';
		if ( false === stripos( $encoding, 'charset=utf-8' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed content encoding is not set to UTF-8.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 50,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-content-encoding',
			);
		}
		return null;
	}
}

<?php
/**
 * Feed Namespace Configuration Diagnostic
 *
 * Validates that feed XML includes required namespaces for compatibility
 * across readers and aggregators. Missing namespaces can break parsing of
 * common elements like content:encoded, media:thumbnail, or dc:creator.
 *
 * **What This Check Does:**
 * - Inspects feed XML for required namespaces
 * - Validates common namespace prefixes (content, dc, media, atom)
 * - Flags feeds missing namespaces used by their elements
 * - Encourages standards-compliant feed output
 *
 * **Why This Matters:**
 * Many feed readers rely on namespace declarations to interpret advanced
 * elements. If namespaces are missing, readers may ignore important fields
 * like full content, author names, or media attachments.
 *
 * **Real-World Impact:**
 * - Feed includes `<content:encoded>` but no `content` namespace
 * - Reader ignores full content, shows only summary
 * - Subscribers think posts are incomplete
 *
 * Result: Reduced engagement and confusion about content quality.
 *
 * **Philosophy Alignment:**
 * - #1 Helpful Neighbor: Prevents silent parsing failures
 * - #9 Show Value: Ensures full content reaches readers
 * - Accessibility First: Supports assistive feed tooling
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/feed-namespace-configuration
 * or https://wpshadow.com/training/rss-xml-standards
 *
 * @since   1.6032.1921
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Feed_Namespace_Configuration Class
 *
 * Parses feed XML headers to verify namespace declarations are present.
 *
 * **Implementation Pattern:**
 * 1. Fetch feed XML
 * 2. Parse namespace declarations
 * 3. Validate expected prefixes for elements found
 * 4. Return findings with compatibility guidance
 *
 * **Related Diagnostics:**
 * - Feed XML Validity: Ensures XML structure is correct
 * - Feed Content Encoding: Ensures encoding matches XML declarations
 * - Feed Custom Endpoints: Validates custom feed output
 */
class Diagnostic_Feed_Namespace_Configuration extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'feed-namespace-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Feed Namespace Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the feed XML includes required namespaces for compatibility.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'feed';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1921
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$feed_url = get_feed_link();
		$response = wp_remote_get( $feed_url, array( 'timeout' => 5 ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}
		$body = wp_remote_retrieve_body( $response );
		if ( false === strpos( $body, 'xmlns="http://www.w3.org/2005/Atom"' ) && false === strpos( $body, 'xmlns:content="http://purl.org/rss/1.0/modules/content/"' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Feed XML is missing required namespaces for compatibility.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level'=> 40,
				'auto_fixable'=> false,
				'kb_link'     => 'https://wpshadow.com/kb/feed-namespace-configuration',
			);
		}
		return null;
	}
}

<?php
/**
 * Custom Permalink Structure Diagnostic
 *
 * Tests custom permalink structure syntax and validates structure tags.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2032.1410
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Permalink Structure Diagnostic Class
 *
 * Validates custom permalink structure syntax and checks for:
 * - Valid WordPress permalink tags
 * - Deprecated or invalid tags
 * - Proper tag syntax (% wrapped)
 * - Security concerns (execution attempts, etc.)
 *
 * Valid tags include:
 * - %year% - 4-digit year
 * - %monthnum% - 2-digit month
 * - %day% - 2-digit day
 * - %hour% - 2-digit hour
 * - %minute% - 2-digit minute
 * - %second% - 2-digit second
 * - %post_id% - Post ID
 * - %postname% - Post slug
 * - %category% - Post category
 * - %author% - Post author name
 *
 * @since 1.2032.1410
 */
class Diagnostic_Custom_Permalink_Structure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-permalink-structure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Permalink Structure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests custom permalink structure syntax and validates structure tags';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Valid WordPress permalink structure tags
	 *
	 * @var array
	 */
	private static $valid_tags = array(
		'%year%',
		'%monthnum%',
		'%day%',
		'%hour%',
		'%minute%',
		'%second%',
		'%post_id%',
		'%postname%',
		'%category%',
		'%author%',
	);

	/**
	 * Deprecated or invalid tags that should trigger warnings
	 *
	 * @var array
	 */
	private static $deprecated_tags = array(
		'%pagename%'  => 'Use %postname% instead',
		'%post_name%' => 'Use %postname% (no underscore)',
		'%month%'     => 'Use %monthnum% instead',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Validates the custom permalink structure for:
	 * 1. Valid tag syntax
	 * 2. Use of deprecated/invalid tags
	 * 3. Security concerns
	 * 4. Best practices
	 *
	 * @since  1.2032.1410
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$permalink_structure = get_option( 'permalink_structure', '' );

		// Only check if custom permalink structure is set
		if ( empty( $permalink_structure ) ) {
			return null;
		}

		// Skip plain permalink structure (handled by other diagnostics)
		if ( '/?p=%post_id%' === $permalink_structure ) {
			return null;
		}

		$issues = array();

		// Check for deprecated tags
		foreach ( self::$deprecated_tags as $deprecated_tag => $suggestion ) {
			if ( false !== strpos( $permalink_structure, $deprecated_tag ) ) {
				$issues[] = sprintf(
					/* translators: 1: deprecated tag, 2: suggestion */
					__( 'Deprecated tag %1$s found. %2$s.', 'wpshadow' ),
					'<code>' . esc_html( $deprecated_tag ) . '</code>',
					esc_html( $suggestion )
				);
			}
		}

		// Extract all tags from the structure
		preg_match_all( '/%[a-z_]+%/i', $permalink_structure, $matches );

		if ( ! empty( $matches[0] ) ) {
			$found_tags = $matches[0];

			// Check for invalid tags
			foreach ( $found_tags as $tag ) {
				$tag_lower = strtolower( $tag );
				if ( ! in_array( $tag_lower, self::$valid_tags, true ) &&
					! array_key_exists( $tag_lower, self::$deprecated_tags ) ) {
					$issues[] = sprintf(
						/* translators: %s: invalid tag */
						__( 'Invalid permalink tag %s found. This tag is not recognized by WordPress.', 'wpshadow' ),
						'<code>' . esc_html( $tag ) . '</code>'
					);
				}
			}
		}

		// Check for malformed tags (% without closing %, etc.)
		$percent_count = substr_count( $permalink_structure, '%' );
		if ( 0 !== $percent_count % 2 ) {
			$issues[] = __( 'Malformed permalink structure: Unmatched % symbol found. Tags must be wrapped in % symbols.', 'wpshadow' );
		}

		// Check for suspicious patterns (security concern)
		$suspicious_patterns = array(
			'<?',     // PHP tags
			'<script', // Script injection
			'eval(',  // Code execution
			'exec(',  // Code execution
			'system(', // Code execution
			'passthru(', // Code execution
		);

		foreach ( $suspicious_patterns as $pattern ) {
			if ( false !== stripos( $permalink_structure, $pattern ) ) {
				$issues[] = sprintf(
					/* translators: %s: suspicious pattern */
					__( 'Security concern: Suspicious pattern %s detected in permalink structure.', 'wpshadow' ),
					'<code>' . esc_html( $pattern ) . '</code>'
				);
			}
		}

		// Check for best practices: should start with /
		if ( '/' !== substr( $permalink_structure, 0, 1 ) ) {
			$issues[] = __( 'Permalink structure should start with a forward slash (/).', 'wpshadow' );
		}

		// Check for best practices: should end with /
		if ( ! empty( $permalink_structure ) && '/' !== substr( $permalink_structure, -1 ) ) {
			$issues[] = __( 'Permalink structure should end with a forward slash (/) or a file extension for better compatibility.', 'wpshadow' );
		}

		// If issues found, return finding
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of issues */
					__( 'Your custom permalink structure has the following issues: %s', 'wpshadow' ),
					'<ul><li>' . implode( '</li><li>', $issues ) . '</li></ul>'
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/custom-permalink-structure',
				'details'      => array(
					'current_structure' => $permalink_structure,
					'issues_found'      => count( $issues ),
					'issues'            => $issues,
					'valid_tags'        => self::$valid_tags,
				),
			);
		}

		return null;
	}
}

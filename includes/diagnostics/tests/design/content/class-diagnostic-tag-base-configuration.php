<?php
/**
 * Tag Base Configuration Diagnostic
 *
 * Verifies tag URL structure is properly configured for SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tag Base Configuration Diagnostic Class
 *
 * Checks tag URL structure (slug) configuration for optimal SEO.
 *
 * @since 1.6032.1900
 */
class Diagnostic_Tag_Base_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tag-base-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tag Base Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tag URL structure configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reading';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if tags are enabled.
		if ( ! taxonomy_exists( 'post_tag' ) ) {
			return null; // Tags disabled.
		}

		// Get tag base setting.
		$tag_base = get_option( 'tag_base', 'tag' );

		if ( empty( $tag_base ) ) {
			$issues[] = __( 'Tag base is empty - using default "tag" prefix', 'wpshadow' );
		}

		// Check for common SEO issues.
		if ( $tag_base === 'tag' ) {
			// Default is acceptable but not customized.
			return null;
		}

		// Check if tag base contains special characters or spaces.
		if ( preg_match( '/[^a-z0-9-_]/', $tag_base ) ) {
			$issues[] = sprintf(
				/* translators: %s: tag base */
				__( 'Tag base "%s" contains invalid characters - may cause URL issues', 'wpshadow' ),
				esc_attr( $tag_base )
			);
		}

		// Check for conflicts with other permalinks.
		global $wp_rewrite;
		if ( isset( $wp_rewrite->category_base ) && $wp_rewrite->category_base === $tag_base ) {
			$issues[] = __( 'Tag base conflicts with category base - both use same URL structure', 'wpshadow' );
		}

		// Check tag count.
		$tag_count = wp_count_terms( array( 'taxonomy' => 'post_tag' ) );
		if ( $tag_count > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of tags */
				__( 'Large number of tags (%d) may impact performance', 'wpshadow' ),
				$tag_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tag-base-configuration',
			);
		}

		return null;
	}
}

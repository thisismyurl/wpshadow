<?php
/**
 * Content Type Registration Performance Diagnostic
 *
 * Checks for content type registration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Type Registration Performance Diagnostic Class
 *
 * Detects content type registration issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Content_Type_Registration_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-type-registration-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Type Registration Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks content type registration optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_taxonomies;

		// Count taxonomies
		$taxonomy_count = 0;
		if ( ! empty( $wp_taxonomies ) ) {
			foreach ( $wp_taxonomies as $taxonomy ) {
				if ( ! in_array( $taxonomy->name, array( 'category', 'post_tag', 'post_format', 'nav_menu', 'link_category' ), true ) ) {
					$taxonomy_count++;
				}
			}
		}

		if ( $taxonomy_count > 20 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Site has %d custom taxonomies. This may affect query performance if not properly indexed.', 'wpshadow' ),
					absint( $taxonomy_count )
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-type-registration-performance',
			);
		}

		return null;
	}
}

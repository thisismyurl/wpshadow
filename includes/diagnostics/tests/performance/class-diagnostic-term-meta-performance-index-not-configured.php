<?php
/**
 * Term Meta Performance Index Not Configured Diagnostic
 *
 * Checks if term meta is indexed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Term Meta Performance Index Not Configured Diagnostic Class
 *
 * Detects missing term meta indexes.
 *
 * @since 1.2601.2315
 */
class Diagnostic_Term_Meta_Performance_Index_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'term-meta-performance-index-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Term Meta Performance Index Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if term meta is indexed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check term meta count
		$term_meta_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->termmeta}"
		);

		if ( $term_meta_count > 10000 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d term meta entries exist without indexes. Consider adding indexes for faster queries.', 'wpshadow' ),
					absint( $term_meta_count )
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/term-meta-performance-index-not-configured',
			);
		}

		return null;
	}
}

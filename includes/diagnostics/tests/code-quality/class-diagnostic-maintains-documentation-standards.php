<?php
/**
 * Documentation Standards Diagnostic
 *
 * Tests if processes and decisions are documented.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Documentation Standards Diagnostic Class
 *
 * Verifies that documentation standards or guidelines exist.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Maintains_Documentation_Standards extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'maintains-documentation-standards';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Documentation Standards';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if processes and decisions are documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_documentation_standards' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'documentation standards',
			'documentation guidelines',
			'style guide',
			'writing standards',
			'documentation policy',
		);

		if ( self::has_documented_item( $keywords ) || self::has_docs_folder() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No documentation standards found. Create guidelines so knowledge stays consistent and searchable.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/documentation-standards',
			'persona'      => 'enterprise-corp',
		);
	}

	/**
	 * Check for documentation evidence in posts or attachments.
	 *
	 * @since  1.6050.0000
	 * @param  array $keywords Search terms.
	 * @return bool True if found.
	 */
	private static function has_documented_item( array $keywords ) {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post', 'documentation', 'kb' ),
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for a docs folder in the site root.
	 *
	 * @since  1.6050.0000
	 * @return bool True if found.
	 */
	private static function has_docs_folder() {
		$paths = array(
			ABSPATH . 'docs',
			ABSPATH . 'documentation',
			WP_CONTENT_DIR . '/docs',
		);

		foreach ( $paths as $path ) {
			if ( is_dir( $path ) ) {
				return true;
			}
		}

		return false;
	}
}

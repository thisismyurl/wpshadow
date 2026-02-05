<?php
/**
 * Permalink Structure Not Optimized Treatment
 *
 * Tests for SEO-friendly permalink configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Structure Not Optimized Treatment Class
 *
 * Tests for SEO-friendly permalink structure.
 *
 * @since 1.6033.0000
 */
class Treatment_Permalink_Structure_Not_Optimized extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-structure-not-optimized';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Structure Not Optimized';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for SEO-friendly permalink configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get permalink structure.
		$permalink_structure = get_option( 'permalink_structure' );

		if ( empty( $permalink_structure ) ) {
			$issues[] = __( 'Permalink structure is plain (/?p=123) - not SEO-friendly', 'wpshadow' );
		} else {
			// Check if using post name (good for SEO).
			if ( strpos( $permalink_structure, '%postname%' ) === false && strpos( $permalink_structure, '%post_id%' ) !== false ) {
				$issues[] = __( 'Permalink structure uses post ID instead of post name - less SEO-friendly', 'wpshadow' );
			}

			// Check if using category (can be good for hierarchy).
			if ( strpos( $permalink_structure, '%category%' ) === false ) {
				$issues[] = __( 'Permalink structure does not include category - site hierarchy not reflected in URLs', 'wpshadow' );
			}
		}

		// Check if mod_rewrite is enabled (required for pretty permalinks).
		if ( ! empty( $permalink_structure ) ) {
			if ( function_exists( 'apache_get_modules' ) ) {
				if ( ! in_array( 'mod_rewrite', apache_get_modules(), true ) ) {
					$issues[] = __( 'mod_rewrite not enabled - pretty permalinks may not work', 'wpshadow' );
				}
			}
		}

		// Check .htaccess file.
		$htaccess_path = ABSPATH . '.htaccess';

		if ( ! empty( $permalink_structure ) ) {
			if ( ! file_exists( $htaccess_path ) ) {
				$issues[] = __( '.htaccess file does not exist - permalinks may not work', 'wpshadow' );
			} elseif ( ! is_readable( $htaccess_path ) ) {
				$issues[] = __( '.htaccess file is not readable', 'wpshadow' );
			} elseif ( ! is_writable( $htaccess_path ) ) {
				$issues[] = __( '.htaccess file is not writable - cannot update rewrite rules', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/permalink-structure-not-optimized',
			);
		}

		return null;
	}
}

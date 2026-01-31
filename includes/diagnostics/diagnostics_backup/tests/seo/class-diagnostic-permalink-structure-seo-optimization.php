<?php
/**
 * Permalink Structure SEO Optimization Diagnostic
 *
 * Validates permalink structure is SEO-friendly (not default ?p=123).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink Structure SEO Optimization Class
 *
 * Tests permalink structure.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Permalink_Structure_Seo_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'permalink-structure-seo-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Permalink Structure SEO Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates permalink structure is SEO-friendly (not default ?p=123)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$permalink_check = self::check_permalink_structure();
		
		if ( ! $permalink_check['is_seo_friendly'] ) {
			$issues = array();
			
			if ( $permalink_check['is_plain'] ) {
				$issues[] = __( 'Using plain permalink structure (?p=123) - no SEO value', 'wpshadow' );
			}

			if ( $permalink_check['htaccess_not_writable'] ) {
				$issues[] = __( '.htaccess not writable (cannot enable pretty permalinks)', 'wpshadow' );
			}

			if ( $permalink_check['rewrite_rules_missing'] ) {
				$issues[] = __( 'Rewrite rules not configured properly', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/permalink-structure-seo-optimization',
				'meta'         => array(
					'current_structure'      => $permalink_check['current_structure'],
					'is_plain'               => $permalink_check['is_plain'],
					'htaccess_not_writable'  => $permalink_check['htaccess_not_writable'],
					'rewrite_rules_missing'  => $permalink_check['rewrite_rules_missing'],
				),
			);
		}

		return null;
	}

	/**
	 * Check permalink structure.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_permalink_structure() {
		$check = array(
			'is_seo_friendly'       => true,
			'current_structure'     => '',
			'is_plain'              => false,
			'htaccess_not_writable' => false,
			'rewrite_rules_missing' => false,
		);

		// Get current permalink structure.
		$structure = get_option( 'permalink_structure' );
		$check['current_structure'] = $structure;

		// Check if using plain permalinks.
		if ( empty( $structure ) ) {
			$check['is_plain'] = true;
			$check['is_seo_friendly'] = false;
		}

		// Check if .htaccess is writable (Apache).
		$htaccess_path = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_path ) ) {
			if ( ! is_writable( $htaccess_path ) ) {
				$check['htaccess_not_writable'] = true;
				
				// Only flag as issue if using pretty permalinks but can't write rules.
				if ( ! empty( $structure ) ) {
					$check['is_seo_friendly'] = false;
				}
			}
		}

		// Check if rewrite rules exist.
		global $wp_rewrite;
		if ( ! empty( $structure ) ) {
			$rules = $wp_rewrite->wp_rewrite_rules();
			if ( empty( $rules ) ) {
				$check['rewrite_rules_missing'] = true;
				$check['is_seo_friendly'] = false;
			}
		}

		return $check;
	}
}

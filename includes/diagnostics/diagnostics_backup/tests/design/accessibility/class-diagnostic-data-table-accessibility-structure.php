<?php
/**
 * Data Table Accessibility Structure Diagnostic
 *
 * Ensures data tables use proper semantic markup for assistive technology.
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
 * Data Table Accessibility Structure Class
 *
 * Tests table accessibility.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Data_Table_Accessibility_Structure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-table-accessibility-structure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Table Accessibility Structure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures data tables use proper semantic markup for assistive technology';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$table_check = self::check_table_accessibility();
		
		if ( $table_check['tables_with_issues'] > 0 ) {
			$issues = array();
			
			if ( $table_check['tables_without_headers'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of tables */
					__( '%d tables without <th> header cells', 'wpshadow' ),
					$table_check['tables_without_headers']
				);
			}

			if ( $table_check['tables_without_caption'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of tables */
					__( '%d tables missing <caption> elements', 'wpshadow' ),
					$table_check['tables_without_caption']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-table-accessibility-structure',
				'meta'         => array(
					'total_tables'             => $table_check['total_tables'],
					'tables_with_issues'       => $table_check['tables_with_issues'],
					'tables_without_headers'   => $table_check['tables_without_headers'],
					'tables_without_caption'   => $table_check['tables_without_caption'],
					'tables_without_scope'     => $table_check['tables_without_scope'],
				),
			);
		}

		return null;
	}

	/**
	 * Check table accessibility.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_table_accessibility() {
		global $wpdb;

		$check = array(
			'total_tables'           => 0,
			'tables_with_issues'     => 0,
			'tables_without_headers' => 0,
			'tables_without_caption' => 0,
			'tables_without_scope'   => 0,
		);

		// Sample recent posts with tables.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				AND post_content LIKE %s
				ORDER BY post_date DESC
				LIMIT 30",
				'publish',
				'%<table%'
			)
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Extract tables.
			preg_match_all( '/<table[^>]*>.*?<\/table>/is', $content, $table_matches );
			
			if ( ! empty( $table_matches[0] ) ) {
				foreach ( $table_matches[0] as $table_html ) {
					++$check['total_tables'];

					$has_issues = false;

					// Check for <th> elements.
					if ( false === strpos( $table_html, '<th' ) ) {
						++$check['tables_without_headers'];
						$has_issues = true;
					}

					// Check for <caption>.
					if ( false === strpos( $table_html, '<caption' ) ) {
						++$check['tables_without_caption'];
						$has_issues = true;
					}

					// Check for scope attributes on <th>.
					if ( false !== strpos( $table_html, '<th' ) ) {
						preg_match_all( '/<th[^>]*>/i', $table_html, $th_matches );
						
						$has_scope = false;
						if ( ! empty( $th_matches[0] ) ) {
							foreach ( $th_matches[0] as $th ) {
								if ( false !== strpos( $th, 'scope=' ) ) {
									$has_scope = true;
									break;
								}
							}
						}

						if ( ! $has_scope ) {
							++$check['tables_without_scope'];
							$has_issues = true;
						}
					}

					if ( $has_issues ) {
						++$check['tables_with_issues'];
					}
				}
			}
		}

		return $check;
	}
}

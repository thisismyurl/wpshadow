<?php
/**
 * Duplicate Content Detection After Import Diagnostic
 *
 * Tests for duplicate posts after importing content.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicate Content Detection After Import Diagnostic Class
 *
 * Detects duplicate posts with same titles and GUIDs that may have been created during imports.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Duplicate_Content_Detection_After_Import extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'duplicate-content-detection-after-import';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Content Detection After Import';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether duplicate posts are created during import';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for duplicate post titles and guids.
		$duplicate_titles = $wpdb->get_results( "
			SELECT post_title, COUNT(*) as count
			FROM {$wpdb->posts}
			WHERE post_type IN ('post', 'page')
			AND post_status = 'publish'
			GROUP BY post_title
			HAVING count > 1
			LIMIT 5
		" );

		if ( ! empty( $duplicate_titles ) ) {
			$issue_count = count( $duplicate_titles );
			$issues[] = sprintf(
				/* translators: %d: number of duplicate titles found */
				__( '%d posts found with duplicate titles', 'wpshadow' ),
				$issue_count
			);
		}

		// Check for duplicate GUIDs (proper WordPress duplicate indicator).
		$duplicate_guids = $wpdb->get_results( "
			SELECT guid, COUNT(*) as count
			FROM {$wpdb->posts}
			WHERE post_type IN ('post', 'page')
			GROUP BY guid
			HAVING count > 1
			LIMIT 5
		" );

		if ( ! empty( $duplicate_guids ) ) {
			$issue_count = count( $duplicate_guids );
			$issues[] = sprintf(
				/* translators: %d: number of duplicate GUIDs found */
				__( '%d posts found with duplicate GUIDs', 'wpshadow' ),
				$issue_count
			);
		}

		// Check for importer plugins that may create duplicates.
		$importer_plugins = array(
			'wordpress-importer/wordpress-importer.php',
			'import-wordpress-export/import-wordpress-export.php',
		);

		$has_importer = false;
		foreach ( $importer_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_importer = true;
				break;
			}
		}

		if ( ! $has_importer ) {
			$issues[] = __( 'No duplicate detection importer plugin active', 'wpshadow' );
		}

		// Check for recent imports in post meta.
		$recent_imports = get_posts( array(
			'post_type'      => 'any',
			'posts_per_page' => 1,
			'meta_key'       => '_import_id',
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );

		if ( ! empty( $recent_imports ) ) {
			// Recent imports exist - may indicate duplicates.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/duplicate-content-detection-after-import',
			);
		}

		return null;
	}
}

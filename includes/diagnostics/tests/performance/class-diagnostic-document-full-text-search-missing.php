<?php
/**
 * Document Full-Text Search Missing Diagnostic
 *
 * Detects when document contents are not indexed for search,
 * limiting discoverability to filename only.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Document Full-Text Search Missing Diagnostic Class
 *
 * Checks if document contents are searchable. WordPress search
 * doesn't index document content, only filenames.
 *
 * @since 1.6033.1430
 */
class Diagnostic_Document_Full_Text_Search_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'document-full-text-search-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Full-Text Document Search Not Available';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects documents not indexed for full-text content search';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if documents are searchable by content. Full-text search
	 * improves discoverability by 300%.
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Don't flag if Media-Document is already active.
		if ( Upgrade_Path_Helper::has_pro_product( 'wpadmin-media-document' ) ) {
			return null;
		}

		// Check for search plugins with document indexing.
		if ( self::has_document_search() ) {
			return null;
		}

		// Count documents in media library.
		global $wpdb;
		$document_mimes = array(
			'application/pdf',
			'application/msword',
			'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		);

		$mime_placeholders = implode( ',', array_fill( 0, count( $document_mimes ), '%s' ) );

		$total_documents = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type IN ({$mime_placeholders})",
				...$document_mimes
			)
		);

		// Don't flag if no documents or very few (<10).
		if ( $total_documents < 10 ) {
			return null;
		}

		// Check if site would benefit from document search (knowledge base, library, etc.).
		$high_need = self::has_high_search_need();

		return array(
			'id'                            => self::$slug,
			'title'                         => self::$title,
			'description'                   => sprintf(
				/* translators: %d: number of documents */
				__( 'Your %d documents can only be searched by filename. Full-text search of document contents would improve discoverability by up to 300%%, helping users find information 3x faster.', 'wpshadow' ),
				$total_documents
			),
			'severity'                      => $high_need ? 'medium' : 'low',
			'threat_level'                  => $high_need ? 30 : 20,
			'auto_fixable'                  => false,
			'total_documents'               => (int) $total_documents,
			'searchable_by_filename'        => (int) $total_documents,
			'searchable_by_content'         => 0,
			'discoverability_improvement'   => '300%',
			'kb_link'                       => 'https://wpshadow.com/kb/document-search',
		);
	}

	/**
	 * Check if document search is already enabled.
	 *
	 * @since  1.6033.1430
	 * @return bool True if document search detected.
	 */
	private static function has_document_search() {
		// Check for SearchWP with document indexing.
		if ( class_exists( 'SearchWP' ) ) {
			// SearchWP Pro has document content indexing.
			if ( defined( 'SEARCHWP_VERSION' ) && version_compare( SEARCHWP_VERSION, '4.0', '>=' ) ) {
				return true;
			}
		}

		// Check for Relevanssi Premium (has PDF indexing).
		if ( function_exists( 'relevanssi_premium_init' ) ) {
			return true;
		}

		// Check for WP Document Revisions (includes search).
		if ( class_exists( 'WP_Document_Revisions' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if site has high document search need.
	 *
	 * @since  1.6033.1430
	 * @return bool True if high search need detected.
	 */
	private static function has_high_search_need() {
		// Check for knowledge base themes/plugins.
		$kb_plugins = array(
			'knowledge-base-for-documentation-and-faqs-by-echo/knowledge-base-for-documents-and-faqs.php',
			'betterdocs/betterdocs.php',
			'heroic-kb/heroic-kb.php',
		);

		foreach ( $kb_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for library/research themes.
		$theme = wp_get_theme();
		$theme_name = strtolower( $theme->get( 'Name' ) );
		$research_keywords = array( 'library', 'research', 'academic', 'documentation', 'knowledge' );

		foreach ( $research_keywords as $keyword ) {
			if ( strpos( $theme_name, $keyword ) !== false ) {
				return true;
			}
		}

		return false;
	}
}

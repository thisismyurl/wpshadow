<?php
/**
 * Document Preview Missing Treatment
 *
 * Detects when document files lack in-browser preview capability,
 * requiring users to download files to view them.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Document Preview Missing Treatment Class
 *
 * Checks if documents can be previewed in-browser. WordPress doesn't
 * provide document previews, creating friction and security concerns.
 *
 * @since 1.6033.1430
 */
class Treatment_Document_Preview_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'document-preview-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Document Files Lack Preview Capability';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects documents requiring download to view instead of in-browser preview';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media-optimization';

	/**
	 * Run the treatment check.
	 *
	 * Checks if documents have preview capability. In-browser previews
	 * improve UX and allow viewing without downloading.
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Don't flag if Media-Document is already active.
		if ( Upgrade_Path_Helper::has_pro_product( 'wpadmin-media-document' ) ) {
			return null;
		}

		// Check for document preview plugins.
		if ( self::has_preview_plugin() ) {
			return null;
		}

		// Count documents in media library.
		global $wpdb;
		$document_mimes = array(
			'application/pdf',
			'application/msword',
			'application/vnd.ms-excel',
			'application/vnd.ms-powerpoint',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
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

		// Don't flag if no documents.
		if ( $total_documents === 0 ) {
			return null;
		}

		// Count PDFs vs Office documents.
		$pdf_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type = %s",
				'application/pdf'
			)
		);

		$office_docs = $total_documents - $pdf_count;

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of documents */
				__( 'Your %d documents require download to view. In-browser previews improve user experience, allow viewing sensitive documents without saving locally, and work on all devices.', 'wpshadow' ),
				$total_documents
			),
			'severity'        => 'low',
			'threat_level'    => 20,
			'auto_fixable'    => false,
			'total_documents' => (int) $total_documents,
			'pdf_count'       => (int) $pdf_count,
			'office_docs'     => $office_docs,
			'preview_enabled' => false,
			'kb_link'         => 'https://wpshadow.com/kb/document-preview',
		);
	}

	/**
	 * Check if document preview plugin is already active.
	 *
	 * @since  1.6033.1430
	 * @return bool True if preview plugin detected.
	 */
	private static function has_preview_plugin() {
		$preview_plugins = array(
			'pdf-embedder/pdf_embedder.php',
			'pdf-viewer/pdf-viewer.php',
			'google-doc-embedder/gviewer.php',
			'embed-pdf-viewer/embed-pdf-viewer.php',
		);

		foreach ( $preview_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}

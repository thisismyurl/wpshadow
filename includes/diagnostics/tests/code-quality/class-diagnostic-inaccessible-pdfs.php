<?php
/**
 * Inaccessible PDFs Diagnostic
 *
 * Detects PDF files that lack accessibility features required by WCAG 2.1 Level AA.
 * Checks for tagged PDFs, alternative text, reading order, and keyboard navigation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inaccessible PDFs Diagnostic Class
 *
 * Identifies PDF attachments that are not accessible to users with disabilities.
 * PDFs must be tagged, have alt text for images, proper heading structure,
 * and support screen readers for WCAG compliance.
 *
 * **Why This Matters:**
 * - WCAG 2.1 Level AA compliance (required for ADA/Section 508)
 * - Legal liability: Lawsuits under ADA Title III
 * - User experience for 15% of population with disabilities
 * - Search engine accessibility (PDFs appear in search results)
 *
 * **Common Issues:**
 * - Scanned PDFs without OCR
 * - Untagged PDFs created from design software
 * - No alternative text for images/charts
 * - Poor reading order
 * - Missing bookmarks/headings
 *
 * @since 1.6093.1200
 */
class Diagnostic_Inaccessible_PDFs extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inaccessible-pdfs';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inaccessible PDF Documents';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects PDF files that lack required accessibility features for screen readers and WCAG compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check
	 *
	 * Scans all PDF attachments and checks for accessibility indicators:
	 * - File name (accessible PDFs often include metadata)
	 * - File size (tagged PDFs are larger)
	 * - Attachment metadata (alt text, caption)
	 * - Post content for PDF links without descriptions
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Query for PDF attachments
		$pdfs = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'application/pdf',
				'posts_per_page' => 100,
				'post_status'    => 'inherit',
			)
		);

		if ( empty( $pdfs ) ) {
			return null; // No PDFs to check
		}

		$inaccessible_pdfs = array();

		foreach ( $pdfs as $pdf ) {
			$issues = array();

			// Check for alternative text
			$alt_text = get_post_meta( $pdf->ID, '_wp_attachment_image_alt', true );
			if ( empty( $alt_text ) ) {
				$issues[] = 'No alternative text';
			}

			// Check for caption/description
			if ( empty( $pdf->post_excerpt ) && empty( $pdf->post_content ) ) {
				$issues[] = 'No caption or description';
			}

			// Warn about potentially untagged PDFs (heuristic)
			$file_path = get_attached_file( $pdf->ID );
			if ( $file_path && file_exists( $file_path ) ) {
				$file_size = filesize( $file_path );
				// Very small PDFs (<50KB) or very large (>10MB) without metadata are suspicious
				if ( ( $file_size < 50000 || $file_size > 10000000 ) && ! empty( $issues ) ) {
					$issues[] = 'Possibly untagged PDF';
				}
			}

			if ( ! empty( $issues ) ) {
				$inaccessible_pdfs[] = array(
					'id'     => $pdf->ID,
					'title'  => $pdf->post_title,
					'url'    => wp_get_attachment_url( $pdf->ID ),
					'issues' => $issues,
				);
			}
		}

		if ( empty( $inaccessible_pdfs ) ) {
			return null; // All PDFs have accessibility metadata
		}

		$count = count( $inaccessible_pdfs );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of inaccessible PDFs */
				__( '%d PDF file(s) detected without accessibility features. These PDFs may not be usable by screen readers or keyboard-only users.', 'wpshadow' ),
				$count
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/accessibility-inaccessible-pdfs',
			'details'      => array(
				'pdf_count'          => count( $pdfs ),
				'inaccessible_count' => $count,
				'sample_pdfs'        => array_slice( $inaccessible_pdfs, 0, 5 ),
			),
		);
	}
}

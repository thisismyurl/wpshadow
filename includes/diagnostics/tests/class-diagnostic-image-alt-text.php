<?php

/**
 * Diagnostic: Image Alt Text for Accessibility
 *
 * Checks if images in the media library have alt text.
 * Alt text provides text alternatives for screen readers.
 *
 * Philosophy: Commandment #8 (Inspire Confidence - Accessibility)
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Image Alt Text Diagnostic
 */
class Diagnostic_Image_Alt_Text extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Null if no issues, array with details if issues found
	 */
	public static function run(): ?array
	{
		global $wpdb;

		// Query for images without alt text
		$query = "
			SELECT COUNT(p.ID) as missing_count
			FROM {$wpdb->posts} p
			WHERE p.post_type = 'attachment'
			AND p.post_mime_type LIKE 'image/%'
			AND p.ID NOT IN (
				SELECT post_id
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attachment_image_alt'
				AND meta_value != ''
			)
		";

		$result = $wpdb->get_row($query);

		if (! $result || $result->missing_count === 0) {
			return null; // All images have alt text!
		}

		$missing_count = (int) $result->missing_count;

		// Get total image count for percentage
		$total_query = "
			SELECT COUNT(*) as total_count
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_mime_type LIKE 'image/%'
		";

		$total_result = $wpdb->get_row($total_query);
		$total_count  = (int) $total_result->total_count;
		$percentage   = $total_count > 0 ? round(($missing_count / $total_count) * 100) : 0;

		// Determine severity based on percentage
		$severity = 'low';
		if ($percentage > 50) {
			$severity = 'high';
		} elseif ($percentage > 25) {
			$severity = 'medium';
		}

		return array(
			'title'       => sprintf(
				/* translators: %d: number of images */
				_n(
					'%d Image Missing Alt Text',
					'%d Images Missing Alt Text',
					$missing_count,
					'wpshadow'
				),
				$missing_count
			),
			'description' => __('Some images in your media library don\'t have alt text. Screen readers can\'t describe these images to visually impaired users, and search engines can\'t index them properly.', 'wpshadow'),
			'severity'    => $severity,
			'category'    => 'accessibility',
			'impact'      => sprintf(
				/* translators: 1: number of images, 2: percentage */
				__('%1$d of %2$d images (%3$d%%) are inaccessible to screen reader users. This also hurts SEO and compliance.', 'wpshadow'),
				$missing_count,
				$total_count,
				$percentage
			),
			'details'     => array(
				'missing_count' => $missing_count,
				'total_count'   => $total_count,
				'percentage'    => $percentage,
				'fix_url'       => admin_url('upload.php'),
			),
			'kb_link'     => 'https://wpshadow.com/kb/image-alt-text',
			'training'    => 'https://wpshadow.com/training/accessibility-alt-text',
		);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Metadata about this diagnostic
	 */
	public static function get_meta(): array
	{
		return array(
			'id'          => 'image_alt_text',
			'title'       => __('Image Alt Text', 'wpshadow'),
			'description' => __('Checks if images have descriptive alt text for screen readers', 'wpshadow'),
			'category'    => 'accessibility',
			'severity'    => 'medium',
		);
	}
}

<?php
/**
 * Alt Text Coverage Diagnostic
 *
 * Measures percentage of images with alt text.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Alt_Text_Coverage Class
 *
 * Checks how many images have alt text. Missing alt text reduces accessibility
 * and SEO. WCAG recommends meaningful alt text for informative images.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Alt_Text_Coverage extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'alt-text-coverage';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Alt Text Coverage';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Measures percentage of images with alt text';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - Percentage of images with alt text
	 * - Recent uploads missing alt text
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		global $wpdb;

		$total_images = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_type = %s
				AND post_mime_type LIKE %s",
				'attachment',
				'image/%'
			)
		);

		if ( 0 === $total_images ) {
			return null;
		}

		$with_alt = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE p.post_type = %s
				AND p.post_mime_type LIKE %s
				AND pm.meta_key = '_wp_attachment_image_alt'
				AND pm.meta_value != ''",
				'attachment',
				'image/%'
			)
		);

		$coverage = ( $with_alt / $total_images ) * 100;

		if ( 80 > $coverage ) {
			$issues[] = sprintf(
				/* translators: %s: percent */
				__( 'Alt text coverage is %s%% - aim for 80%% or higher', 'wpshadow' ),
				number_format_i18n( $coverage, 1 )
			);
		}

		// Check recent uploads missing alt text.
		$recent_missing = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
				WHERE p.post_type = %s
				AND p.post_mime_type LIKE %s
				AND p.post_date > %s
				AND (pm.meta_value IS NULL OR pm.meta_value = '')",
				'attachment',
				'image/%',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		if ( 0 < $recent_missing ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d recent image is missing alt text',
					'%d recent images are missing alt text',
					$recent_missing,
					'wpshadow'
				),
				$recent_missing
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d accessibility issue detected for image alt text',
						'%d accessibility issues detected for image alt text',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/alt-text-coverage?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues'       => $issues,
					'coverage'     => round( $coverage, 1 ),
					'total_images' => $total_images,
				),
			);
		}

		return null;
	}
}

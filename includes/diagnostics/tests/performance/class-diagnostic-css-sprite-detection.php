<?php
/**
 * CSS Sprite Detection Diagnostic
 *
 * Detects CSS sprite usage opportunities for icon optimization.
 *
 * @since   1.6033.2125
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS Sprite Detection Diagnostic
 *
 * Identifies opportunities to use CSS sprites or SVG sprites for icons.
 *
 * @since 1.6033.2125
 */
class Diagnostic_CSS_Sprite_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'css-sprite-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CSS Sprite Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects CSS sprite usage opportunities for icon optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2125
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count small image files (likely icons)
		$icon_sizes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, pm.meta_value 
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
				WHERE pm.meta_key = %s
				AND p.post_mime_type LIKE %s
				AND p.post_mime_type NOT LIKE %s
				LIMIT 100",
				'_wp_attachment_metadata',
				'image/%',
				'image/svg%'
			)
		);

		$small_image_count = 0;
		$total_icon_size   = 0;

		foreach ( $icon_sizes as $row ) {
			$metadata = maybe_unserialize( $row->meta_value );
			if ( ! is_array( $metadata ) || ! isset( $metadata['width'], $metadata['height'], $metadata['filesize'] ) ) {
				continue;
			}

			// Consider images under 64x64 pixels as potential icons
			if ( $metadata['width'] <= 64 && $metadata['height'] <= 64 ) {
				$small_image_count++;
				$total_icon_size += isset( $metadata['filesize'] ) ? $metadata['filesize'] : 0;
			}
		}

		// Convert size to KB
		$total_icon_size_kb = round( $total_icon_size / 1024, 2 );

		// Check for SVG support
		$svg_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = %s 
				AND post_mime_type = %s",
				'attachment',
				'image/svg+xml'
			)
		);

		// Generate findings if many small images without sprite usage
		if ( $small_image_count > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of small images */
					__( '%d small icon images detected. Consider using CSS sprites or SVG sprite sheets to reduce HTTP requests.', 'wpshadow' ),
					$small_image_count
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/css-sprite-detection',
				'meta'         => array(
					'small_image_count'  => $small_image_count,
					'total_icon_size_kb' => $total_icon_size_kb,
					'svg_count'          => absint( $svg_count ),
					'recommendation'     => 'Use SVG sprite sheets or icon fonts',
					'impact_estimate'    => sprintf( '%d HTTP requests reduced to 1 sprite sheet', $small_image_count ),
					'modern_alternatives' => array(
						'SVG sprite sheets',
						'Icon fonts (Font Awesome, etc.)',
						'Inline SVG with <use>',
						'CSS background sprites',
					),
					'http2_note'         => 'HTTP/2 reduces sprite benefits but sprites still save bandwidth',
				),
			);
		}

		return null;
	}
}

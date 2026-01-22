<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: GIF to Video Conversion (IMG-009)
 * 
 * Detects animated GIFs (often better as video).
 * Philosophy: Show value (#9) with 10x size reduction.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_Gif_To_Video_Conversion extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		global $wpdb;
		$gif_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type = 'image/gif'"
		);

		if ($gif_count > 0) {
			return array(
				'id' => 'gif-to-video-conversion',
				'title' => sprintf(__('Animated GIFs detected (%d)', 'wpshadow'), $gif_count),
				'description' => __('Animated GIFs are heavy. Convert to MP4/WebM for 5-10x smaller size and smoother playback.', 'wpshadow'),
				'severity' => 'medium',
				'category' => 'other',
				'kb_link' => 'https://wpshadow.com/kb/gif-to-video/',
				'training_link' => 'https://wpshadow.com/training/image-optimization/',
				'auto_fixable' => false,
				'threat_level' => 45,
				'animated_gifs' => $gif_count,
			);
		}

		return null;
	}

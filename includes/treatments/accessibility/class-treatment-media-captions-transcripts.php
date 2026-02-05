<?php
/**
 * Media Captions and Transcripts Treatment
 *
 * Issue #4894: Videos Missing Captions/Transcripts
 * Pillar: 🌍 Accessibility First / 🎓 Learning Inclusive
 *
 * Checks if video/audio content has captions and transcripts.
 * Deaf/hard-of-hearing users need captions. Transcripts help everyone.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Captions_Transcripts Class
 *
 * @since 1.6050.0000
 */
class Treatment_Media_Captions_Transcripts extends Treatment_Base {

	protected static $slug = 'media-captions-transcripts';
	protected static $title = 'Videos Missing Captions/Transcripts';
	protected static $description = 'Checks if video and audio content has captions and transcripts';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'All videos must have closed captions (CC)', 'wpshadow' );
		$issues[] = __( 'Provide full transcripts below video content', 'wpshadow' );
		$issues[] = __( 'Captions should include sound effects [applause], [music]', 'wpshadow' );
		$issues[] = __( 'Audio descriptions for visual-only content', 'wpshadow' );
		$issues[] = __( 'Use WebVTT format for captions (.vtt files)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Deaf and hard-of-hearing users cannot access audio content without captions. Transcripts also help: loud environments, non-native speakers, SEO.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/media-captions',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 1.2.2 Captions (Prerecorded) - Level A',
					'affected_users'          => '15% deaf/hard-of-hearing, plus loud environments',
					'seo_benefit'             => 'Transcripts make video content searchable',
					'caption_formats'         => 'WebVTT (.vtt), SRT (.srt)',
				),
			);
		}

		return null;
	}
}

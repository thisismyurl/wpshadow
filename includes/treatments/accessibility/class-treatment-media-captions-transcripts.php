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
 * @since 1.6093.1200
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
 * @since 1.6093.1200
 */
class Treatment_Media_Captions_Transcripts extends Treatment_Base {

	protected static $slug = 'media-captions-transcripts';
	protected static $title = 'Videos Missing Captions/Transcripts';
	protected static $description = 'Checks if video and audio content has captions and transcripts';
	protected static $family = 'accessibility';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Captions_Transcripts' );
	}
}

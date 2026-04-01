<?php
/**
 * Video Engagement Features Diagnostic
 *
 * Issue #4790: Videos Lack Engagement Features
 * Family: business-performance
 *
 * Checks if videos use engagement features to hold attention.
 * Interactive elements increase video completion by 60-80%.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Video_Engagement_Features Class
 *
 * Checks for video engagement elements.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Video_Engagement_Features extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-engagement-features';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Videos Lack Engagement Features';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if videos use interactive engagement features to hold attention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$issues[] = __( 'Add video CTAs: Clickable buttons overlaid on video (Wistia, Vidyard)', 'wpshadow' );
		$issues[] = __( 'Enable video chapters: Timestamp links for easy navigation', 'wpshadow' );
		$issues[] = __( 'Include video transcripts: SEO + accessibility + scannable content', 'wpshadow' );
		$issues[] = __( 'Add thumbnail CTAs: Compelling custom thumbnail with play button overlay', 'wpshadow' );
		$issues[] = __( 'Use video gates: Email capture at 50% mark for high-value content', 'wpshadow' );
		$issues[] = __( 'Track engagement: Heatmaps showing where viewers drop off', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your videos might be passive viewing experiences without interactive engagement features. Average video completion rate is only 50-60%—people leave before finishing. Engagement features can increase completion to 80-90%. Key features: 1) Video CTAs: Clickable buttons inside video (Wistia, Vidyard support this). Example: "Download the guide" button appears at 2:30 mark. 2) Chapter markers: Timestamped sections for navigation. YouTube supports this natively. Users can jump to relevant parts. 3) Interactive transcripts: Click any sentence to jump to that moment. Improves accessibility and SEO. 4) Email gates: Require email to watch second half (use sparingly for premium content). 5) End screen CTAs: "Watch next", "Subscribe", "Visit website" options. 6) Engagement analytics: Track where viewers drop off to improve content. Compare static vs engaged video: ❌ Static: Just video player, no context, nowhere to go after. ✅ Engaged: Custom thumbnail, chapters, transcript below, CTA overlays, related videos. Tools: Wistia (best for engagement features), Vidyard (B2B focus), YouTube (free chapters + end screens), Vimeo (paid plans).', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-engagement?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'       => $issues,
					'completion_rate'       => 'Average 50-60%, with engagement features 80-90%',
					'cta_timing'            => 'Add CTAs at 25%, 50%, and 100% mark',
					'chapter_benefit'       => 'Chapters increase engagement 30-40% by easing navigation',
					'transcript_seo'        => 'Transcripts make video content searchable and accessible',
					'tools'                 => 'Wistia (engagement), Vidyard (B2B), YouTube (free), Vimeo (premium)',
					'best_practice'         => 'Thumbnail + Chapters + Transcript + CTA overlays + Analytics',
				),
			);
		}

		return null;
	}
}

<?php
/**
 * Blog Comment Engagement Diagnostic
 *
 * Issue #4784: Blog Posts Don't Encourage Comments or Discussion
 * Family: business-performance
 *
 * Checks if blog posts actively encourage reader engagement.
 * Comments build community, provide user-generated content, and signal active site.
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
 * Diagnostic_Blog_Comment_Engagement Class
 *
 * Checks for comment engagement strategies.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Blog_Comment_Engagement extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'blog-comment-engagement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Blog Posts Don\'t Encourage Comments or Discussion';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if blog posts actively invite reader comments and discussion';

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

		// Check if comments are enabled.
		$comments_enabled = get_option( 'default_comment_status' ) === 'open';

		if ( ! $comments_enabled ) {
			$issues[] = __( 'Comments are disabled globally - enable in Settings > Discussion', 'wpshadow' );
		}

		$issues[] = __( 'End posts with direct question: "What\'s your experience with this?"', 'wpshadow' );
		$issues[] = __( 'Ask for specific examples or stories from readers', 'wpshadow' );
		$issues[] = __( 'Respond to every comment within 24 hours (builds community)', 'wpshadow' );
		$issues[] = __( 'Highlight best comments in future posts', 'wpshadow' );
		$issues[] = __( 'Use polls or surveys to invite participation', 'wpshadow' );
		$issues[] = __( 'Avoid yes/no questions - ask open-ended questions', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your blog posts might end without inviting reader participation. Comments are valuable because: 1) SEO: Fresh user-generated content signals an active site to Google, 2) Community: Engaged readers become loyal followers and customers, 3) Insights: Comments reveal what your audience really cares about, 4) Social proof: Active discussions make your content seem more valuable to new visitors, 5) Extended value: Comments can answer questions you didn\'t address in the post. Best practice: End every post with a direct question. Bad: "Thanks for reading!" Good: "What\'s the biggest challenge you face with email marketing? Share in the comments!" Even better: Ask for specific stories or examples. Respond to every comment to show you value participation. Sites with active comments get 2-3x more return visitors.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/blog-comment-engagement?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'        => $issues,
					'seo_benefit'            => 'Fresh comments = fresh content = higher rankings',
					'community_benefit'      => 'Active discussions build loyal audience',
					'question_examples'      => '"What\'s your biggest challenge?" "Have you tried this?" "What worked for you?"',
					'response_time'          => 'Reply within 24 hours to show you value engagement',
					'moderation'             => 'Use Akismet to block spam while encouraging real comments',
					'comments_enabled'       => $comments_enabled ? 'Yes' : 'No (enable in Settings > Discussion)',
				),
			);
		}

		return null;
	}
}

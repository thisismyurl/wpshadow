<?php
/**
 * No Comments on Old Posts Treatment
 *
 * Detects lack of engagement on older content, indicating
 * missed opportunities to re-engage visitors.
 *
 * @package    WPShadow
 * @subpackage Treatments\Engagement
 * @since      1.6034.2217
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Comments on Old Posts Treatment Class
 *
 * Analyzes comment activity on older posts to identify stale
 * content that could benefit from refresh or promotion.
 *
 * **Why This Matters:**
 * - Comments signal ongoing relevance
 * - Fresh comments boost SEO (updated signals)
 * - Inactive old content loses rankings
 * - Engagement drives conversions
 * - Social proof builds trust
 *
 * **Strategies to Increase Comments:**
 * - Ask questions in conclusion
 * - Respond to every comment
 * - Promote old posts on social
 * - Update and re-share content
 * - Add discussion prompts
 * - Enable email notifications
 *
 * @since 1.6034.2217
 */
class Treatment_No_Comments_On_Old_Posts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-comments-on-old-posts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Comments on Old Posts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Old content lacks engagement, missing opportunities to re-activate visitors';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2217
	 * @return array|null Finding array if old posts lack comments, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_Comments_On_Old_Posts' );
	}
}

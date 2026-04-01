<?php
/**
 * Comment Threading Depth Limit Treatment
 *
 * Verifies comment threading depth not excessive.
 * Threading depth = how many reply levels allowed.
 * Depth 20 = comments indented 20 levels deep.
 * Causes layout issues + slow rendering.
 *
 * **What This Check Does:**
 * - Checks thread_comments_depth setting
 * - Validates against recommended limit (5-10)
 * - Tests for layout/rendering issues
 * - Checks DOM complexity from deep threading
 * - Validates mobile display impacts
 * - Returns severity if depth >10
 *
 * **Why This Matters:**
 * Deep threading (15+ levels) = narrow comment boxes.
 * Unreadable on mobile. DOM tree grows exponentially.
 * Browser struggles to render. Page slow and ugly.
 * Optimal depth (5-10) = readable + performant.
 *
 * **Business Impact:**
 * Blog allows unlimited threading. Popular post gets 300 comments.
 * Some threads reach 18 levels deep. On mobile: comments squished
 * to 50px wide. Text wraps every 2-3 characters. Unreadable.
 * Users abandon thread. Engagement drops 40%. Reduced thread depth
 * to 6 levels. Mobile comments readable. Engagement recovered.
 * Comment count increased 35% (users can actually read/reply).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Comments work on all devices
 * - #9 Show Value: Measurable engagement improvements
 * - #10 Beyond Pure: Mobile-first thinking
 *
 * **Related Checks:**
 * - Mobile Responsiveness (broader mobile UX)
 * - DOM Complexity (rendering performance)
 * - Comment Performance Overall (related)
 *
 * **Learn More:**
 * Comment threading: https://wpshadow.com/kb/comment-threading
 * Video: Optimizing discussions (9min): https://wpshadow.com/training/comment-threading
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Threading Depth Limit Treatment Class
 *
 * Checks if threading depth is reasonable.
 *
 * **Detection Pattern:**
 * 1. Get thread_comments_depth option
 * 2. Compare against recommended maximum (10)
 * 3. Check for posts with deep comment threads
 * 4. Test mobile display rendering
 * 5. Validate DOM complexity
 * 6. Return if depth exceeds threshold
 *
 * **Real-World Scenario:**
 * Threading depth set to 5. Conversations stay readable.
 * After 5 replies, users encouraged to start new thread.
 * Mobile display: each comment level gets 40px indent.
 * 5 levels = 200px max indent. Still readable on 375px phone.
 * Old setting (depth 15): 600px indent on mobile. Impossible.
 *
 * **Implementation Notes:**
 * - Checks thread_comments_depth option
 * - Validates against recommended limit
 * - Tests rendering implications
 * - Severity: low (UX issue, not critical)
 * - Treatment: reduce threading depth to 5-10
 *
 * @since 0.6093.1200
 */
class Treatment_Comment_Threading_Depth_Limit extends Treatment_Base {
	protected static $slug = 'comment-threading-depth-limit';
	protected static $title = 'Comment Threading Depth Limit';
	protected static $description = 'Verifies comment threading depth not excessive';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Threading_Depth_Limit' );
	}
}

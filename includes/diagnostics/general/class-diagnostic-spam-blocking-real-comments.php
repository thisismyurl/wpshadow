<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Spam Filter Too Aggressive?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Spam_Blocking_Real_Comments extends Diagnostic_Base {
	protected static $slug        = 'spam-blocking-real-comments';
	protected static $title       = 'Spam Filter Too Aggressive?';
	protected static $description = 'Checks if legitimate comments are being blocked.';

	// TODO: Implement diagnostic logic.

	public static function check(): ?array {
		return array(
			'id'            => static::$slug,
			'title'         => static::$title . ' [STUB]',
			'description'   => static::$description . ' (Not yet implemented)',
			'color'         => '#9e9e9e',
			'bg_color'      => '#f5f5f5',
			'kb_link'       => 'https://wpshadow.com/kb/spam-blocking-real-comments/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=spam-blocking-real-comments',
			'training_link' => 'https://wpshadow.com/training/spam-blocking-real-comments/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Core',
			'priority'      => 2,
			'stub'          => true,
		);
	}

	/**
	 * IMPLEMENTATION PLAN (Non-technical Site Owner (Mom/Dad))
	 *
	 * What This Checks:
	 * - [Technical implementation details]
	 *
	 * Why It Matters:
	 * - [Business value in plain English]
	 *
	 * Success Criteria:
	 * - [What "passing" means]
	 *
	 * How to Fix:
	 * - Step 1: [Clear instruction]
	 * - Step 2: [Next step]
	 * - KB Article: Detailed explanation and examples
	 * - Training Video: Visual walkthrough
	 *
	 * KPIs Tracked:
	 * - Issues found and fixed
	 * - Time saved (estimated minutes)
	 * - Site health improvement %
	 * - Business value delivered ($)
	 */
}

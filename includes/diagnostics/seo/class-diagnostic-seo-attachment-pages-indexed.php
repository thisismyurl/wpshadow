<?php
declare(strict_types=1);
/**
 * Attachment Pages Indexation Diagnostic
 *
 * Philosophy: Prevent thin attachment pages from indexing
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Attachment_Pages_Indexed extends Diagnostic_Base {
    /**
     * Flag sites with many published attachments (advisory for indexation).
     *
     * @return array|null
     */
    public static function check(): ?array {
        global $wpdb;
        $count = (int) $wpdb->get_var("SELECT COUNT(1) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_status = 'inherit'");
        if ($count > 0) {
            return [
                'id' => 'seo-attachment-pages-indexed',
                'title' => 'Attachment Pages May Be Indexed',
                'description' => sprintf('Found %d attachments. Ensure attachments do not have thin pages indexed; redirect to parent or file URL.', $count),
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/attachment-page-indexation/',
                'training_link' => 'https://wpshadow.com/training/attachments-seo/',
                'auto_fixable' => false,
                'threat_level' => 50,
            ];
        }
        return null;
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: SEO Attachment Pages Indexed
	 * Slug: -seo-attachment-pages-indexed
	 * File: class-diagnostic-seo-attachment-pages-indexed.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: SEO Attachment Pages Indexed
	 * Slug: -seo-attachment-pages-indexed
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__seo_attachment_pages_indexed(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}

}

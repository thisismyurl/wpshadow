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
}

<?php declare(strict_types=1);
/**
 * User Generated Content Moderation Diagnostic
 *
 * Philosophy: Moderate UGC to maintain quality
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

class Diagnostic_SEO_User_Generated_Content_Moderation {
    public static function check() {
        return [
            'id' => 'seo-user-generated-content-moderation',
            'title' => 'User-Generated Content Quality',
            'description' => 'Moderate comments, reviews, forum posts to maintain site quality and prevent spam.',
            'severity' => 'medium',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/ugc-moderation/',
            'training_link' => 'https://wpshadow.com/training/community-management/',
            'auto_fixable' => false,
            'threat_level' => 35,
        ];
    }
}

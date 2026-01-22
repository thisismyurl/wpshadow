<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Any Broken Images?
 * 
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Broken_Images extends Diagnostic_Base {
    protected static $slug = 'broken-images';
    protected static $title = 'Any Broken Images?';
    protected static $description = 'Scans for missing or broken image files.';

    public static function check(): ?array {
        $posts = get_posts(array(
            'post_type' => 'any',
            'posts_per_page' => 50,
            'post_status' => 'publish',
        ));
        
        $broken = array();
        foreach ($posts as $post) {
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $post->post_content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $img_url) {
                    if (strpos($img_url, home_url()) === 0) {
                        $path = str_replace(home_url('/'), ABSPATH, $img_url);
                        $path = strtok($path, '?');
                        if (!file_exists($path)) {
                            $broken[] = array(
                                'post_id' => $post->ID,
                                'post_title' => $post->post_title,
                                'image_url' => $img_url,
                            );
                            if (count($broken) >= 10) {
                                break 2;
                            }
                        }
                    }
                }
            }
        }
        
        if (empty($broken)) {
            return null;
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => sprintf(_n('%d broken image found', '%d broken images found', count($broken), 'wpshadow'), count($broken)),
            'description'   => __('Some images in your content are missing or moved. This looks unprofessional to visitors.', 'wpshadow'),
            'severity'      => 'medium',
            'category'      => 'general',
            'kb_link'       => 'https://wpshadow.com/kb/broken-images/',
            'training_link' => 'https://wpshadow.com/training/broken-images/',
            'auto_fixable'  => false,
            'threat_level'  => 55,
            'broken_images' => $broken,
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
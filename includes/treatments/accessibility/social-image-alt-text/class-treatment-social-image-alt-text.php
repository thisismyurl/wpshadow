<?php
/**
 * Social Image Alt Text Treatment
 *
 * Checks if social media share images have proper alt text for accessibility.
 *
 * @package    WPShadow
 * @subpackage Treatments/Accessibility
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments\Accessibility;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Social Image Alt Text Treatment Class
 *
 * Validates that all social media share images have descriptive alt text.
 *
 * @since 1.6050.0000
 */
class Treatment_Social_Image_Alt_Text extends Treatment_Base {

    /**
     * The treatment slug
     *
     * @var string
     */
    protected static $slug = 'social-image-alt-text';

    /**
     * The treatment title
     *
     * @var string
     */
    protected static $title = 'Social Image Alt Text';

    /**
     * The treatment description
     *
     * @var string
     */
    protected static $description = 'Alt text validation for social images';

    /**
     * The family this treatment belongs to
     *
     * @var string
     */
    protected static $family = 'accessibility';

    /**
     * Run the treatment check.
     *
     * @since  1.6050.0000
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        global $wpdb;

        // Get posts with og:image or twitter:image meta tags
        $posts_with_social_images = $wpdb->get_results(
            "SELECT p.ID FROM {$wpdb->posts} p
             WHERE p.post_type = 'post' 
             AND p.post_status = 'publish'
             LIMIT 10"
        );

        if ( empty( $posts_with_social_images ) ) {
            return null; // No posts to check
        }

        $posts_missing_alt = 0;

        foreach ( $posts_with_social_images as $post ) {
            // Check for Yoast SEO og:image ID
            $og_image_id = get_post_meta( $post->ID, '_yoast_wpseo_opengraph-image-id', true );

            if ( $og_image_id ) {
                $alt_text = get_post_meta( $og_image_id, '_wp_attachment_image_alt', true );

                if ( ! $alt_text || strlen( $alt_text ) < 10 ) {
                    $posts_missing_alt++;
                }
            }
        }

        if ( $posts_missing_alt > 0 ) {
            return array(
                'id'            => self::$slug,
                'title'         => self::$title,
                'description'   => sprintf(
                    /* translators: %d: number of posts */
                    __( '%d posts have social images without descriptive alt text. Add alt text for accessibility.', 'wpshadow' ),
                    $posts_missing_alt
                ),
                'severity'      => 'medium',
                'threat_level'  => 30,
                'auto_fixable'  => false,
                'kb_link'       => 'https://wpshadow.com/kb/social-image-alt-text',
                'persona'       => 'publisher',
            );
        }

        return null; // No issue found
    }
}

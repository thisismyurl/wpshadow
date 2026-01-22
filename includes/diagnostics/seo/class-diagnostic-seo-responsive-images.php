<?php
declare(strict_types=1);
/**
 * Responsive Images Diagnostic
 *
 * Philosophy: Improve performance and image SEO with srcset/sizes
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Responsive_Images extends Diagnostic_Base {
    /**
     * Sample an attachment to check for srcset generation capability.
     *
     * @return array|null
     */
    public static function check(): ?array {
        $id = (int) get_option('media_last_attachment_id', 0);
        if ($id > 0) {
            $srcset = wp_get_attachment_image_srcset($id, 'large');
            if (!empty($srcset)) {
                return null;
            }
        }
        return [
            'id' => 'seo-responsive-images',
            'title' => 'Responsive Image Srcset/Sizes',
            'description' => 'Ensure content images use srcset/sizes for responsive delivery. WordPress supports this natively for attachment images.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/responsive-images/',
            'training_link' => 'https://wpshadow.com/training/image-optimization/',
            'auto_fixable' => false,
            'threat_level' => 15,
        ];
    }
}

<?php
declare(strict_types=1);
/**
 * Menu Depth Excessive Diagnostic
 *
 * Philosophy: Menus 4+ levels deep hurt UX
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Menu_Depth_Excessive extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-menu-depth-excessive',
            'title' => 'Excessive Menu Depth',
            'description' => 'Navigation menus deeper than 3 levels hurt UX and crawlability. Simplify menu structure.',
            'severity' => 'low',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/menu-depth/',
            'training_link' => 'https://wpshadow.com/training/navigation-optimization/',
            'auto_fixable' => false,
            'threat_level' => 20,
        ];
    }
}

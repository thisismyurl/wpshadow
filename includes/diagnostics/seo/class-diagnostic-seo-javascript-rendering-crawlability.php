<?php
declare(strict_types=1);
/**
 * JavaScript Rendering Crawlability Diagnostic
 *
 * Philosophy: Ensure JS-rendered content is crawlable
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_JavaScript_Rendering_Crawlability extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-javascript-rendering-crawlability',
            'title' => 'JavaScript Rendering for SEO',
            'description' => 'Ensure critical content is server-rendered or verify Googlebot can render JavaScript. Use dynamic rendering if needed.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/javascript-seo/',
            'training_link' => 'https://wpshadow.com/training/client-side-rendering/',
            'auto_fixable' => false,
            'threat_level' => 70,
        ];
    }

}
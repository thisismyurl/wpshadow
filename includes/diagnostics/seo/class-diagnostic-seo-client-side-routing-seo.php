<?php
declare(strict_types=1);
/**
 * Client-Side Routing SEO Diagnostic
 *
 * Philosophy: SPAs need proper crawling strategy
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Client_Side_Routing_SEO extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'seo-client-side-routing-seo',
            'title' => 'Client-Side Routing (SPA) Strategy',
            'description' => 'Single-page apps need server-side rendering, dynamic rendering, or prerendering for SEO.',
            'severity' => 'high',
            'category' => 'seo',
            'kb_link' => 'https://wpshadow.com/kb/spa-seo/',
            'training_link' => 'https://wpshadow.com/training/single-page-apps/',
            'auto_fixable' => false,
            'threat_level' => 75,
        ];
    }

}
<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Image_Optimization_Score extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-image-optimization', 'title' => __('Image Optimization Score', 'wpshadow'), 'description' => __('Tracks % of images using modern formats (WebP), proper sizing, lazy loading. Unoptimized = bandwidth waste.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/image-optimization/', 'training_link' => 'https://wpshadow.com/training/image-formats/', 'auto_fixable' => false, 'threat_level' => 6]; } }

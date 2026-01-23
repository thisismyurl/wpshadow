#!/usr/bin/env python3
"""
Extract inline styles from PHP files and consolidate into external CSS files.
This script identifies all inline styles, creates CSS classes, and updates PHP files.
"""

import os
import re
import json
from pathlib import Path
from collections import defaultdict

class InlineStyleExtractor:
    def __init__(self, plugin_dir):
        self.plugin_dir = Path(plugin_dir)
        self.styles_by_category = defaultdict(list)
        self.php_files = []
        self.style_map = {}
        self.class_counter = 0
        
    def find_php_files(self):
        """Find all PHP files in the plugin directory."""
        for root, dirs, files in os.walk(self.plugin_dir):
            # Skip vendor and other non-essential directories
            dirs[:] = [d for d in dirs if d not in ['vendor', 'node_modules', 'tmp', '.git']]
            for file in files:
                if file.endswith('.php'):
                    self.php_files.append(Path(root) / file)
        return self.php_files
    
    def extract_inline_styles(self, file_path):
        """Extract all inline style attributes from a PHP file."""
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
        except Exception as e:
            print(f"Error reading {file_path}: {e}")
            return []
        
        # Pattern to match style="..." and style='...' attributes
        pattern = r'style\s*=\s*["\']([^"\']*)["\']'
        matches = re.finditer(pattern, content)
        
        styles = []
        for match in matches:
            style_content = match.group(1)
            if style_content.strip():
                styles.append({
                    'file': str(file_path.relative_to(self.plugin_dir)),
                    'style': style_content.strip(),
                    'full_match': match.group(0),
                    'position': match.start()
                })
        
        return styles
    
    def categorize_styles(self):
        """Categorize styles by file and type."""
        for php_file in self.find_php_files():
            styles = self.extract_inline_styles(php_file)
            for style in styles:
                category = self._get_category(php_file)
                self.styles_by_category[category].append(style)
    
    def _get_category(self, file_path):
        """Determine the category of a PHP file."""
        rel_path = str(file_path.relative_to(self.plugin_dir))
        
        if 'wpshadow.php' in rel_path:
            return 'main'
        elif 'error-handler' in rel_path:
            return 'error-handling'
        elif 'reports' in rel_path or 'report' in rel_path:
            return 'reports'
        elif 'settings' in rel_path:
            return 'settings'
        elif 'kpi' in rel_path or 'gamification' in rel_path:
            return 'features'
        elif 'views' in rel_path:
            return 'views'
        elif 'includes' in rel_path:
            return 'includes'
        else:
            return 'other'
    
    def generate_css_class(self, style_string):
        """Generate a CSS class name from style properties."""
        self.class_counter += 1
        
        # Extract key properties
        props = {}
        for prop in style_string.split(';'):
            if ':' in prop:
                key, val = prop.split(':', 1)
                key = key.strip().lower().replace('-', '_')
                val = val.strip()
                if key and val:
                    props[key] = val
        
        # Generate meaningful class name
        if 'color' in props or 'background' in props or 'border' in props:
            return f'wps-styled-{self.class_counter}'
        elif 'display' in props or 'flex' in props or 'grid' in props:
            return f'wps-layout-{self.class_counter}'
        elif 'margin' in props or 'padding' in props:
            return f'wps-spacing-{self.class_counter}'
        elif 'font' in props or 'text' in props:
            return f'wps-text-{self.class_counter}'
        else:
            return f'wps-util-{self.class_counter}'
    
    def generate_css_from_styles(self):
        """Generate CSS classes from extracted inline styles."""
        css_content = {}
        
        for category, styles in self.styles_by_category.items():
            css_lines = ['/* Auto-generated CSS from inline styles */\n']
            class_defs = {}
            
            for style in styles:
                style_str = style['style']
                # Skip CSS variables (they need inline styles)
                if 'var(' in style_str:
                    continue
                
                class_name = self.generate_css_class(style_str)
                if class_name not in class_defs:
                    # Convert inline style to CSS class
                    css_lines.append(f".{class_name} {{\n")
                    css_lines.append(f"  {style_str}\n")
                    css_lines.append("}\n\n")
                    class_defs[class_name] = style_str
                    self.style_map[style_str] = class_name
            
            css_content[category] = ''.join(css_lines)
        
        return css_content
    
    def get_summary(self):
        """Generate a summary of findings."""
        total_styles = sum(len(styles) for styles in self.styles_by_category.values())
        return {
            'total_files': len(self.php_files),
            'total_inline_styles': total_styles,
            'by_category': {cat: len(styles) for cat, styles in self.styles_by_category.items()},
            'files_with_styles': list(set(s['file'] for cat in self.styles_by_category.values() for s in cat))
        }

def main():
    plugin_dir = '/workspaces/wpshadow'
    extractor = InlineStyleExtractor(plugin_dir)
    
    print("🔍 Scanning for inline styles...")
    extractor.find_php_files()
    print(f"   Found {len(extractor.php_files)} PHP files\n")
    
    print("📋 Extracting inline styles...")
    extractor.categorize_styles()
    summary = extractor.get_summary()
    print(f"   Found {summary['total_inline_styles']} inline styles\n")
    
    print("📊 Summary by category:")
    for cat, count in summary['by_category'].items():
        if count > 0:
            print(f"   {cat}: {count} styles")
    
    print("\n📝 Top files with inline styles:")
    file_counts = defaultdict(int)
    for cat_styles in extractor.styles_by_category.values():
        for style in cat_styles:
            file_counts[style['file']] += 1
    
    for file_path, count in sorted(file_counts.items(), key=lambda x: x[1], reverse=True)[:15]:
        print(f"   {file_path}: {count} styles")
    
    # Generate CSS
    print("\n🎨 Generating CSS classes...")
    css_content = extractor.generate_css_from_styles()
    
    # Save report
    report_path = plugin_dir + '/INLINE_STYLES_AUDIT.json'
    with open(report_path, 'w') as f:
        json.dump({
            'summary': summary,
            'style_map': extractor.style_map,
            'files_analyzed': len(extractor.php_files)
        }, f, indent=2)
    print(f"   ✓ Saved audit to {report_path}")
    
    return summary, css_content

if __name__ == '__main__':
    main()

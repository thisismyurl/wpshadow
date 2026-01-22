#!/usr/bin/env python3
"""
Create Knowledge Base articles from WPShadow tooltip data
Generates WP-CLI commands to bulk-create KB articles
"""

import json
import re
import subprocess
from pathlib import Path

# Load all tooltip files
data_dir = Path('/workspaces/wpshadow/includes/data')
tooltip_files = list(data_dir.glob('tooltips*.json'))

# Extract all unique KB URLs and associated tooltip data
kb_articles = {}

for tooltip_file in tooltip_files:
    with open(tooltip_file, 'r') as f:
        tooltips = json.load(f)
        
    for tooltip in tooltips:
        if 'kb_url' in tooltip and tooltip['kb_url']:
            kb_url = tooltip['kb_url']
            slug = kb_url.replace('https://wpshadow.com/kb/', '')
            
            if slug not in kb_articles:
                kb_articles[slug] = {
                    'title': tooltip.get('title', '').replace('WordPress', 'WP'),
                    'message': tooltip.get('message', ''),
                    'category': tooltip.get('category', 'general'),
                    'level': tooltip.get('level', 'beginner'),
                    'tooltips': []
                }
            
            kb_articles[slug]['tooltips'].append({
                'id': tooltip.get('id', ''),
                'title': tooltip.get('title', ''),
                'selector': tooltip.get('selector', '')
            })

print(f"Found {len(kb_articles)} unique KB articles to create")
print("\nGenerating WP-CLI commands...\n")

# Generate WP-CLI commands
commands = []

for slug, article in sorted(kb_articles.items()):
    # Clean up title
    title = article['title'].strip()
    if not title:
        title = slug.replace('-', ' ').title()
    
    # Build content from message + related tooltips
    content = f"<p>{article['message']}</p>"
    
    if len(article['tooltips']) > 1:
        content += "\n\n<h2>Related Features</h2>\n<ul>"
        for tt in article['tooltips'][:5]:  # Limit to first 5
            if tt['title']:
                content += f"\n<li>{tt['title']}</li>"
        content += "\n</ul>"
    
    # Escape content for shell
    content_escaped = content.replace('"', '\\"').replace('$', '\\$')
    title_escaped = title.replace('"', '\\"').replace('$', '\\$')
    
    # WP-CLI command
    cmd = f'''docker-compose exec -T wordpress-test wp --allow-root post create \\
  --post_type=wpshadow_kb \\
  --post_title="{title_escaped}" \\
  --post_content="{content_escaped}" \\
  --post_name="{slug}" \\
  --post_status=publish \\
  --meta_input='{{"_wpshadow_difficulty":"{article['level']}","_wpshadow_read_time":"3"}}'
'''
    
    commands.append(cmd)

# Write to shell script
script_path = '/workspaces/wpshadow/import-kb-articles.sh'
with open(script_path, 'w') as f:
    f.write("#!/bin/bash\n")
    f.write("# Auto-generated KB article import script\n")
    f.write(f"# Creates {len(commands)} knowledge base articles\n\n")
    f.write("cd /workspaces/wpshadow\n\n")
    f.write("echo 'Creating KB articles...'\n")
    f.write("CREATED=0\n\n")
    
    for i, cmd in enumerate(commands, 1):
        f.write(f"# Article {i}/{len(commands)}\n")
        f.write(cmd)
        f.write(" && CREATED=$((CREATED+1))\n\n")
    
    f.write('echo ""\n')
    f.write('echo "Created $CREATED KB articles!"\n')

print(f"✅ Generated {len(commands)} WP-CLI commands")
print(f"✅ Saved to: {script_path}")
print(f"\nTo import KB articles, run:")
print(f"  chmod +x {script_path}")
print(f"  {script_path}")

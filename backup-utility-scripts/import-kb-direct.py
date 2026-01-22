#!/usr/bin/env python3
"""
Create Knowledge Base articles directly in database (faster than WP-CLI)
"""

import json
import MySQLdb
import html
from pathlib import Path
from datetime import datetime

# Database connection
conn = MySQLdb.connect(
    host='localhost',
    port=3306,
    user='wordpress',
    passwd='wordpress',
    db='wpshadow_test'
)
cursor = conn.cursor()

# Load all tooltip files
data_dir = Path('/workspaces/wpshadow/includes/data')
tooltip_files = list(data_dir.glob('tooltips*.json'))

# Extract all unique KB URLs
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
            
            kb_articles[slug]['tooltips'].append(tooltip)

print(f"Found {len(kb_articles)} unique KB articles")
print("Creating articles in database...\n")

now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
created = 0

for slug, article in sorted(kb_articles.items()):
    # Clean up title
    title = article['title'].strip()
    if not title:
        title = slug.replace('-', ' ').title()
    
    # Build content
    content = f"<p>{html.escape(article['message'])}</p>"
    
    if len(article['tooltips']) > 1:
        content += "\n\n<h2>Related Features</h2>\n<ul>"
        for tt in article['tooltips'][:8]:
            if tt.get('title'):
                content += f"\n<li><strong>{html.escape(tt['title'])}</strong></li>"
        content += "\n</ul>"
    
    content += f"\n\n<p><em>Difficulty: {article['level'].title()}</em></p>"
    
    # Insert post
    cursor.execute("""
        INSERT INTO wp_posts 
        (post_author, post_date, post_date_gmt, post_content, post_title, 
         post_excerpt, post_status, post_name, post_modified, post_modified_gmt,
         post_type, comment_status)
        VALUES (1, %s, %s, %s, %s, %s, 'publish', %s, %s, %s, 'wpshadow_kb', 'open')
    """, (now, now, content, title, article['message'][:150], slug, now, now))
    
    post_id = cursor.lastrowid
    
    # Add meta
    cursor.execute("""
        INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
        VALUES (%s, '_wpshadow_difficulty', %s),
               (%s, '_wpshadow_read_time', '3')
    """, (post_id, article['level'], post_id))
    
    created += 1
    
    if created % 25 == 0:
        print(f"  Created {created} articles...")

conn.commit()
cursor.close()
conn.close()

print(f"\n✅ Created {created} KB articles!")
print(f"View at: https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/wp-admin/edit.php?post_type=wpshadow_kb")

#!/bin/bash

echo "Creating labels with leading zeros..."

# Delete ALL current labels
gh label list --limit 200 | awk 'NR>1 {print $1}' | while read label; do
  gh label delete "$label" --yes 2>/dev/null && echo "  ✓ Deleted: $label"
done

echo ""
echo "Creating new labels with proper numbering..."

# Issue Types
gh label create "Bug" --color "d73a4a" --description "Something is broken or not working as expected" 2>/dev/null
gh label create "Feature" --color "a2eeef" --description "New feature request or enhancement" 2>/dev/null
gh label create "Documentation" --color "0075ca" --description "Documentation improvement or addition" 2>/dev/null
gh label create "Question" --color "d876e3" --description "Question or need for clarification" 2>/dev/null
gh label create "Discussion" --color "1d76db" --description "Discussion topic for community engagement" 2>/dev/null

# Principles (11 - Purple) with LEADING ZEROS
gh label create "Principle #01 (Helpful Neighbor)" --color "7057ff" --description "Help neighbors solve their most important problems" 2>/dev/null
gh label create "Principle #02 (Free as Possible)" --color "7057ff" --description "Affordable access for nonprofits and small organizations" 2>/dev/null
gh label create "Principle #03 (Advice Not Sales)" --color "7057ff" --description "Provide advice over sales pitches" 2>/dev/null
gh label create "Principle #04 (Accessibility First)" --color "7057ff" --description "Accessible to all users regardless of ability" 2>/dev/null
gh label create "Principle #05 (Learning Inclusive)" --color "7057ff" --description "Support learners at all skill levels" 2>/dev/null
gh label create "Principle #06 (Culturally Respectful)" --color "7057ff" --description "Inclusive of all cultures and perspectives" 2>/dev/null
gh label create "Principle #07 (Ridiculously Good)" --color "7057ff" --description "Exceptional quality and attention to detail" 2>/dev/null
gh label create "Principle #08 (Inspire Confidence)" --color "7057ff" --description "Build trust and credibility" 2>/dev/null
gh label create "Principle #09 (Show Value - KPIs)" --color "7057ff" --description "Demonstrable value through key performance indicators" 2>/dev/null
gh label create "Principle #10 (Privacy First)" --color "7057ff" --description "Privacy and data protection paramount" 2>/dev/null
gh label create "Principle #11 (Talk-Worthy)" --color "7057ff" --description "Create experiences people want to discuss" 2>/dev/null

# CANON Pillars (3 - Green)
gh label create "CANON: Accessibility First" --color "28a745" --description "Accessibility is fundamental to our mission" 2>/dev/null
gh label create "CANON: Learning Inclusive" --color "28a745" --description "Learning support for all skill levels" 2>/dev/null
gh label create "CANON: Culturally Respectful" --color "28a745" --description "Respect and inclusion for all cultures" 2>/dev/null

# Feature Categories (5 - Blue)
gh label create "Feature: Diagnostics" --color "0052cc" --description "Diagnostic functionality and enhancements" 2>/dev/null
gh label create "Feature: Treatments" --color "0052cc" --description "Treatment/solution features" 2>/dev/null
gh label create "Feature: Workflow" --color "0052cc" --description "Workflow optimization and improvements" 2>/dev/null
gh label create "Feature: KPI Tracking" --color "0052cc" --description "Key Performance Indicator tracking features" 2>/dev/null
gh label create "Feature: Dashboard" --color "0052cc" --description "Dashboard and reporting features" 2>/dev/null

# Technical Areas (6 - Varied)
gh label create "Area (Security)" --color "d73a4a" --description "Security-related issues and improvements" 2>/dev/null
gh label create "Area (Performance)" --color "ff8800" --description "Performance optimization and improvements" 2>/dev/null
gh label create "Area (Accessibility)" --color "28a745" --description "Accessibility and inclusive design" 2>/dev/null
gh label create "Area (Multisite)" --color "6f42c1" --description "WordPress multisite functionality" 2>/dev/null
gh label create "Area (API)" --color "1f6feb" --description "API and integration features" 2>/dev/null
gh label create "Area (Database)" --color "0298c3" --description "Database-related issues" 2>/dev/null

# Community Feedback (4 - Green)
gh label create "Feedback (User Request)" --color "28a745" --description "Direct user feature request" 2>/dev/null
gh label create "Feedback (Suggestion)" --color "28a745" --description "Community suggestion or idea" 2>/dev/null
gh label create "Feedback (Discussion)" --color "28a745" --description "Feedback and discussion topic" 2>/dev/null
gh label create "Feedback (Testimonial)" --color "28a745" --description "User testimonial or success story" 2>/dev/null

# Priority (4)
gh label create "Priority (Critical)" --color "d73a4a" --description "Critical priority - blocking functionality" 2>/dev/null
gh label create "Priority (High)" --color "ff6b6b" --description "High priority - significant impact" 2>/dev/null
gh label create "Priority (Medium)" --color "ffd700" --description "Medium priority - standard issue" 2>/dev/null
gh label create "Priority (Low)" --color "c2c2c2" --description "Low priority - minor improvements" 2>/dev/null

# Status (4)
gh label create "Status (Needs Triage)" --color "ffd700" --description "Needs initial review and categorization" 2>/dev/null
gh label create "Status (In Progress)" --color "0052cc" --description "Currently being worked on" 2>/dev/null
gh label create "Status (Blocked)" --color "d73a4a" --description "Blocked and cannot proceed" 2>/dev/null
gh label create "Status (Ready for Review)" --color "28a745" --description "Ready for review or testing" 2>/dev/null

# Content (4 - Blue)
gh label create "Content (KB Article)" --color "0052cc" --description "Knowledge base article" 2>/dev/null
gh label create "Content (Training Video)" --color "0052cc" --description "Training video content" 2>/dev/null
gh label create "Content (Blog Post)" --color "0052cc" --description "Blog post or article" 2>/dev/null
gh label create "Content (Social Media)" --color "0052cc" --description "Social media content" 2>/dev/null

# Roadmap (2) - with leading zeros
gh label create "Roadmap #01 (Phase 3 - Current)" --color "28a745" --description "Current phase 3 roadmap item" 2>/dev/null
gh label create "Roadmap #02 (Future)" --color "c2c2c2" --description "Future planning roadmap item" 2>/dev/null

# Roles (3 - Purple)
gh label create "Role (Good First Issue)" --color "7057ff" --description "Good issue for new contributors" 2>/dev/null
gh label create "Role (Help Wanted)" --color "7057ff" --description "Help wanted from the community" 2>/dev/null
gh label create "Role (Expert Needed)" --color "7057ff" --description "Expert knowledge required" 2>/dev/null

echo ""
echo "✅ All labels recreated with proper numbering!"
gh label list --limit 200 | wc -l

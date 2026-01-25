#!/bin/bash

echo "Creating final optimized label system..."
echo ""

# Priority labels with leading zeros (01-04)
gh label create "Priority 01 (Low)" --color "c2c2c2" --description "Nice to have improvements that align with Principle #7 (Ridiculously Good)" 2>/dev/null
gh label create "Priority 02 (Medium)" --color "ffd700" --description "Important improvements supporting our core principles" 2>/dev/null
gh label create "Priority 03 (High)" --color "ff6b6b" --description "Significant impact addressing a core principle" 2>/dev/null
gh label create "Priority 04 (Critical)" --color "d73a4a" --description "Urgent: Blocks core principle implementation or site functionality" 2>/dev/null

# Update existing principle labels with descriptions matching core principles
gh label delete "Principle #10 (Privacy First)" --yes 2>/dev/null || true
gh label delete "Principle #11 (Talk-Worthy)" --yes 2>/dev/null || true

gh label create "Principle #10 (Privacy First)" --color "7057ff" --description "All actions respect user privacy and data consent - Principle #10" 2>/dev/null || true
gh label create "Principle #11 (Talk-Worthy)" --color "7057ff" --description "Create experiences people want to discuss and recommend - Principle #11" 2>/dev/null || true

# Update all existing principle labels with aligned descriptions
gh label delete "Principle #01 (Helpful Neighbor)" --yes 2>/dev/null || true
gh label delete "Principle #02 (Free as Possible)" --yes 2>/dev/null || true
gh label delete "Principle #03 (Advice Not Sales)" --yes 2>/dev/null || true
gh label delete "Principle #04 (Accessibility First)" --yes 2>/dev/null || true
gh label delete "Principle #05 (Learning Inclusive)" --yes 2>/dev/null || true
gh label delete "Principle #06 (Culturally Respectful)" --yes 2>/dev/null || true
gh label delete "Principle #07 (Ridiculously Good)" --yes 2>/dev/null || true
gh label delete "Principle #08 (Inspire Confidence)" --yes 2>/dev/null || true
gh label delete "Principle #09 (Show Value - KPIs)" --yes 2>/dev/null || true

gh label create "Principle #01 (Helpful Neighbor)" --color "7057ff" --description "Help neighbors solve their most important problems - Principle #1" 2>/dev/null
gh label create "Principle #02 (Free as Possible)" --color "7057ff" --description "Affordable access for nonprofits and small organizations - Principle #2" 2>/dev/null
gh label create "Principle #03 (Advice Not Sales)" --color "7057ff" --description "Provide educational advice, never pushy sales - Principle #3" 2>/dev/null
gh label create "Principle #04 (Accessibility First)" --color "7057ff" --description "Accessible to all users regardless of ability - Principle #4" 2>/dev/null
gh label create "Principle #05 (Learning Inclusive)" --color "7057ff" --description "Support learners at all skill levels with clear documentation - Principle #5" 2>/dev/null
gh label create "Principle #06 (Culturally Respectful)" --color "7057ff" --description "Honor diverse contexts and cultural perspectives - Principle #6" 2>/dev/null
gh label create "Principle #07 (Ridiculously Good)" --color "7057ff" --description "Exceptional quality better than premium alternatives - Principle #7" 2>/dev/null
gh label create "Principle #08 (Inspire Confidence)" --color "7057ff" --description "User experience that builds trust and confidence - Principle #8" 2>/dev/null
gh label create "Principle #09 (Show Value - KPIs)" --color "7057ff" --description "Demonstrable value through key performance indicators - Principle #9" 2>/dev/null

echo ""
echo "✅ Final optimized label system created"

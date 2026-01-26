<?php
/**
 * Podcast Script Generator
 * 
 * Generates conversational, educational podcast scripts with four distinct characters:
 * - The Host: Professional, authoritative NPR/CBC style host
 * - The Help: Younger, idealistic audience proxy
 * - The Sidekick: Equal knowledge, reinforces Host's perspective
 * - The Expert: Subject matter deep-diver
 * 
 * @author Your Name
 * @version 1.0
 */

declare(strict_types=1);

class PodcastCharacter
{
    public string $name;
    public string $role;
    public string $age;
    public string $personality;
    public array $speech_patterns = [];
    
    public function __construct(
        string $name,
        string $role,
        string $age,
        string $personality,
        array $speech_patterns = []
    ) {
        $this->name = $name;
        $this->role = $role;
        $this->age = $age;
        $this->personality = $personality;
        $this->speech_patterns = $speech_patterns;
    }
}

class PodcastScriptGenerator
{
    private PodcastCharacter $host;
    private PodcastCharacter $help;
    private PodcastCharacter $sidekick;
    private PodcastCharacter $expert;
    private string $topic = '';
    private string $episode_number = '';
    
    public function __construct()
    {
        $this->initializeCharacters();
    }
    
    /**
     * Initialize the four podcast characters with their traits
     */
    private function initializeCharacters(): void
    {
        $this->host = new PodcastCharacter(
            name: 'Margaret Chen',
            role: 'The Host',
            age: '52',
            personality: 'Professional, authoritative, well-spoken, instills confidence, interesting storyteller',
            speech_patterns: [
                'Well, let\'s dig into this.',
                'That\'s a great point.',
                'Here\'s what we know.',
                'I think it\'s important to understand that...',
                'Let me turn to our expert here.',
                'So here\'s the thing...',
            ]
        );
        
        $this->help = new PodcastCharacter(
            name: 'Jordan Mills',
            role: 'The Help',
            age: '28',
            personality: 'Idealistic, curious, energetic, audience proxy, asks the questions listeners want answered',
            speech_patterns: [
                'But wait, I want to understand...',
                'So you\'re saying...',
                'That\'s fascinating! But what about...',
                'I think a lot of people wonder about this.',
                'That makes sense, but how does that actually work?',
                'I love that perspective.',
            ]
        );
        
        $this->sidekick = new PodcastCharacter(
            name: 'David Rodriguez',
            role: 'The Sidekick',
            age: '30',
            personality: 'Equal subject knowledge, reinforces Host\'s perspective, adds complementary viewpoints, collaborative',
            speech_patterns: [
                'Absolutely, and I\'d add to that...',
                'Yeah, Margaret makes a good point there.',
                'What I find interesting is...',
                'That aligns with what we\'re seeing in...',
                'I think that\'s crucial because...',
                'You know, there\'s another angle here too.',
            ]
        );
        
        $this->expert = new PodcastCharacter(
            name: 'Dr. James Patterson',
            role: 'The Expert',
            age: '50',
            personality: 'Trusted advisor, deep subject matter knowledge, approachable despite expertise, mentor-like',
            speech_patterns: [
                'The research shows us...',
                'From my experience in this field...',
                'Let me break this down a bit.',
                'That\'s exactly right, and here\'s why...',
                'The data supports that, and additionally...',
                'I\'ve seen this happen countless times, and the reason is...',
            ]
        );
    }
    
    /**
     * Set the topic for the episode
     */
    public function setTopic(string $topic): self
    {
        $this->topic = $topic;
        return $this;
    }
    
    /**
     * Set the episode number
     */
    public function setEpisodeNumber(string $episode_number): self
    {
        $this->episode_number = $episode_number;
        return $this;
    }
    
    /**
     * Generate the opening segment
     */
    public function generateOpening(): string
    {
        $script = "PODCAST SCRIPT - EPISODE {$this->episode_number}\n";
        $script .= "TOPIC: {$this->topic}\n";
        $script .= str_repeat("=", 80) . "\n\n";
        
        $script .= "[OPENING THEME MUSIC FADES]\n\n";
        
        $script .= "MARGARET (The Host):\n";
        $script .= "Welcome back to In Depth. I'm Margaret Chen. Today we're diving into something\n";
        $script .= "that affects all of us: " . strtolower($this->topic) . ". We'll be exploring what it is,\n";
        $script .= "why it matters, and what you can do about it.\n\n";
        
        $script .= "To help me unpack this, I've got David Rodriguez, our producer and research\n";
        $script .= "partner. Jordan Mills is here asking the questions you'd want answered. And\n";
        $script .= "joining us remotely is Dr. James Patterson, who's spent the last twenty-five years\n";
        $script .= "studying this field.\n\n";
        
        $script .= "Let's start simple: What exactly is " . strtolower($this->topic) . "?\n\n";
        
        return $script;
    }
    
    /**
     * Generate a question segment from The Help
     */
    public function generateHelpSegment(string $question): string
    {
        $script = "JORDAN (The Help):\n";
        $script .= "{$question}\n\n";
        return $script;
    }
    
    /**
     * Generate a response from The Host
     */
    public function generateHostResponse(string $response): string
    {
        $script = "MARGARET (The Host):\n";
        $script .= "{$response}\n\n";
        return $script;
    }
    
    /**
     * Generate a reinforcement from The Sidekick
     */
    public function generateSidekickSegment(string $comment): string
    {
        $script = "DAVID (The Sidekick):\n";
        $script .= "{$comment}\n\n";
        return $script;
    }
    
    /**
     * Generate expert deep-dive
     */
    public function generateExpertSegment(string $expertise): string
    {
        $script = "DR. PATTERSON (The Expert):\n";
        $script .= "{$expertise}\n\n";
        return $script;
    }
    
    /**
     * Generate a discussion exchange (simplified template)
     */
    public function generateDiscussionExchange(
        string $host_prompt,
        string $help_question,
        string $host_response,
        string $sidekick_reinforcement,
        string $expert_insight
    ): string
    {
        $script = "";
        
        // Host introduces the topic
        $script .= "MARGARET (The Host):\n";
        $script .= "{$host_prompt}\n\n";
        
        // Help asks follow-up
        $script .= "JORDAN (The Help):\n";
        $script .= "{$help_question}\n\n";
        
        // Host responds
        $script .= "MARGARET (The Host):\n";
        $script .= "{$host_response}\n\n";
        
        // Sidekick reinforces
        $script .= "DAVID (The Sidekick):\n";
        $script .= "{$sidekick_reinforcement}\n\n";
        
        // Expert goes deeper
        $script .= "DR. PATTERSON (The Expert):\n";
        $script .= "{$expert_insight}\n\n";
        
        $script .= str_repeat("-", 80) . "\n\n";
        
        return $script;
    }
    
    /**
     * Generate the closing segment
     */
    public function generateClosing(): string
    {
        $script = "[TRANSITION MUSIC]\n\n";
        
        $script .= "MARGARET (The Host):\n";
        $script .= "Before we go, here's what I want you to remember about " . strtolower($this->topic) . ":\n";
        $script .= "It's complex, but not complicated. You don't need to be an expert to\n";
        $script .= "understand it, and you don't need permission to act on what you've learned.\n\n";
        
        $script .= "JORDAN (The Help):\n";
        $script .= "And there are resources out there to help you go deeper.\n\n";
        
        $script .= "DAVID (The Sidekick):\n";
        $script .= "You'll find links to everything we talked about on our website.\n\n";
        
        $script .= "DR. PATTERSON (The Expert):\n";
        $script .= "Thanks for having me. It's been a pleasure talking with you.\n\n";
        
        $script .= "MARGARET (The Host):\n";
        $script .= "That's been In Depth. I'm Margaret Chen. Thanks for listening.\n\n";
        
        $script .= "[CLOSING THEME MUSIC AND FADE]\n\n";
        
        $script .= str_repeat("=", 80) . "\n";
        $script .= "END OF EPISODE {$this->episode_number}\n";
        
        return $script;
    }
    
    /**
     * Get character information
     */
    public function getCharacter(string $character_type): ?PodcastCharacter
    {
        return match(strtolower($character_type)) {
            'host' => $this->host,
            'help' => $this->help,
            'sidekick' => $this->sidekick,
            'expert' => $this->expert,
            default => null,
        };
    }
    
    /**
     * Generate a complete script (basic template)
     */
    public function generateCompleteScript(): string
    {
        if (empty($this->topic) || empty($this->episode_number)) {
            throw new Exception('Topic and episode number must be set before generating script.');
        }
        
        $script = $this->generateOpening();
        
        // Example exchange pattern - can be expanded
        $script .= $this->generateDiscussionExchange(
            host_prompt: "Let's break this down into three key components.",
            help_question: "Can you explain what you mean by that?",
            host_response: "Absolutely. Think of it like this...",
            sidekick_reinforcement: "And that's important because it affects...",
            expert_insight: "The research in my field confirms that..."
        );
        
        $script .= $this->generateClosing();
        
        return $script;
    }
    
    /**
     * Output script to console
     */
    public function displayScript(string $script): void
    {
        echo $script;
    }
    
    /**
     * Save script to file
     */
    public function saveScript(string $script, string $filename): bool
    {
        return (bool) file_put_contents($filename, $script);
    }
}

// Example usage
if (php_sapi_name() === 'cli') {
    $generator = new PodcastScriptGenerator();
    $generator
        ->setEpisodeNumber('001')
        ->setTopic('Understanding Sandpaper Grits: P400 vs 400');
    
    try {
        // Build a custom script about sandpaper
        $script = "PODCAST SCRIPT - EPISODE 001\n";
        $script .= "TOPIC: Understanding Sandpaper Grits: P400 vs 400\n";
        $script .= str_repeat("=", 80) . "\n\n";
        
        $script .= "[OPENING THEME MUSIC FADES]\n\n";
        
        $script .= "MARGARET (The Host):\n";
        $script .= "Welcome back to In Depth. I'm Margaret Chen. Today we're tackling a question\n";
        $script .= "that confuses a lot of DIYers and woodworkers: what's the difference between\n";
        $script .= "sandpaper grits, and why do we have these different numbering systems?\n";
        $script .= "Specifically, we're looking at P400 versus 400. Sounds the same, right? It's not.\n\n";
        
        $script .= "I'm joined by David Rodriguez, our research partner, Jordan Mills who's asking\n";
        $script .= "the questions you want answered, and Dr. James Patterson, who spent years in\n";
        $script .= "materials science before becoming a finishing expert.\n\n";
        
        // Discussion Exchange 1: What are sandpaper grits?
        $script .= "MARGARET (The Host):\n";
        $script .= "Let's start with the basics. When we talk about sandpaper grit, what are we\n";
        $script .= "actually talking about?\n\n";
        
        $script .= "DAVID (The Sidekick):\n";
        $script .= "Yeah, it's the size of the particles on the surface. Finer grit numbers mean\n";
        $script .= "smoother particles, coarser numbers mean rougher ones.\n\n";
        
        $script .= "JORDAN (The Help):\n";
        $script .= "Wait, so a higher number is actually smoother? That seems backwards to me.\n\n";
        
        $script .= "DR. PATTERSON (The Expert):\n";
        $script .= "It does seem backwards, and that's because of how the industry evolved. The\n";
        $script .= "numbering system refers to how many particles fit through a mesh screen. So a\n";
        $script .= "P400 grit has 400 particles per linear inch. The higher the number, the more\n";
        $script .= "particles you can fit, which means each individual particle is smaller. Smaller\n";
        $script .= "particles create a finer, smoother surface.\n\n";
        
        $script .= str_repeat("-", 80) . "\n\n";
        
        // Discussion Exchange 2: The two standards
        $script .= "MARGARET (The Host):\n";
        $script .= "Now here's where it gets interesting. There are actually two different standards\n";
        $script .= "in use around the world. Can you explain those, Dr. Patterson?\n\n";
        
        $script .= "DR. PATTERSON (The Expert):\n";
        $script .= "Absolutely. The P-grade system comes from the FEPA standard—that's the\n";
        $script .= "Federation of European Producers of Abrasives. The regular grade system, like 400,\n";
        $script .= "comes from the CAMI standard, which is the Coated Abrasive Manufacturers Institute,\n";
        $script .= "primarily used in North America. They both measure particle size, but they use\n";
        $script .= "slightly different methods and sieves.\n\n";
        
        $script .= "JORDAN (The Help):\n";
        $script .= "So are they the same thing, just with different names? Can I use them\n";
        $script .= "interchangeably?\n\n";
        
        $script .= "DAVID (The Sidekick):\n";
        $script .= "That's actually a great question because the answer is mostly yes, but not\n";
        $script .= "exactly. They're pretty close in practice.\n\n";
        
        $script .= "DR. PATTERSON (The Expert):\n";
        $script .= "Right. A P400 from FEPA is nominally equivalent to 400 grit from CAMI, but\n";
        $script .= "there are small differences. The P-grade system is slightly more precise and\n";
        $script .= "has a narrower range of particle sizes. The CAMI system allows for a broader\n";
        $script .= "distribution. In practical terms, for a home woodworker or DIYer, you won't notice\n";
        $script .= "much difference. But if you're doing fine finishing work or automotive paint prep,\n";
        $script .= "that precision matters.\n\n";
        
        $script .= str_repeat("-", 80) . "\n\n";
        
        // Discussion Exchange 3: When does it matter?
        $script .= "MARGARET (The Host):\n";
        $script .= "So when would someone actually care about using P400 versus 400?\n\n";
        
        $script .= "JORDAN (The Help):\n";
        $script .= "Yeah, when would I notice a difference?\n\n";
        
        $script .= "DR. PATTERSON (The Expert):\n";
        $script .= "If you're doing standard woodworking, furniture finishing, or general DIY work,\n";
        $script .= "you won't notice a meaningful difference. Use whichever is available and\n";
        $script .= "affordable. But there are three areas where it matters: First, automotive\n";
        $script .= "refinishing and paint preparation, where precision is critical. Second,\n";
        $script .= "high-end furniture or instrument making. And third, when you're moving through\n";
        $script .= "multiple grit stages in sequence. Using the same system throughout ensures better\n";
        $script .= "consistency.\n\n";
        
        $script .= "DAVID (The Sidekick):\n";
        $script .= "That makes sense. So consistency within a project is important.\n\n";
        
        $script .= "MARGARET (The Host):\n";
        $script .= "Let's break down what that progression looks like, because a lot of people don't\n";
        $script .= "know you're supposed to go through multiple grits.\n\n";
        
        $script .= "DR. PATTERSON (The Expert):\n";
        $script .= "You never go directly to your final grit. If you start with 80 grit, you'd move to\n";
        $script .= "120, then 150, then 220, then 320, and finally 400 or 500. Each step removes the\n";
        $script .= "scratches from the previous grit. If you skip grits, you'll have visible scratches\n";
        $script .= "that are hard to remove. The progression is what creates that smooth, professional\n";
        $script .= "finish.\n\n";
        
        $script .= str_repeat("-", 80) . "\n\n";
        
        // Closing
        $script .= "[TRANSITION MUSIC]\n\n";
        
        $script .= "MARGARET (The Host):\n";
        $script .= "So here's what I want you to remember: P400 and 400 are for all practical\n";
        $script .= "purposes the same thing in your workshop. What matters more is understanding that\n";
        $script .= "the higher the number, the finer the grit. And when you're finishing a project,\n";
        $script .= "don't skip grits—walk your way up gradually.\n\n";
        
        $script .= "JORDAN (The Help):\n";
        $script .= "This actually makes a lot of sense now. I'm way less confused than I was when\n";
        $script .= "we started.\n\n";
        
        $script .= "DAVID (The Sidekick):\n";
        $script .= "And that's the whole goal. You don't need to be an expert to get professional\n";
        $script .= "results.\n\n";
        
        $script .= "DR. PATTERSON (The Expert):\n";
        $script .= "Exactly. Good technique beats fancy equipment every time.\n\n";
        
        $script .= "MARGARET (The Host):\n";
        $script .= "That's been In Depth. I'm Margaret Chen. Thanks for listening.\n\n";
        
        $script .= "[CLOSING THEME MUSIC AND FADE]\n\n";
        
        $script .= str_repeat("=", 80) . "\n";
        $script .= "END OF EPISODE 001\n";
        
        $generator->displayScript($script);
        
        // Save to file
        $filename = 'episode-001-sandpaper-grits.txt';
        if ($generator->saveScript($script, $filename)) {
            echo "\n✓ Script saved to {$filename}\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

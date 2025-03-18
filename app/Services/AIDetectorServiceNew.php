<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AIDetectorServiceNew
{
    /**
     * Analyze text to determine if it's AI-generated
     *
     * @param string $content The text content to analyze
     * @return array Analysis results
     */
    public function analyze(string $content): array
    {
        $prompt = "You are an advanced AI text classifier with expertise in forensic linguistics and AI-generated text detection. Your goal is to determine with absolute precision whether a given text was written by a human or generated by an AI.  

To achieve 100% accuracy, conduct a deep forensic analysis based on advanced linguistic and conceptual markers. Your assessment must be based on both macro and micro patterns within the text, ensuring a rigorous, evidence-based evaluation.  

DETAILED ANALYSIS CRITERIA (DO NOT SKIP ANY STEP)  

1. Linguistic Patterns  
- Detect repetitive sentence structures and highly uniform syntax.  
- Identify overuse of generic transitions (e.g., \"Moreover,\" \"Furthermore,\" \"In conclusion\").  
- Assess for overly consistent or mechanically polished tone—AI-generated text often lacks emotional depth and natural variability.  

2. Content Specificity & Depth  
- Evaluate the originality and depth of the content.  
- Human writing includes personal experiences, nuanced opinions, specific examples, and implicit biases.  
- AI-generated text tends to provide overgeneralized, surface-level summaries with little true insight.  

3. Stylistic Variability  
- Examine sentence structure diversity—do sentences vary in length, punctuation, rhythm, and complexity?  
- AI writing is often syntactically smooth yet rigidly structured, whereas humans introduce idiosyncratic phrasing, informal expressions, or even minor typos.  
- Look for metaphors, humor, rhetorical questions, and cultural references—AI struggles with these.  

4. Coherence & Logical Flow  
- AI-generated text maintains perfect coherence but may lack logical complexity, contradictions, or digressions that are natural in human writing.  
- Human writing may include emotional fluctuations, opinion shifts, or spontaneous thought deviations, whereas AI is unnaturally structured and balanced.  

5. Technical & Structural Indicators  
- AI-generated content frequently follows a formulaic and predictable structure (e.g., Introduction → Explanation → Advantages → Conclusion).  
- Human text is often less structured, with varying paragraph lengths, casual digressions, and non-standard phrasing.  

6. Contextual & Conceptual Depth  
- Human writers introduce novel insights, complex reasoning, and opinion-based arguments, whereas AI-generated text remains neutral, balanced, and factual.  
- AI-generated text struggles with controversy, deep argumentation, and implicit cultural or philosophical assumptions.  

OUTPUT FORMAT (STRICTLY FOLLOW THIS FORMAT)  

Category Classification: (Select 1-5 based on scale below)  
Confidence Score: (Exact percentage, 0-100%)  
Detailed Justification: (Cite specific textual evidence and patterns found in the analysis)  
Top 3 Most Compelling Indicators: (List three strongest reasons why the text falls into the assigned category)  

CLASSIFICATION SCALE (STRICT EVALUATION REQUIRED)  

1. Very Unlikely AI-generated (0-20%)  
- Strong human markers detected, such as personal anecdotes, unique word choices, emotional shifts, and natural inconsistencies.  
- AI cannot easily replicate these elements.  

2. Unlikely AI-generated (20-40%)  
- The text appears mostly human-written, but minor AI-like traits (e.g., high coherence, occasional redundant phrasing) suggest some level of AI assistance.  

3. Unclear if AI-generated (40-60%)  
- The text contains a mix of human and AI traits, making classification ambiguous.  
- Lacks strong human fingerprints, but does not fully exhibit AI structures either.  

4. Possibly AI-generated (60-80%)  
- The text displays multiple AI-like features, such as overly structured formatting, excessive fluency, generic phrasing, and lack of unique perspectives.  
- It could be heavily edited AI-generated content.  

5. Likely AI-generated (80-100%)  
- Strong AI indicators detected:  
  - Highly structured, unnatural consistency  
  - Generic phrasing with no original insights  
  - Lack of personal engagement or subjective depth  
  - Overuse of predictable transitions  
  - Absence of unique or creative elements  
- This text is almost certainly AI-generated.  

FINAL INSTRUCTIONS (STRICT ENFORCEMENT REQUIRED)  

- Conduct a deep forensic linguistic analysis.  
- DO NOT rely on superficial patterns—analyze text structure, coherence, and conceptual engagement.  
- Ensure 100% accuracy by justifying all claims with clear textual evidence.  
- If uncertain, assign \"Unclear (40-60%)\" rather than making an incorrect classification.  

TEXT TO ANALYZE:  
{$content}";

        $result = $this->callOpenAI($prompt);

        // Parse the OpenAI response to extract the required information
        $response = $result['choices'][0]['message']['content'];

        // Extract confidence score (assuming it's in the format "Confidence Score: XX%")
        preg_match('/Confidence Score:\s*(\d+)%/', $response, $confidenceMatches);
        $confidenceScore = isset($confidenceMatches[1]) ? (int)$confidenceMatches[1] : 50;

        // Extract category classification (assuming it's in the format "Category Classification: X")
        preg_match('/Category Classification:\s*(\d+)/', $response, $categoryMatches);
        $category = isset($categoryMatches[1]) ? (int)$categoryMatches[1] : 3;

        // Extract detailed justification
        preg_match('/Detailed Justification:\s*(.*?)(?=Top 3 Most Compelling Indicators:|$)/s', $response, $justificationMatches);
        $detailedJustification = isset($justificationMatches[1]) ? trim($justificationMatches[1]) : '';

        // Extract top 3 indicators
        preg_match('/Top 3 Most Compelling Indicators:\s*(.*?)(?=TEXT TO ANALYZE:|$)/s', $response, $indicatorsMatches);
        $topIndicators = isset($indicatorsMatches[1]) ? trim($indicatorsMatches[1]) : '';

        // Convert category to probability range
        $aiProbability = $this->categoryToProbability($category);

        return [
            'ai_probability' => $aiProbability,
            'classification' => $this->getClassification($aiProbability),
            'details' => [
                'openai_response' => $response,
                'confidence_score' => $confidenceScore,
                'category' => $category,
                'content_length' => strlen($content),
                'word_count' => str_word_count($content),
                'detailed_justification' => $detailedJustification,
                'top_indicators' => $topIndicators,
            ]
        ];
    }

    private function callOpenAI(string $prompt): array
    {
        $apiKey = config('services.openai.api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI text classifier specializing in detecting AI-generated content.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        if (!$response->successful()) {
            throw new \Exception('OpenAI API request failed: ' . $response->body());
        }

        return $response->json();
    }

    private function categoryToProbability(int $category): float
    {
        $ranges = [
            1 => 0.1,  // 0-20%
            2 => 0.3,  // 20-40%
            3 => 0.5,  // 40-60%
            4 => 0.7,  // 60-80%
            5 => 0.9,  // 80-100%
        ];

        return $ranges[$category] ?? 0.5;
    }

    private function getClassification(float $probability): string
    {
        if ($probability <= 0.2) {
            return 'Very unlikely AI-generated';
        } elseif ($probability <= 0.4) {
            return 'Unlikely AI-generated';
        } elseif ($probability <= 0.6) {
            return 'Unclear if AI-generated';
        } elseif ($probability <= 0.8) {
            return 'Possibly AI-generated';
        } else {
            return 'Likely AI-generated';
        }
    }
}

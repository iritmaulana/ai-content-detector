<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AIDetectorService
{
    /**
     * Analyze text to determine if it's AI-generated
     *
     * @param string $content The text content to analyze
     * @return array Analysis results
     */
    public function analyze(string $content): array
    {
        // Menggunakan analisis statistik yang ditingkatkan
        $statisticalResult = $this->statisticalAnalysis($content);

        // Tambahan: deteksi pola umum AI
        $commonAIPatterns = $this->detectCommonAIPatterns($content);

        // Tambahan: analisis gaya penulisan
        $styleScore = $this->analyzeWritingStyle($content);

        // Tambahan: analisis konteks
        $contextScore = $this->analyzeContext($content);

        // Gabungkan semua skor
        $baseScore = $statisticalResult['probability'];
        $aiProbability = $baseScore + ($commonAIPatterns * 0.1) + ($styleScore * 0.1) + ($contextScore * 0.05);
        $aiProbability = min(1, max(0, $aiProbability)); // Pastikan tetap dalam range 0-1

        // Klasifikasi berdasarkan probabilitas
        $classification = $this->getClassification($aiProbability);

        return [
            'ai_probability' => $aiProbability,
            'classification' => $classification,
            'details' => [
                'statistical_analysis' => $statisticalResult['details'],
                'common_ai_patterns' => $commonAIPatterns,
                'writing_style_score' => $styleScore,
                'context_score' => $contextScore,
                'content_length' => strlen($content),
                'word_count' => str_word_count($content),
            ]
        ];
    }

    /**
     * Statistical analysis of text patterns
     */
    private function statisticalAnalysis(string $content): array
    {
        // Text preprocessing
        $cleanContent = preg_replace('/\s+/', ' ', $content);
        $cleanContent = strtolower(trim($cleanContent));
        $words = explode(' ', $cleanContent);

        // Calculate metrics
        $metrics = [
            'avg_word_length' => $this->calculateAvgWordLength($words),
            'sentence_variance' => $this->calculateSentenceVariance($content),
            'repetition_score' => $this->calculateRepetitionScore($words),
            'perplexity' => $this->calculatePerplexity($content),
            'burstiness' => $this->calculateBurstiness($content),
            'transition_words_ratio' => $this->calculateTransitionWordsRatio($content), // Baru
            'sentence_starter_variety' => $this->calculateSentenceStarterVariety($content), // Baru
        ];

        // Weight and combine metrics
        $probability = $this->combineMetrics($metrics);

        return [
            'probability' => $probability,
            'details' => $metrics
        ];
    }

    /**
     * Calculate average word length
     */
    private function calculateAvgWordLength(array $words): float
    {
        $totalLength = array_sum(array_map('strlen', $words));
        return count($words) > 0 ? $totalLength / count($words) : 0;
    }

    /**
     * Calculate sentence length variance (AI text often has uniform sentences)
     */
    private function calculateSentenceVariance(string $content): float
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $lengths = array_map('strlen', $sentences);

        if (count($lengths) <= 1) {
            return 0;
        }

        $mean = array_sum($lengths) / count($lengths);
        $variance = 0;

        foreach ($lengths as $length) {
            $variance += pow($length - $mean, 2);
        }

        return $variance / (count($lengths) - 1);
    }

    /**
     * Calculate repetition score (AI tends to reuse phrases)
     */
    private function calculateRepetitionScore(array $words): float
    {
        $wordCounts = array_count_values($words);
        $totalWords = count($words);
        $uniqueWords = count($wordCounts);

        // Calculate repetition ratio
        return $totalWords > 0 ? $uniqueWords / $totalWords : 0;
    }

    /**
     * Calculate text perplexity (simplified)
     */
    private function calculatePerplexity(string $content): float
    {
        // Simplified perplexity calculation
        // Real implementation would use an n-gram language model

        // For demonstration, we'll use a simple heuristic
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentenceCount = count($sentences);
        $wordCount = str_word_count($content);

        return $sentenceCount > 0 ? $wordCount / $sentenceCount : 0;
    }

    /**
     * Calculate text burstiness (human text has more variance)
     */
    private function calculateBurstiness(string $content): float
    {
        // Burstiness measures the variability in word and sentence patterns
        // For demonstration, using a simplified approach

        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $burstiness = 0;

        if (count($sentences) > 1) {
            $wordCounts = [];
            foreach ($sentences as $sentence) {
                $wordCounts[] = str_word_count($sentence);
            }

            $mean = array_sum($wordCounts) / count($wordCounts);
            $stdDev = 0;

            foreach ($wordCounts as $count) {
                $stdDev += pow($count - $mean, 2);
            }

            $stdDev = sqrt($stdDev / count($wordCounts));
            $burstiness = $mean > 0 ? $stdDev / $mean : 0;
        }

        return $burstiness;
    }

    /**
     * Hitung rasio kata transisi (AI sering menggunakan banyak kata transisi)
     */
    private function calculateTransitionWordsRatio(string $content): float
    {
        $transitionWords = [
            'furthermore',
            'moreover',
            'additionally',
            'consequently',
            'therefore',
            'thus',
            'hence',
            'nonetheless',
            'nevertheless',
            'conversely',
            'meanwhile',
            'subsequently',
            'alternatively'
        ];

        $wordCount = str_word_count(strtolower($content));
        $transitionCount = 0;

        foreach ($transitionWords as $word) {
            $transitionCount += substr_count(strtolower($content), $word);
        }

        return $wordCount > 0 ? $transitionCount / $wordCount : 0;
    }

    /**
     * Hitung variasi awal kalimat (AI sering menggunakan pola yang sama)
     */
    private function calculateSentenceStarterVariety(string $content): float
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);

        if (count($sentences) <= 3) {
            return 0.5; // Nilai default untuk teks pendek
        }

        $starters = [];
        foreach ($sentences as $sentence) {
            $words = explode(' ', trim($sentence));
            if (!empty($words[0])) {
                $starters[] = strtolower($words[0]);
            }
        }

        $uniqueStarters = count(array_unique($starters));
        $totalStarters = count($starters);

        return $totalStarters > 0 ? $uniqueStarters / $totalStarters : 0;
    }

    /**
     * Combine different metrics into a single probability score
     */
    private function combineMetrics(array $metrics): float
    {
        // Weights for each metric - disesuaikan untuk akurasi lebih baik
        $weights = [
            'avg_word_length' => 0.05,      // Kurangi bobot ini
            'sentence_variance' => 0.35,    // Tingkatkan bobot ini
            'repetition_score' => 0.3,      // Tingkatkan bobot ini
            'perplexity' => 0.15,           // Tetap
            'burstiness' => 0.15,           // Kurangi sedikit
            'transition_words_ratio' => 0.2, // Metrik baru
            'sentence_starter_variety' => 0.15, // Metrik baru
        ];

        // Hitung ulang total bobot
        $totalWeight = array_sum($weights);
        // Normalisasi bobot agar tetap 1.0
        foreach ($weights as $key => $value) {
            $weights[$key] = $value / $totalWeight;
        }

        // Normalize metrics
        $normalizedMetrics = [
            'avg_word_length' => $this->normalize($metrics['avg_word_length'], 4, 7),
            'sentence_variance' => $this->normalize($metrics['sentence_variance'], 5, 100),
            'repetition_score' => 1 - $this->normalize($metrics['repetition_score'], 0.3, 0.8),
            'perplexity' => $this->normalize($metrics['perplexity'], 5, 25),
            'burstiness' => 1 - $this->normalize($metrics['burstiness'], 0.2, 0.8),
            'transition_words_ratio' => $this->normalize($metrics['transition_words_ratio'], 0.01, 0.1),
            'sentence_starter_variety' => 1 - $this->normalize($metrics['sentence_starter_variety'], 0.3, 0.9),
        ];

        // Calculate weighted average
        $probability = 0;
        foreach ($normalizedMetrics as $key => $value) {
            $probability += $value * $weights[$key];
        }

        return min(1, max(0, $probability));
    }

    /**
     * Normalize a value between min and max to a 0-1 scale
     */
    private function normalize(float $value, float $min, float $max): float
    {
        if ($max == $min) return 0.5;
        return min(1, max(0, ($value - $min) / ($max - $min)));
    }

    /**
     * Deteksi pola yang umum dalam teks AI
     */
    private function detectCommonAIPatterns(string $content): float
    {
        $score = 0;

        // Deteksi pola-pola umum pada teks AI
        $patterns = [
            // Teks AI sering mengikuti struktur pengantar-isi-kesimpulan yang sangat formal
            '/\b(in this (article|text|essay|response), (we|I) will|this (article|text|essay|response) (aims|intends) to)\b/i' => 0.08,

            // Teks AI sering menggunakan frasa penutup yang formal dan berulang
            '/\b(in conclusion|to summarize|as a final note|wrapping up|to conclude)\b/i' => 0.07,

            // Teks AI sering menggunakan format daftar berurutan
            '/\b(first(ly)?|second(ly)?|third(ly)?|fourth(ly)?|finally|lastly).{0,30}(next|then|after that|subsequently)/is' => 0.06,

            // Teks AI sering menghasilkan paragraf dengan panjang yang sangat konsisten
            '/(\n\n.{100,150}){3,}/s' => 0.08,

            // Teks AI sering menggunakan frasa introductory yang tidak natural
            '/\b(it is (worth|important|crucial|essential|necessary) to (note|mention|emphasize|highlight|consider))\b/i' => 0.09,

            // Teks AI sering menggunakan kata-kata yang terlalu formal
            '/\b(utilize|implementation|methodology|aforementioned|conceptualize|paradigm|subsequently)\b/i' => 0.06,

            // Teks AI sering menggunakan frasa transisi yang berpola
            '/\b(on the one hand|on the other hand|in light of|with regard to|as mentioned earlier)\b/i' => 0.07,

            // Teks AI sering membuat akhir paragraf yang sangat "transisi" ke paragraf berikutnya
            '/\b(now, let\'s|next, we will|let us now|having discussed|moving on to)\b/i' => 0.08,

            // Teks AI sering menggunakan klausa yang sangat formal "it is" + adverb + adjective
            '/\b(it is (particularly|especially|notably|significantly|remarkably|exceptionally) (important|interesting|noteworthy|relevant|crucial))\b/i' => 0.09,
        ];

        foreach ($patterns as $pattern => $weight) {
            $matches = preg_match_all($pattern, $content);
            $score += min(0.3, $matches * $weight);
        }

        return min(0.5, $score); // Maksimal 0.5 untuk menghindari false positive
    }

    private function analyzeWritingStyle(string $content): float
    {
        // Deteksi pola gaya penulisan AI

        // Ukur keragaman kosakata
        $words = str_word_count(strtolower($content), 1);
        $uniqueWords = count(array_unique($words));
        $totalWords = count($words);
        $vocabularyDiversity = $totalWords > 0 ? $uniqueWords / $totalWords : 0;

        // Ukur rasio kata sambung
        $conjunctions = ['and', 'but', 'or', 'so', 'because', 'although', 'since', 'unless', 'while'];
        $conjunctionCount = 0;
        foreach ($conjunctions as $conj) {
            $conjunctionCount += substr_count(strtolower($content), ' ' . $conj . ' ');
        }
        $conjunctionRatio = $totalWords > 0 ? $conjunctionCount / $totalWords : 0;

        // Ukur penggunaan kalimat aktif vs pasif (AI sering menggunakan pasif)
        $passiveVoicePatterns = [
            '/\b(is|are|was|were|be|been|being) [a-z]+ed\b/i',
            '/\b(has|have|had) been [a-z]+ed\b/i',
        ];

        $passiveCount = 0;
        foreach ($passiveVoicePatterns as $pattern) {
            $passiveCount += preg_match_all($pattern, $content);
        }

        $sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $passiveRatio = count($sentences) > 0 ? $passiveCount / count($sentences) : 0;

        // Ukur penggunaan frasa berbentuk rumus (AI sering menggunakan)
        $formulaicPhrases = [
            'it is important to note that',
            'it should be noted that',
            'it is worth mentioning that',
            'it is crucial to understand',
            'it is essential to consider',
        ];

        $formulaicCount = 0;
        foreach ($formulaicPhrases as $phrase) {
            $formulaicCount += substr_count(strtolower($content), $phrase);
        }
        $formulaicRatio = $totalWords > 0 ? ($formulaicCount * 10) / $totalWords : 0; // Bobot lebih karena signifikan

        // Hitung penggunaan kata ganti orang pertama (I, we) vs ganti orang ketiga (AI lebih formal)
        $firstPersonCount = preg_match_all('/\b(I|we|my|our|myself|ourselves)\b/i', $content);
        $thirdPersonCount = preg_match_all('/\b(it|they|he|she|them|their|its|his|her)\b/i', $content);

        $personRatio = 0;
        if (($firstPersonCount + $thirdPersonCount) > 0) {
            $personRatio = $thirdPersonCount / ($firstPersonCount + $thirdPersonCount);
        }

        // Gabungkan metrik dengan bobot
        $styleScore =
            (0.25 * (1 - $vocabularyDiversity)) + // Semakin rendah keragaman kosakata, semakin tinggi skor AI
            (0.15 * $conjunctionRatio) +          // Penggunaan kata sambung berlebihan menunjukkan AI
            (0.25 * $passiveRatio) +              // Penggunaan pasif berlebihan menunjukkan AI
            (0.20 * $formulaicRatio) +            // Frasa formula menunjukkan AI
            (0.15 * $personRatio);                // Penggunaan orang ketiga berlebihan menunjukkan AI

        return min(1, $styleScore);
    }

    /**
     * Analisis konteks dokumen
     */
    private function analyzeContext(string $content): float
    {
        // Hitung konsistensi topik - teks AI cenderung lebih konsisten
        $paragraphs = explode("\n\n", $content);
        $topicConsistency = 0;

        if (count($paragraphs) > 2) {
            // Implementasi sederhana: hitung kata kunci yang muncul di berbagai paragraf
            $keywordSets = [];

            foreach ($paragraphs as $paragraph) {
                $words = explode(' ', strtolower($paragraph));
                $words = array_filter($words, function ($word) {
                    return strlen($word) > 3; // Hanya kata-kata bermakna
                });
                $keywordSets[] = array_unique($words);
            }

            // Hitung kata yang muncul di semua paragraf
            $commonWords = call_user_func_array('array_intersect', $keywordSets);
            $topicConsistency = count($commonWords) / (count($paragraphs) * 0.5);
        }

        // Ukur kesinambungan antar paragraf
        $paragraphCoherence = 0;
        if (count($paragraphs) > 1) {
            $coherenceScores = [];
            for ($i = 0; $i < count($paragraphs) - 1; $i++) {
                $currPara = strtolower($paragraphs[$i]);
                $nextPara = strtolower($paragraphs[$i + 1]);

                // Cari kata-kata dari paragraf pertama yang muncul di paragraf berikutnya
                $currWords = str_word_count($currPara, 1);
                $nextWords = str_word_count($nextPara, 1);

                $sharedWords = array_intersect($currWords, $nextWords);
                $coherenceScores[] = count($sharedWords) / (count($currWords) * 0.2); // Normalisasi
            }

            $paragraphCoherence = array_sum($coherenceScores) / count($coherenceScores);
        }

        // Ukur struktur paragraf (AI sering memiliki pola teratur)
        $paragraphStructure = 0;
        if (count($paragraphs) > 3) {
            $paraLengths = array_map('strlen', $paragraphs);
            $avgLength = array_sum($paraLengths) / count($paraLengths);

            // Hitung deviasi dari panjang rata-rata
            $deviations = [];
            foreach ($paraLengths as $length) {
                $deviations[] = abs($length - $avgLength) / $avgLength;
            }

            // Teks AI cenderung memiliki deviasi rendah (ukuran paragraf konsisten)
            $avgDeviation = array_sum($deviations) / count($deviations);
            $paragraphStructure = 1 - min(1, $avgDeviation * 2); // Invers deviasi
        }

        // Gabungkan metrik
        $contextScore =
            (0.4 * $topicConsistency) +   // Konsistensi topik yang tinggi menunjukkan AI
            (0.3 * $paragraphCoherence) + // Koherensi yang sangat terstruktur menunjukkan AI
            (0.3 * $paragraphStructure);  // Struktur paragraf yang teratur menunjukkan AI

        return min(1, $contextScore);
    }
    /**
     * Get classification based on probability
     */
    private function getClassification(float $probability): string
    {
        if ($probability < 0.25) {        // Perkecil threshold untuk "Likely Human"
            return 'Likely Human';
        } elseif ($probability < 0.65) {  // Perluas rentang untuk "Possibly AI"
            return 'Possibly AI';
        } else {
            return 'Likely AI';
        }
    }
}

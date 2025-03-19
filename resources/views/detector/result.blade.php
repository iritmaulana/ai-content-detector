<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Content Detection Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 bg-white p-6 shadow-lg rounded-lg">
        <h1 class="text-2xl font-bold mb-4 text-center text-gray-800">AI Content Detection Result</h1>

        {{-- Hasil Deteksi --}}
        <div class="mb-4 p-4 rounded-lg border shadow-md bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700">Detection Result:</h2>

            <div class="flex flex-col space-y-2">
                <p class="text-xl font-bold text-red-600">
                    AI-Generated: {{ number_format($result['ai_probability'] * 100, 2) }}%
                </p>
                <p class="text-xl font-bold text-green-600">
                    Human-Written: {{ number_format((1 - $result['ai_probability']) * 100, 2) }}%
                </p>
            </div>

            {{-- Progress bar --}}
            <div class="mt-4 h-6 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-red-500 transition-all duration-500"
                    style="width: {{ number_format($result['ai_probability'] * 100, 2) }}%">
                </div>
            </div>
        </div>

        {{-- Konten yang dianalisis dengan highlight --}}
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-700">Your Content:</h2>
            <div class="bg-gray-200 p-4 rounded-md text-gray-800 max-h-60 overflow-y-auto">
                @foreach ($result['details']['sentence_analysis'] as $analysis)
                    <span
                        class="inline-block mb-1 {{ $analysis['ai_probability'] > 0.6 ? 'bg-red-200' : ($analysis['ai_probability'] > 0.4 ? 'bg-yellow-200' : 'bg-green-200') }} p-1 rounded">
                        {{ $analysis['sentence'] }}
                        @if ($analysis['ai_probability'] > 0.6)
                            <span class="text-xs text-red-600 ml-1">(AI-generated)</span>
                        @elseif ($analysis['ai_probability'] > 0.4)
                            <span class="text-xs text-yellow-600 ml-1">(Possibly AI)</span>
                        @else
                            <span class="text-xs text-green-600 ml-1">(Human-written)</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Detail Analisis --}}
        <div class="mb-6 p-4 rounded-lg border shadow-md bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700">Analysis Details:</h2>
            <ul class="mt-2 space-y-1 text-gray-800">
                <li><strong>Classification:</strong> {{ $result['classification'] }}</li>
                <li><strong>Confidence Score:</strong> {{ $result['details']['confidence_score'] }}%</li>
                <li><strong>Content Length:</strong> {{ $result['details']['content_length'] }} characters</li>
                <li><strong>Word Count:</strong> {{ $result['details']['word_count'] }} words</li>
                <li><strong>Sentences Analyzed:</strong> {{ count($result['details']['sentence_analysis']) }}</li>
                <li><strong>AI-generated Sentences:</strong>
                    {{ count(array_filter($result['details']['sentence_analysis'], fn($s) => $s['ai_probability'] > 0.6)) }}
                </li>
                <li><strong>Possibly AI Sentences:</strong>
                    {{ count(array_filter($result['details']['sentence_analysis'], fn($s) => $s['ai_probability'] > 0.4 && $s['ai_probability'] <= 0.6)) }}
                </li>
                <li><strong>Human-written Sentences:</strong>
                    {{ count(array_filter($result['details']['sentence_analysis'], fn($s) => $s['ai_probability'] <= 0.4)) }}
                </li>
            </ul>
        </div>

        {{-- Detailed Justification --}}
        <div class="mb-6 p-4 rounded-lg border shadow-md bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700">Detailed Justification:</h2>
            <div class="mt-2 text-gray-800 whitespace-pre-line">
                {{ $result['details']['detailed_justification'] }}
            </div>
        </div>

        {{-- Top Indicators --}}
        <div class="mb-6 p-4 rounded-lg border shadow-md bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700">Top 3 Most Compelling Indicators:</h2>
            <div class="mt-2 text-gray-800 whitespace-pre-line">
                {{ $result['details']['top_indicators'] }}
            </div>
        </div>

        {{-- Tombol Kembali --}}
        <div class="text-center">
            <a href="{{ route('detector.index') }}"
                class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                Analyze Again
            </a>
        </div>
    </div>
</body>

</html>

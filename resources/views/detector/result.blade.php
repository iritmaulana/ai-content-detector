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

        {{-- Konten yang dianalisis --}}
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-700">Your Content:</h2>
            <p class="bg-gray-200 p-4 rounded-md text-gray-800 max-h-60 overflow-y-auto">
                {{ $content }}
            </p>
        </div>

        {{-- Hasil Deteksi --}}
        <div class="mb-4 p-4 rounded-lg border shadow-md bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700">Detection Result:</h2>

            <p
                class="text-xl font-bold 
                @if ($result['ai_probability'] > 0.7) text-red-600 
                @elseif ($result['ai_probability'] > 0.4) text-yellow-600 
                @else text-green-600 @endif">
                AI Probability: {{ number_format($result['ai_probability'] * 100, 2) }}%
            </p>

            <p class="text-lg font-medium mt-2 text-gray-700">
                Classification:
                <span class="font-bold">
                    {{ $result['classification'] }}
                </span>
            </p>

            <p class="text-lg font-medium mt-2 text-gray-700">
                Confidence Score:
                <span class="font-bold">
                    {{ $result['details']['confidence_score'] }}%
                </span>
            </p>
        </div>

        {{-- Detail Analisis --}}
        <div class="mb-6 p-4 rounded-lg border shadow-md bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700">Analysis Details:</h2>
            <ul class="mt-2 space-y-1 text-gray-800">
                <li><strong>Content Length:</strong> {{ $result['details']['content_length'] }} characters</li>
                <li><strong>Word Count:</strong> {{ $result['details']['word_count'] }} words</li>
                {{-- <li><strong>Statistical Analysis:</strong> {{ count($result['details']['statistical_analysis']) }}
                    parameters analyzed</li> --}}
                {{-- <li><strong>API Analysis:</strong> {{ count($result['details']['api_analysis']) }} sources checked</li> --}}
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

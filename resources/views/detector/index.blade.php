<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Content Detector</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 bg-white p-6 shadow-lg rounded-lg">
        <h1 class="text-2xl font-bold mb-4 text-center">AI Content Detector</h1>

        @if (session('error'))
            <div class="bg-red-200 text-red-700 p-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('detector.analyze') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="content" class="block text-gray-700 font-semibold mb-2">Enter Text:</label>
                <textarea id="content" name="content" rows="6"
                    class="w-full border rounded-lg p-3 focus:ring focus:ring-blue-300" placeholder="Type or paste content here..."></textarea>
                @error('content')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg hover:bg-blue-700 transition">
                Analyze
            </button>
        </form>

        {{-- Classification Information --}}
        <div class="mt-8 p-4 rounded-lg border shadow-md bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Classification Scale:</h2>

            <div class="space-y-4">
                <div class="p-3 rounded-lg bg-green-50 border border-green-200">
                    <h3 class="font-semibold text-green-700">1. Very Unlikely AI-generated (0-20%)</h3>
                    <p class="text-sm text-gray-600 mt-1">Strong human markers detected, such as personal anecdotes,
                        unique word choices, emotional shifts, and natural inconsistencies.</p>
                </div>

                <div class="p-3 rounded-lg bg-green-50 border border-green-200">
                    <h3 class="font-semibold text-green-700">2. Unlikely AI-generated (20-40%)</h3>
                    <p class="text-sm text-gray-600 mt-1">The text appears mostly human-written, but minor AI-like
                        traits suggest some level of AI assistance.</p>
                </div>

                <div class="p-3 rounded-lg bg-yellow-50 border border-yellow-200">
                    <h3 class="font-semibold text-yellow-700">3. Unclear if AI-generated (40-60%)</h3>
                    <p class="text-sm text-gray-600 mt-1">The text contains a mix of human and AI traits, making
                        classification ambiguous.</p>
                </div>

                <div class="p-3 rounded-lg bg-red-50 border border-red-200">
                    <h3 class="font-semibold text-red-700">4. Possibly AI-generated (60-80%)</h3>
                    <p class="text-sm text-gray-600 mt-1">The text displays multiple AI-like features, such as overly
                        structured formatting and generic phrasing.</p>
                </div>

                <div class="p-3 rounded-lg bg-red-50 border border-red-200">
                    <h3 class="font-semibold text-red-700">5. Likely AI-generated (80-100%)</h3>
                    <p class="text-sm text-gray-600 mt-1">Strong AI indicators detected: highly structured, unnatural
                        consistency, and lack of personal engagement.</p>
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-600">
                <p class="font-medium mb-2">How it works:</p>
                <p>Our AI detector analyzes your text using advanced linguistic forensics to identify patterns that
                    distinguish human writing from AI-generated content. The analysis considers:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>Linguistic patterns and sentence structures</li>
                    <li>Content depth and originality</li>
                    <li>Stylistic variability</li>
                    <li>Logical flow and coherence</li>
                    <li>Technical and structural indicators</li>
                    <li>Contextual and conceptual depth</li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>

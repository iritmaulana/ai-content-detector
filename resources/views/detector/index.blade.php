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
    </div>
</body>

</html>

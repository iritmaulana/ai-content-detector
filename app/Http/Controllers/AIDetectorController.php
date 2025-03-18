<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\DetectionHistory;
use App\Services\AIDetectorServiceNew;

class AIDetectorController extends Controller
{
    protected $detectorService;

    public function __construct(AIDetectorServiceNew $detectorService)
    {
        $this->detectorService = $detectorService;
    }

    public function index()
    {
        return view('detector.index');
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'content' => 'required|string|min:100',
        ]);

        $content = $request->input('content');

        // Analyze the content
        $result = $this->detectorService->analyze($content);

        // Save detection history
        // if ($request->user()) {
        //     DetectionHistory::create([
        //         'user_id' => $request->user()->id,
        //         'content' => $content,
        //         'ai_probability' => $result['ai_probability'],
        //         'details' => json_encode($result['details']),
        //     ]);
        // }
        return view('detector.result', [
            'content' => $content,
            'result' => $result,
        ]);
    }

    public function history(Request $request)
    {
        // $history = DetectionHistory::where('user_id', $request->user()->id)
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(10);

        return view('detector.history', [
            'history' => $history,
        ]);
    }
}

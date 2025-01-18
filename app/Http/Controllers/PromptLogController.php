<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;
use App\Models\PromptLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PromptLogController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    // Add a new prompt-response log
    public function store(Request $request)
    {
        $this->middleware('throttle:60,1'); // Limit to 60 requests per minute per IP
        try {
            $validated = $request->validate([
                'prompt' => 'required|string|max:1000',
            ]);
    
            // Call OpenAI GPT API
            $response = $this->openAIService->getResponse($validated['prompt']);
            
            print_r($response);
            $aiResponse = $response['choices'][0]['text'];

            $responseEvaluate = $this->openAIService->evaluate($validated['prompt'] , $aiResponse);
    
            $validated['response'] = $aiResponse;
            $validated['responseEvaluation'] = $responseEvaluate;
            $validated['timestamp'] = now();
    
            $log = PromptLog::create($validated);
    
            return response()->json(['message' => 'Prompt logged successfully', 'data' => $log], 201);
        } catch (Exception $exception) {
            Log::critical('Error: ' . $exception->getMessage());
            Log::critical('In file: ' . $exception->getFile() . ' on line: ' . $exception->getLine());
            return response()->json(['message' => 'Error during Prompt logged...', 'error' => $exception->getMessage()], 400);
        }
    }
    
    // Get the most used prompts
    public function mostUsedPrompts(Request $request)
    {
        $result = PromptLog::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => ['_id' => '$prompt', 'count' => ['$sum' => 1]]],
                ['$sort' => ['count' => -1]],
                ['$limit' => 10],
            ]);
        });

        $formattedResults = [];
        foreach ($result as $item) {
            $formattedResults[] = ['prompt' => $item['_id'], 'count' => $item['count']];
        }

        return response()->json(['data' => $formattedResults], 200);
    }

    // Get all logs with evaluations
    public function getLogs(Request $request)
    {
        $logs = PromptLog::whereNotNull('responseEvaluation')
            ->paginate($request->input('per_page', 10));

        return response()->json($logs, 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;
use App\Models\PromptLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
            
            // print_r($response);
            $aiResponse = $response['choices'][0]['text'];

            $responseEvaluate = $this->openAIService->evaluate($validated['prompt'] , $aiResponse);
    
            $validated['response'] = $aiResponse;
            $validated['responseEvaluation'] = $responseEvaluate;
            $validated['timestamp'] = now();
            
            DB::beginTransaction();
            $log = PromptLog::create($validated);
            DB::commit();

            return $this->success($log, 'Prompt logged successfully');
            // return response()->json(['message' => 'Prompt logged successfully', 'data' => $log], 201);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::critical('Error: ' . $exception->getMessage());
            Log::critical('In file: ' . $exception->getFile() . ' on line: ' . $exception->getLine());
            return $this->error(400, 'Error during Prompt logged... /n/r'.$exception->getMessage());
            // return response()->json(['message' => 'Error during Prompt logged...', 'error' => $exception->getMessage()], 400);
        }
    }
    
    // Get the most used prompts
    public function mostUsedPrompts(Request $request)
    {
        try {
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
    
            return $this->success($formattedResults, 'data fetched successfully');
            // return response()->json(['data' => $formattedResults], 200);
        } catch (Exception $exception) {
            Log::critical('Error: ' . $exception->getMessage());
            Log::critical('In file: ' . $exception->getFile() . ' on line: ' . $exception->getLine());
            return $this->error(400, 'Error during fetching data... /n/r'.$exception->getMessage());
        }
    }

    // Get all logs with evaluations
    public function getLogs(Request $request)
    {
        try {
            $logs = PromptLog::whereNotNull('responseEvaluation')
            ->paginate($request->input('per_page', 10));

            return $this->success($logs, 'data fetched successfully');
            // return response()->json($logs, 200);
        } catch (Exception $exception) {
            Log::critical('Error: ' . $exception->getMessage());
            Log::critical('In file: ' . $exception->getFile() . ' on line: ' . $exception->getLine());
            return $this->error(400, 'Error during fetching data... /n/r'.$exception->getMessage());
        }        
    }
}

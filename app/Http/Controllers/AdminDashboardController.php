<?php

namespace App\Http\Controllers;

use App\Models\PromptLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class AdminDashboardController extends Controller
{
    // Get dashboard metrics
    public function getMetrics()
    {
        try {
            $metrics = PromptLog::raw(function ($collection) {
                return $collection->aggregate([
                    ['$group' => [
                        '_id' => null,
                        'averageRelevance' => ['$avg' => '$responseEvaluation.relevance'],
                        'averageClarity' => ['$avg' => '$responseEvaluation.clarity'],
                        'averageTone' => ['$avg' => '$responseEvaluation.tone'],
                        'totalLogs' => ['$sum' => 1],
                    ]],
                ]);
            });
    
            $formattedMetrics = $metrics->first() ?? [
                'averageRelevance' => 0,
                'averageClarity' => 0,
                'averageTone' => 0,
                'totalLogs' => 0,
            ];
            
            return $this->success($formattedMetrics, 'data fetched successfully');
            // return response()->json(['metrics' => $formattedMetrics], 200);
        } catch (Exception $exception) {
            Log::critical('Error: ' . $exception->getMessage());
            Log::critical('In file: ' . $exception->getFile() . ' on line: ' . $exception->getLine());
            return $this->error(400, 'Error during fetching data... /n/r'.$exception->getMessage());
        } 
    }

    // Get suggestions for improving prompt design
    public function getSuggestions()
    {
        try {
            $suggestions = PromptLog::raw(function ($collection) {
                return $collection->aggregate([
                    ['$group' => [
                        '_id' => '$prompt',
                        'averageScore' => ['$avg' => '$responseEvaluation.totalScore'], // Assuming totalScore exists
                    ]],
                    ['$sort' => ['averageScore' => 1]], // Low scores suggest poor prompts
                    ['$limit' => 5],
                ]);
            });
    
            $formattedSuggestions = [];
            foreach ($suggestions as $item) {
                $formattedSuggestions[] = [
                    'prompt' => $item['_id'],
                    'averageScore' => $item['averageScore'],
                ];
            }
            
            return $this->success($formattedSuggestions, 'data fetched successfully');
            // return response()->json(['suggestions' => $formattedSuggestions], 200);
        } catch (Exception $exception) {
            Log::critical('Error: ' . $exception->getMessage());
            Log::critical('In file: ' . $exception->getFile() . ' on line: ' . $exception->getLine());
            return $this->error(400, 'Error during fetching data... /n/r'.$exception->getMessage());
        } 
    }
}

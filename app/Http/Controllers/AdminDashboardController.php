<?php

namespace App\Http\Controllers;

use App\Models\PromptLog;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    // Get dashboard metrics
    public function getMetrics()
    {
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

        return response()->json(['metrics' => $formattedMetrics], 200);
    }

    // Get suggestions for improving prompt design
    public function getSuggestions()
    {
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

        return response()->json(['suggestions' => $formattedSuggestions], 200);
    }
}

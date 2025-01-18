<?php
namespace App\Services;

use GuzzleHttp\Client;
use Sastrawi\Sentiment\SentimentAnalyzer;


class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.openai.com/v1/']);
    }

    public function getResponse(string $prompt): array
    {
        $response = $this->client->post('chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 150,
                'temperature' => 0.7,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function evaluate(string $prompt, string $response): array
    {
        $relevance = $this->evaluateRelevance($prompt, $response);
        $clarity = $this->evaluateClarity($response);
        $tone = $this->evaluateTone($response);
    
        // Ensure all scores are numeric and valid
        $data = [
            'relevance' => $relevance ?? 0,
            'clarity' => $clarity ?? 0,
            'tone' => $tone ?? 0,
        ];
    
        // Calculate the average
        $average = count($data) > 0 ? array_sum($data) / count($data) : 0;
    
        return [...$data, 'totalScore' => round($average, 2)];
    }

    private function evaluateRelevance($prompt, $response)
    {
        $promptKeywords = explode(' ', strtolower($prompt));
        $responseWords = explode(' ', strtolower($response));
        
        $matches = 0;
        foreach ($promptKeywords as $keyword) {
            if (in_array($keyword, $responseWords)) {
                $matches++;
            }
        }

        // Score from 1 to 5 based on the number of matches
        return min(5, max(1, intval(($matches / count($promptKeywords)) * 5)));
    }

    private function evaluateClarity($response)
    {
        $sentences = preg_split('/[.!?]+/', $response);
        $numSentences = count(array_filter($sentences));
        $numWords = str_word_count($response);
        
        // Simple clarity score based on sentence length
        if ($numSentences > 0) {
            $averageSentenceLength = $numWords / $numSentences;
            return min(5, max(1, intval((5 - ($averageSentenceLength / 10)) * 5)));
        }

        return 1; // Default score if no sentences
    }

    private function evaluateTone($response)
    {
        // Example using a hypothetical sentiment analysis function
        $sentimentScore = $this->analyzeSentiment($response); // Returns a score from -1 (negative) to 1 (positive)

        if ($sentimentScore > 0.5) {
            return 5; // Very positive
        } elseif ($sentimentScore > 0) {
            return 4; // Positive
        } elseif ($sentimentScore == 0) {
            return 3; // Neutral
        } elseif ($sentimentScore > -0.5) {
            return 2; // Negative
        } else {
            return 1; // Very negative
        }
    }

    private function analyzeSentiment($text)
    {
        $client = new Client();
        
        try {
            $response = $client->post('https://api.aylien.com/api/v1/sentiment', [
                'headers' => [
                    'X-AYLIEN-TextAPI-Application-ID' => env('AYLIEN_APP_ID'),
                    'X-AYLIEN-TextAPI-Application-Key' => env('AYLIEN_APP_KEY'),
                ],
                'json' => [
                    'text' => $text,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            
            // Map the polarity to a score
            switch ($result['polarity']) {
                case 'positive':
                    return 1; // Positive sentiment
                case 'negative':
                    return -1; // Negative sentiment
                case 'neutral':
                    return 0; // Neutral sentiment
                default:
                    return 0; // Default to neutral if unknown
            }
        } catch (\Exception $e) {
            // Handle exceptions (e.g., log the error)
            return 0; // Default to neutral on error
        }
    }

    // private function analyzeSentiment($text)
    // {
    //     $positiveWords = ['good', 'great', 'happy', 'love', 'excellent'];
    //     $negativeWords = ['bad', 'sad', 'hate', 'terrible', 'awful'];

    //     $score = 0;

    //     // Normalize the text to lowercase
    //     $text = strtolower($text);
        
    //     // Count positive words
    //     foreach ($positiveWords as $word) {
    //         if (strpos($text, $word) !== false) {
    //             $score++;
    //         }
    //     }

    //     // Count negative words
    //     foreach ($negativeWords as $word) {
    //         if (strpos($text, $word) !== false) {
    //             $score--;
    //         }
    //     }

    //     // Normalize score to -1, 0, 1
    //     if ($score > 0) {
    //         return 1; // Positive
    //     } elseif ($score < 0) {
    //         return -1; // Negative
    //     } else {
    //         return 0; // Neutral
    //     }
    // }
}
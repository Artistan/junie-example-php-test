<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatHelperController extends Controller
{
    private string $storageFile = 'chat_messages.json';

    public function index()
    {
        $messages = $this->loadMessages();
        return view('chat-helper', ['messages' => $messages]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $userMessage = trim($request->input('message'));

        // Append user message
        $messages = $this->loadMessages();
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
            'time' => now()->toIso8601String(),
        ];
        $this->saveMessages($messages);

        // Build prompt as required
        $prompt = "You are PHP developer assistant. Answer only to questions related to PHP and Web development. Answer shortly. If question is not relevant answer with one phrase \"I can't help you\". There is the user message: \n$userMessage";

        $answer = $this->askOpenAI($prompt);

        // Append assistant message
        $messages[] = [
            'role' => 'assistant',
            'content' => $answer ?? 'I cannot get an answer right now. Please try again later.',
            'time' => now()->toIso8601String(),
        ];
        $this->saveMessages($messages);

        return redirect()->route('chat-helper.index');
    }

    private function loadMessages(): array
    {
        try {
            if (Storage::exists($this->storageFile)) {
                $raw = Storage::get($this->storageFile);
                $data = json_decode($raw, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        } catch (\Throwable $e) {
            // ignore, return empty
        }
        return [];
    }

    private function saveMessages(array $messages): void
    {
        try {
            Storage::put($this->storageFile, json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function askOpenAI(string $prompt): ?string
    {
        $apiKey = env('OPENAI_API_KEY');
        if (!$apiKey) {
            return "I can't help you"; // Fallback when key is missing
        }

        $model = env('OPENAI_MODEL', 'gpt-4o-mini');

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 200,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return null;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            if (isset($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ApiConnection;
use App\Models\ChatSession;
use App\Models\Conversation;
use App\Services\ChatService;
use App\Http\Requests\ChatRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    /**
     * Show the chat interface.
     */
    public function index(Request $request): View
    {
        $userId      = auth()->id();
        $connections = ApiConnection::active()->orderBy('name')->get();

        $selectedConnectionId = $request->get('connection_id', $connections->first()?->id);
        $selectedConnection   = $connections->firstWhere('id', $selectedConnectionId);

        // Load the user's chat history sidebar
        $sessions = ChatSession::where('user_id', $userId)
            ->with(['apiConnection'])
            ->orderByDesc('last_message_at')
            ->limit(30)
            ->get();

        // Load messages for the selected session
        $currentSession = null;
        $conversations  = collect();

        if ($request->has('session')) {
            $currentSession = ChatSession::where('id', $request->get('session'))
                ->where('user_id', $userId)
                ->first();

            if ($currentSession) {
                $conversations = $currentSession->conversations()->get();

                // Override selected connection from the session
                if ($currentSession->api_connection_id) {
                    $selectedConnection = $connections->firstWhere('id', $currentSession->api_connection_id)
                                         ?? $selectedConnection;
                }
            }
        }

        return view('chat.index', compact(
            'connections',
            'selectedConnection',
            'conversations',
            'sessions',
            'currentSession'
        ));
    }

    /**
     * POST /chat/send — process a chat message.
     */
    public function send(Request $request): JsonResponse
    {
        set_time_limit(300);

        $validated = $request->validate([
            'connection_id' => ['required', 'exists:api_connections,id'],
            'message'       => ['required', 'string', 'max:5000'],
            'session_id'    => ['nullable', 'integer'],
        ]);

        $connection = ApiConnection::find($validated['connection_id']);

        if (! $connection->active) {
            return response()->json([
                'error' => "La connexion « {$connection->name} » est désactivée.",
            ], 403);
        }

        $result = $this->chatService->handle(
            $connection,
            $validated['message'],
            $validated['session_id'] ?? null
        );

        $conversation = $result['conversation'];
        $session      = $result['session'];

        return response()->json([
            'success'    => true,
            'session_id' => $session->id,
            'data'       => $conversation->toArray(),
        ]);
    }

    /**
     * DELETE /chat/sessions/{session} — delete a whole conversation thread.
     */
    public function destroySession(ChatSession $session): JsonResponse
    {
        // Make sure the session belongs to the authenticated user
        if ($session->user_id !== auth()->id()) {
            return response()->json(['error' => 'Non autorisé.'], 403);
        }

        $session->delete(); // Cascades to conversations via DB constraint
        return response()->json(['success' => true]);
    }
}

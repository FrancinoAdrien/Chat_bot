<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use App\Models\ApiConnection;
use App\Models\Conversation;
use App\Services\ChatService;
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
        $connections = ApiConnection::active()->orderBy('name')->get();
        $selectedConnectionId = $request->get('connection_id', $connections->first()?->id);
        $selectedConnection   = $connections->firstWhere('id', $selectedConnectionId);

        $conversations = $selectedConnection
            ? Conversation::where('api_connection_id', $selectedConnection->id)
                ->latest()
                ->limit(50)
                ->get()
                ->reverse()
                ->values()
            : collect();

        return view('chat.index', compact('connections', 'selectedConnection', 'conversations'));
    }

    /**
     * POST /api/chat — process a chat message.
     */
    public function send(ChatRequest $request): JsonResponse
    {
        // Increase execution time for slow local LLMs
        set_time_limit(300);

        // Re-validate based on new model
        $request->validate(['connection_id' => ['required', 'exists:api_connections,id']]);

        $connection = ApiConnection::find($request->integer('connection_id'));

        if (! $connection->active) {
            return response()->json([
                'error' => "La connexion « {$connection->name} » est désactivée.",
            ], 403);
        }

        $result = $this->chatService->handle($connection, $request->input('message'));

        return response()->json([
            'success'  => true,
            'data'     => $result->toArray(),
        ]);
    }

    /**
     * GET /api/chat/history — paginated history for a connection.
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate(['connection_id' => ['required', 'exists:api_connections,id']]);

        $conversations = Conversation::where('api_connection_id', $request->integer('connection_id'))
            ->latest()
            ->paginate(20);

        return response()->json($conversations);
    }

    /**
     * DELETE /api/chat/{conversation} — delete a single message.
     */
    public function destroy(Conversation $conversation): JsonResponse
    {
        $conversation->delete();
        return response()->json(['success' => true]);
    }
}

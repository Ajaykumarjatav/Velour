<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function message(Request $request)
    {
        $request->validate(['message' => ['required', 'string', 'max:500']]);

        $service  = new ChatbotService();
        $response = $service->respond($request->message);

        return response()->json($response);
    }
}

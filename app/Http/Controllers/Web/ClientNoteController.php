<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientNoteController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function store(Request $request, Client $client)
    {
        abort_unless($client->salon_id === $this->salon()->id, 403);

        $data = $request->validate([
            'type'    => ['required', 'in:general,allergy,medical,preference,formula,colour,other'],
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $client->notes()->create([
            'type'     => $data['type'],
            'content'  => $data['content'],
            'staff_id' => Auth::id(),
            'is_pinned'=> false,
        ]);

        return back()->with('success', 'Note added.');
    }

    public function pin(ClientNote $note)
    {
        abort_unless($note->client->salon_id === $this->salon()->id, 403);
        $note->update(['is_pinned' => !$note->is_pinned]);

        return back()->with('success', $note->is_pinned ? 'Note pinned.' : 'Note unpinned.');
    }

    public function destroy(ClientNote $note)
    {
        abort_unless($note->client->salon_id === $this->salon()->id, 403);
        $note->delete();

        return back()->with('success', 'Note deleted.');
    }
}

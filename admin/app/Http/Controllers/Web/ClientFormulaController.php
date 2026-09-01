<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Client;
use App\Models\ClientFormula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientFormulaController extends Controller
{
    use ResolvesActiveSalon;

    private function salon()
    {
        return $this->activeSalon();
    }

    public function create(Client $client)
    {
        abort_unless($client->salon_id === $this->salon()->id, 403);
        return view('clients.formula-create', compact('client'));
    }

    public function store(Request $request, Client $client)
    {
        abort_unless($client->salon_id === $this->salon()->id, 403);

        $data = $request->validate([
            'base_color'       => ['nullable', 'string', 'max:100'],
            'highlight_color'  => ['nullable', 'string', 'max:100'],
            'toner'            => ['nullable', 'string', 'max:100'],
            'developer'        => ['nullable', 'string', 'max:100'],
            'olaplex'          => ['nullable', 'string', 'max:100'],
            'natural_level'    => ['nullable', 'integer', 'min:1', 'max:10'],
            'target_level'     => ['nullable', 'integer', 'min:1', 'max:10'],
            'texture'          => ['nullable', 'string', 'max:100'],
            'scalp_condition'  => ['nullable', 'string', 'max:200'],
            'technique'        => ['nullable', 'string', 'max:200'],
            'result_notes'     => ['nullable', 'string', 'max:2000'],
            'goal'             => ['nullable', 'string', 'max:500'],
            'used_at'          => ['nullable', 'date'],
        ]);

        // Mark old current formula as not current
        $client->formulas()->where('is_current', true)->update(['is_current' => false]);

        $client->formulas()->create(array_merge($data, [
            'staff_id'   => Auth::id(),
            'is_current' => true,
            'used_at'    => $data['used_at'] ?? today(),
        ]));

        return redirect()->route('clients.show', $client)->with('success', 'Formula saved.');
    }

    public function show(Client $client, ClientFormula $formula)
    {
        abort_unless($client->salon_id === $this->salon()->id, 403);
        return view('clients.formula-show', compact('client', 'formula'));
    }

    public function destroy(Client $client, ClientFormula $formula)
    {
        abort_unless($client->salon_id === $this->salon()->id, 403);
        $formula->delete();

        return redirect()->route('clients.show', $client)->with('success', 'Formula deleted.');
    }
}

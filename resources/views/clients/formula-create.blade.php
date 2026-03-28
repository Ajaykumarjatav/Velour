@extends('layouts.app')
@section('title', 'New Formula — '.$client->first_name)
@section('page-title', 'New Colour Formula')
@section('content')

<div class="max-w-2xl">
  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
      <div class="w-10 h-10 rounded-xl bg-velour-100 flex items-center justify-center text-velour-700 font-bold">
        {{ strtoupper(substr($client->first_name, 0, 1)) }}
      </div>
      <div>
        <p class="font-semibold text-gray-900">{{ $client->first_name }} {{ $client->last_name }}</p>
        <p class="text-xs text-gray-400">New formula will replace the current one</p>
      </div>
    </div>

    <form action="{{ route('clients.formulas.store', $client->id) }}" method="POST" class="space-y-5">
      @csrf

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Date used</label>
          <input type="date" name="used_at" value="{{ old('used_at', today()->toDateString()) }}"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Technique</label>
          <input type="text" name="technique" value="{{ old('technique') }}" placeholder="e.g. Balayage, Full colour…"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
      </div>

      <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Colour Details</p>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Base colour</label>
          <input type="text" name="base_color" value="{{ old('base_color') }}" placeholder="e.g. Wella 6/1 + 40vol"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Highlight colour</label>
          <input type="text" name="highlight_color" value="{{ old('highlight_color') }}" placeholder="e.g. Lightener + 30vol"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Toner</label>
          <input type="text" name="toner" value="{{ old('toner') }}" placeholder="e.g. Redken Shades EQ 9P"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Developer</label>
          <input type="text" name="developer" value="{{ old('developer') }}" placeholder="e.g. 20 vol, 6%"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Olaplex</label>
          <input type="text" name="olaplex" value="{{ old('olaplex') }}" placeholder="e.g. No.1 Bond Multiplier"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
      </div>

      <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hair Assessment</p>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Natural level (1–10)</label>
          <input type="number" name="natural_level" value="{{ old('natural_level') }}" min="1" max="10"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Target level (1–10)</label>
          <input type="number" name="target_level" value="{{ old('target_level') }}" min="1" max="10"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Texture</label>
          <input type="text" name="texture" value="{{ old('texture') }}" placeholder="e.g. Fine, Medium, Coarse"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Scalp condition</label>
          <input type="text" name="scalp_condition" value="{{ old('scalp_condition') }}" placeholder="e.g. Healthy, Sensitive"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Goal</label>
        <input type="text" name="goal" value="{{ old('goal') }}" placeholder="e.g. Brighter highlights, cover greys"
               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Result notes</label>
        <textarea name="result_notes" rows="3" placeholder="How did the colour turn out? Any notes for next time…"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 resize-none">{{ old('result_notes') }}</textarea>
      </div>

      <div class="flex gap-3 pt-2 border-t border-gray-100">
        <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
          Save Formula
        </button>
        <a href="{{ route('clients.show', $client->id) }}"
           class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-600 transition-colors">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

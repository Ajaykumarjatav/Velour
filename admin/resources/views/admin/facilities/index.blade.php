@extends('layouts.admin')

@section('title', 'Facilities')

@section('page-title', 'Facilities (all tenants)')

@section('content')
  <p class="text-sm text-gray-400 mb-6">
    Rooms, stations, and areas recorded by each salon. To edit, open the tenant or use
    <span class="text-gray-300">Impersonate</span> from Users and use
    <strong class="text-white">Facilities</strong> in the salon app sidebar.
  </p>

  @if($facilities->isEmpty())
    <p class="text-gray-500 text-sm">No facilities have been created yet.</p>
  @else
    <div class="overflow-x-auto rounded-xl border border-gray-800">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-800 text-left text-gray-500">
            <th class="px-4 py-3 font-medium">Salon</th>
            <th class="px-4 py-3 font-medium">Facility</th>
            <th class="px-4 py-3 font-medium">Kind</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          @foreach($facilities as $facility)
            <tr class="text-gray-300 hover:bg-gray-900/50">
              <td class="px-4 py-3">
                @if($facility->salon)
                  <a href="{{ route('admin.tenants.show', $facility->salon_id) }}"
                     class="text-velour-400 hover:text-velour-300 font-medium">
                    {{ $facility->salon->name }}
                  </a>
                @else
                  <span class="text-gray-500">#{{ $facility->salon_id }}</span>
                @endif
              </td>
              <td class="px-4 py-3 text-white">{{ $facility->name }}</td>
              <td class="px-4 py-3 text-gray-400">
                {{ \App\Models\Facility::kindOptions()[$facility->kind] ?? $facility->kind }}
              </td>
              <td class="px-4 py-3 text-gray-400">
                {{ \App\Models\Facility::statusOptions()[$facility->status] ?? $facility->status }}
              </td>
              <td class="px-4 py-3 text-right">
                @if($facility->salon)
                  <a href="{{ url('/book/'.$facility->salon->slug) }}"
                     target="_blank"
                     rel="noopener"
                     class="text-xs text-gray-500 hover:text-gray-300">Public booking ↗</a>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      {{ $facilities->links() }}
    </div>
  @endif
@endsection

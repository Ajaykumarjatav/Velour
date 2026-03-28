@extends('layouts.auth')
@section('title', 'Delete Account')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12 bg-red-50">
  <div class="bg-white rounded-2xl border border-red-100 shadow-sm w-full max-w-md p-8">

    <div class="text-center mb-6">
      <div class="text-5xl mb-3">⚠️</div>
      <h1 class="text-2xl font-bold text-red-700" style="font-family:'Playfair Display',serif">
        Delete Your Account
      </h1>
      <p class="text-gray-500 text-sm mt-2">
        This action is <strong>irreversible</strong>. Your account, salon, staff records, client data,
        and all history will be permanently deleted after 30 days.
      </p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700 mb-5">
      {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('account.destroy') }}" class="space-y-4">
      @csrf
      @method('DELETE')

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Type <code class="bg-gray-100 px-1 py-0.5 rounded text-red-600 text-xs">DELETE MY ACCOUNT</code> to confirm
        </label>
        <input name="confirmation" type="text" required
          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-300 focus:border-transparent outline-none"
          placeholder="DELETE MY ACCOUNT">
        @error('confirmation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
        <input name="password" type="password" required
          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-300 outline-none"
          placeholder="Enter your password">
        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
        <textarea name="reason" rows="2"
          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-red-300 outline-none resize-none"
          placeholder="Help us improve — why are you leaving?"></textarea>
      </div>

      <button type="submit"
        class="w-full bg-red-600 text-white rounded-xl py-3 font-semibold hover:bg-red-700 transition text-sm">
        Permanently Delete Account
      </button>

      <a href="{{ route('account.sessions') }}"
        class="block text-center text-sm text-gray-400 hover:text-gray-600 mt-2">
        Cancel — keep my account
      </a>
    </form>
  </div>
</div>
@endsection

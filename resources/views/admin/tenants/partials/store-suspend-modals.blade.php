@php
    $suspendUrlTemplate = preg_replace('#/\d+(/suspend)$#', '/__STORE__$1', route('admin.tenants.suspend', 1));
    $unsuspendUrlTemplate = preg_replace('#/\d+(/unsuspend)$#', '/__STORE__$1', route('admin.tenants.unsuspend', 1));
@endphp

{{-- Suspend single store (must live inside parent x-data scope) --}}
<div x-show="suspendStore" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg" @click.outside="suspendStore = null">
    <h2 class="text-lg font-bold text-white mb-1">Suspend store</h2>
    <p class="text-sm text-gray-500 mb-4"><span x-text="suspendStoreName"></span> — public website & booking will stop for this location only.</p>
    <form method="POST"
          x-bind:action="`{{ $suspendUrlTemplate }}`.replace('__STORE__', suspendStore)"
          class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs text-gray-400 mb-1.5">Reason *</label>
        <select name="reason" required class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
          <option value="policy_violation">Policy violation</option>
          <option value="payment_failure">Payment failure</option>
          <option value="fraud">Fraud</option>
          <option value="abuse">Abuse</option>
          <option value="requested">Requested by owner</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1.5">Internal note</label>
        <textarea name="notes" rows="2" placeholder="Admin notes (not emailed)"
                  class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl resize-none"></textarea>
      </div>
      <label class="flex items-center gap-2 text-sm text-gray-300">
        <input type="checkbox" name="notify_owner" value="1" checked class="rounded"> Notify owner by email
      </label>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold rounded-xl bg-red-700 hover:bg-red-600 text-white">Confirm suspension</button>
        <button type="button" @click="suspendStore = null" class="px-4 py-2.5 text-sm rounded-xl border border-gray-700 text-gray-400">Cancel</button>
      </div>
    </form>
  </div>
</div>

{{-- Reactivate single store --}}
<div x-show="unsuspendStore" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg" @click.outside="unsuspendStore = null">
    <h2 class="text-lg font-bold text-white mb-1">Reactivate store</h2>
    <p class="text-sm text-gray-500 mb-4"><span x-text="unsuspendStoreName"></span> — website, booking & panel access will be restored.</p>
    <form method="POST"
          x-bind:action="`{{ $unsuspendUrlTemplate }}`.replace('__STORE__', unsuspendStore)"
          class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs text-gray-400 mb-1.5">Note (optional)</label>
        <input type="text" name="unsuspend_reason" placeholder="Reason for reactivation"
               class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
      </div>
      <label class="flex items-center gap-2 text-sm text-gray-300">
        <input type="checkbox" name="notify_owner" value="1" checked class="rounded"> Notify owner by email
      </label>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold rounded-xl bg-green-700 hover:bg-green-600 text-white">Reactivate store</button>
        <button type="button" @click="unsuspendStore = null" class="px-4 py-2.5 text-sm rounded-xl border border-gray-700 text-gray-400">Cancel</button>
      </div>
    </form>
  </div>
</div>

@extends('layouts.app')

@section('content')
<div class="space-y-6">
  <h1 class="text-2xl font-semibold">Inventory Analytics</h1>

  <form method="get" class="flex gap-3 items-end">
    <div>
      <label class="text-sm">From</label>
      <input type="date" name="from" value="{{ $from }}" class="border rounded px-2 py-1">
    </div>
    <div>
      <label class="text-sm">To</label>
      <input type="date" name="to" value="{{ $to }}" class="border rounded px-2 py-1">
    </div>
    <button class="px-3 py-2 bg-blue-600 text-white rounded">Apply</button>
  </form>

  <div>
    <h2 class="font-medium mb-2">Top 10 Items ({{ $from }} → {{ $to }})</h2>
    <table class="w-full text-sm border">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-2 border">#</th>
          <th class="p-2 border text-left">Item</th>
          <th class="p-2 border text-right">Qty Used</th>
        </tr>
      </thead>
      <tbody>
        @forelse($topItems as $i => $row)
          <tr>
            <td class="p-2 border text-center">{{ $i+1 }}</td>
            <td class="p-2 border">{{ $row->item_name ?: 'Unknown' }}</td>
            <td class="p-2 border text-right">{{ (int) $row->qty_used }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="p-4 text-center text-gray-500">No data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>
    <h2 class="font-medium mb-2">Usage by Service</h2>
    <table class="w-full text-sm border">
      <thead class="bg-gray-50">
        <tr>
          <th class="p-2 border text-left">Service</th>
          <th class="p-2 border text-left">Item</th>
          <th class="p-2 border text-right">Qty Used</th>
        </tr>
      </thead>
      <tbody>
        @forelse($byService as $row)
          <tr>
            <td class="p-2 border">{{ $row->service_name ?? '—' }}</td>
            <td class="p-2 border">{{ $row->item_name ?: 'Unknown' }}</td>
            <td class="p-2 border text-right">{{ (int) $row->qty_used }}</td>
          </tr>
        @empty
          <tr><td colspan="3" class="p-4 text-center text-gray-500">No data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

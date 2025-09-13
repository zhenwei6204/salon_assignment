@extends('layout.app') 

@section('content')
<h1>Define Inventory Usage for: {{ $service->name }}</h1>

@if(session('success'))
  <div style="color: green">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('admin.services.consumption.update', $service) }}">
  @csrf
  <table border="1" cellpadding="6">
    <tr>
      <th>Item</th>
      <th>Qty per Service</th>
    </tr>
    @foreach($items as $item)
      @php
        // Fall back to 'unit' if null (older records)
        $type = $item->tracking_type ?? 'unit';
        $step = $type === 'unit' ? '1' : '0.01';
        $placeholder = $type === 'unit'
          ? '0 (whole units)'
          : '0.00 '.$item->unit;
      @endphp
      <tr>
        <td>
          {{ $item->name }} ({{ $item->unit }})
          <small style="color:#666">
            — {{ $type === 'unit' ? 'whole units only' : 'decimals allowed' }}
          </small>
        </td>
        <td>
          <input
            type="number"
            step="{{ $step }}"
            min="0"
            name="items[{{ $item->id }}]"
            value="{{ $existing[$item->id] ?? '' }}"
            placeholder="{{ $placeholder }}"
          >
        </td>
      </tr>
    @endforeach
  </table>

  <button type="submit">Save</button>
</form>
@endsection

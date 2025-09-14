<form method="POST" action="{{ route('admin.items.store') }}" style="margin-bottom:1rem;">
  @csrf
  <input name="sku"  placeholder="SKU" required>
  <input name="name" placeholder="Name" required>
  <input name="unit" placeholder="Unit (pcs/ml/g)" value="pcs">

  <select name="tracking_type" required>
    <option value="unit">Whole units (pcs/box)</option>
    <option value="measure">Measured (ml/g)</option>
  </select>

  <input type="number" name="reorder_level" placeholder="Reorder level" value="0" min="0" required>
  <button type="submit">Add Item</button>
</form>

{{-- Optional: show tracking type in the list --}}
<table border="1" cellpadding="6">
  <tr><th>SKU</th><th>Name</th><th>Unit</th><th>Tracking</th><th>Reorder</th><th>On Hand</th></tr>
  @foreach($items as $item)
    <tr>
      <td>{{ $item->sku }}</td>
      <td>{{ $item->name }}</td>
      <td>{{ $item->unit }}</td>
      <td>{{ $item->tracking_type }}</td>
      <td>{{ $item->reorder_level }}</td>
      <td>{{ $item->onHand() }}</td>
    </tr>
  @endforeach
</table>

@php /** @var \App\Models\Item $item */ @endphp

<x-mail::message>
# Low stock alert

The following item dropped **below the reorder level** and needs attention.

<x-mail::panel>
**Item:** {{ $item->name }}  
**Current stock:** {{ $item->stock }} {{ $item->unit ?? '' }}  
**Reorder level:** {{ $item->reorder_level }}
@isset($item->sku)
**SKU:** {{ $item->sku }}
@endisset
</x-mail::panel>

<x-mail::table>
| Field         | Value |
|:--------------|:------|
| Item          | {{ $item->name }} |
| Current stock | {{ $item->stock }} {{ $item->unit ?? '' }} |
| Reorder level | {{ $item->reorder_level }} |
| Last updated  | {{ $item->updated_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }} |
</x-mail::table>

@isset($url)
<x-mail::button :url="$url">View Item</x-mail::button>
@endisset

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

@foreach($returns as $purchaseReturn)
    @php
        $purchaseReturn = $purchaseReturn; // local alias for included invoice
    @endphp
    @include('purchase-returns.pdf.invoice')
    @if (! $loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach

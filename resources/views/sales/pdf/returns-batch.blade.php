@foreach($salesReturns as $saleReturn)
    @php $saleReturn = $saleReturn; @endphp
    @include('sales.pdf.return-invoice')
    @if (! $loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach

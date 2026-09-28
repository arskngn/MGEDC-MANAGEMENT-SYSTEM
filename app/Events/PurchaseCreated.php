<?php

namespace App\Events;

use App\Models\Purchase;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\PrivateChannel;

class PurchaseCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    public function __construct(public Purchase $purchase)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('purchases'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->purchase->loadMissing(['supplier']);

        $netPayable = (float) $this->purchase->payable_amount;
        $paidAmount = (float) $this->purchase->paid_amount;
        $dueAmount = max(0, $netPayable - $paidAmount);

        return [
            'id' => $this->purchase->id,
            'invoice_no' => $this->purchase->invoice_no,
            'purchase_date' => $this->purchase->purchase_date ? $this->purchase->purchase_date->format('d M, Y') : now()->format('d M, Y'),
            'supplier_name' => $this->purchase->supplier->name ?? 'N/A',
            'supplier_phone' => $this->purchase->supplier->phone ?? '',
            'subtotal' => (float) $this->purchase->subtotal,
            'discount' => (float) $this->purchase->discount,
            'payable_amount' => $netPayable,
            'paid_amount' => $paidAmount,
            'due_amount' => $dueAmount,
            'status' => $this->purchase->status ?? 'ordered',
            'receipt_url' => route('purchases.receipt', $this->purchase),
            'edit_url' => route('purchases.edit', $this->purchase),
            'pdf_url' => route('purchases.invoice.pdf', $this->purchase),
            'created_at' => $this->purchase->created_at ? $this->purchase->created_at->toDateTimeString() : now()->toDateTimeString(),
        ];
    }
}

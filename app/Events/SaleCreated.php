<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Broadcasting\PrivateChannel;

class SaleCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public bool $afterCommit = true;

    /**
     * Create a new event instance.
     */
    public function __construct(public Sale $sale)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     * Using private channel to prevent unauthorized access to customer data.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('sales'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->sale->loadMissing(['customer', 'salesperson', 'user']);

        return [
            'id' => $this->sale->id,
            'invoice_no' => $this->sale->invoice_no,
            'sale_date' => $this->sale->sale_date ? $this->sale->sale_date->format('d M, Y') : now()->format('d M, Y'),
            'customer_name' => $this->sale->customer->name ?? 'Walk-in Customer',
            'customer_phone' => $this->sale->customer->phone ?? '',
            'salesperson_name' => $this->sale->salesperson->name ?? ($this->sale->user->name ?? 'N/A'),
            'salesperson_id' => $this->sale->salesperson_id,
            'user_id' => $this->sale->user_id,
            'receivable_amount' => (float) $this->sale->receivable_amount,
            'paid_amount' => (float) $this->sale->paid_amount,
            'due_amount' => (float) $this->sale->due_amount,
            'payment_status' => $this->sale->payment_status ?? 'pending',
            'status' => $this->sale->status ?? 'pending',
            'show_url' => route('sales.show', $this->sale),
            'edit_url' => route('sales.edit', $this->sale),
            'pdf_url' => route('sales.invoice.pdf', $this->sale),
            'created_at' => $this->sale->created_at ? $this->sale->created_at->toDateTimeString() : now()->toDateTimeString(),
        ];
    }
}

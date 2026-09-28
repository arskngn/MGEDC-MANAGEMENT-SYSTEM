<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnPayment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Services\PdfService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierPaymentController extends Controller
{
    public function index(Supplier $supplier): View
    {
        $payablePurchases = Purchase::query()
            ->with(['purchaseReturns'])
            ->where('supplier_id', $supplier->id)
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();

        $receivableReturns = PurchaseReturn::query()
            ->with('purchase')
            ->whereHas('purchase', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->get();

        $lines = collect();
        $payableTotal = 0.0;
        $receivableTotal = 0.0;

        foreach ($payablePurchases as $p) {
            $totalReturns = (float) $p->purchaseReturns->sum('receivable_amount');
            $netDue = max(0, ((float) $p->payable_amount - $totalReturns) - (float) $p->paid_amount);

            if ($netDue > 0.009) {
                $payableTotal += $netDue;
                $lines->push([
                    'reason' => 'Purchase',
                    'invoice_no' => $p->invoice_no,
                    'amount' => (float) $netDue,
                    'date' => $p->purchase_date ? strtotime((string) $p->purchase_date) : strtotime((string) $p->created_at),
                ]);
            }
        }

        foreach ($receivableReturns as $r) {
            $unpaidOnPurchase = max(0, (float) $r->purchase->payable_amount - (float) $r->purchase->paid_amount);
            $cashOwed = max(0, (float) $r->receivable_amount - $unpaidOnPurchase);
            $dueCash = max(0, $cashOwed - (float) $r->received_amount);

            if ($dueCash > 0.009) {
                $receivableTotal += $dueCash;
                $lines->push([
                    'reason' => 'Purchase Return',
                    'invoice_no' => $r->return_invoice_no,
                    'amount' => (float) $dueCash,
                    'date' => $r->return_date ? strtotime((string) $r->return_date) : strtotime((string) $r->created_at),
                ]);
            }
        }


        $lines = $lines->sortByDesc('date')->values()->all();

        return view('suppliers.payments', [
            'supplier' => $supplier,
            'payableTotal' => $payableTotal,
            'receivableTotal' => $receivableTotal,
            'lines' => $lines,
        ]);
    }

    public function clear(Request $request, Supplier $supplier)
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $supplier) {
            if ($user && $user->hasPermission('Store Supplier Payment')) {
                // Lock purchases for update to prevent race conditions
                $payablePurchases = Purchase::query()
                    ->where('supplier_id', $supplier->id)
                    ->whereRaw('payable_amount - paid_amount > 0.009')
                    ->lockForUpdate()
                    ->get();

                foreach ($payablePurchases as $p) {
                    $due = (float) $p->due_amount;
                    if ($due <= 0.0001) {
                        continue;
                    }

                    PurchasePayment::create([
                        'purchase_id' => $p->id,
                        'user_id' => $user->id,
                        'amount' => $due,
                    ]);

                    Purchase::where('id', $p->id)->increment('paid_amount', $due);
                }
            }

            if ($user && $user->hasPermission('Store Supplier Payment Receive')) {
                // Lock purchase returns for update to prevent race conditions
                $receivableReturns = PurchaseReturn::query()
                    ->whereRaw('receivable_amount - received_amount > 0.009')
                    ->whereHas('purchase', fn ($q) => $q->where('supplier_id', $supplier->id))
                    ->lockForUpdate()
                    ->get();

                foreach ($receivableReturns as $r) {
                    $due = (float) $r->due_amount;
                    if ($due <= 0.0001) {
                        continue;
                    }

                    PurchaseReturnPayment::create([
                        'purchase_return_id' => $r->id,
                        'user_id' => $user->id,
                        'amount' => $due,
                        'paid_at' => now(),
                    ]);

                    PurchaseReturn::where('id', $r->id)->increment('received_amount', $due);
                }
            }
        });

        return redirect()->route('suppliers.payments.index', $supplier)->with('success', 'Payments cleared successfully.');
    }

    public function allPayments(Request $request): View
    {
        $query = $request->input('search', '');
        $filter = $request->input('filter', 'all');
        $startDate = $request->input('date_from');
        $endDate = $request->input('date_to');
        $paymentMethod = $request->input('payment_method', 'all');

        // Get Purchase Payments
        $purchasePaymentsQuery = PurchasePayment::with(['purchase.supplier', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchase', function ($pq) use ($search) {
                    $pq->where('invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            })
            ->when($paymentMethod !== 'all', function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            });

        // Only include purchase payments if filter is 'all' or 'paid_for_purchase'
        $purchasePayments = collect();
        if ($filter === 'all' || $filter === 'paid_for_purchase') {
            $purchasePayments = $purchasePaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->purchase->invoice_no ?? 'N/A',
                    'date' => $payment->created_at,
                    'supplier' => $payment->purchase->supplier,
                    'trx' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Paid For Purchase',
                    'amount' => (float) $payment->amount,
                    'type' => 'payment',
                    'user' => $payment->user,
                    'payment_method' => $payment->payment_method,
                    'check_number' => $payment->check_number,
                    'check_date' => $payment->check_date,
                ];
            });
        }

        // Get Purchase Return Payments (Received)
        $returnPaymentsQuery = PurchaseReturnPayment::with(['purchaseReturn.purchase.supplier', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('purchaseReturn.purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchaseReturn', function ($pq) use ($search) {
                    $pq->where('return_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            })
            ->when($paymentMethod !== 'all', function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            });

        $returnPayments = collect();
        if ($filter === 'all' || $filter === 'received_for_purchase_return') {
            $returnPayments = $returnPaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->purchaseReturn->return_invoice_no ?? 'N/A',
                    'date' => $payment->created_at,
                    'supplier' => $payment->purchaseReturn->purchase->supplier,
                    'trx' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Received For Purchase Return',
                    'amount' => (float) $payment->amount,
                    'type' => 'receipt',
                    'user' => $payment->user,
                    'payment_method' => $payment->payment_method,
                    'check_number' => $payment->check_number,
                    'check_date' => $payment->check_date,
                ];
            });
        }

        // Merge and sort
        $allPayments = $purchasePayments->merge($returnPayments)
            ->sortByDesc('date')
            ->values();

        return view('supplier-payments.index', [
            'payments' => $allPayments,
            'search' => $query,
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'paymentMethod' => $paymentMethod
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = $request->input('search', '');
        $filter = $request->input('filter', 'all');
        $startDate = $request->input('date_from');
        $endDate = $request->input('date_to');
        $paymentMethod = $request->input('payment_method', 'all');

        $payments = collect();

        // Similar logic as allPayments but for PDF
        $purchasePaymentsQuery = PurchasePayment::with('purchase.supplier')
            ->when($query, function ($q, $search) {
                $q->whereHas('purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchase', function ($pq) use ($search) {
                    $pq->where('invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            })
            ->when($paymentMethod !== 'all', function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            });

        if ($filter === 'paid_for_purchase') {
            $purchasePaymentsQuery->whereHas('purchase');
        }

        $purchasePayments = $purchasePaymentsQuery->get()->map(function ($payment) {
            return [
                'invoice_no' => $payment->purchase->invoice_no,
                'date' => $payment->created_at->format('Y-m-d'),
                'supplier' => $payment->purchase->supplier->name,
                'trx' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'reason' => 'Paid For Purchase',
                'amount' => (float) $payment->amount,
                'type' => 'payment',
                'payment_method' => $payment->payment_method,
                'check_number' => $payment->check_number,
                'check_date' => $payment->check_date,
            ];
        });

        $returnPaymentsQuery = PurchaseReturnPayment::with('purchaseReturn.purchase.supplier')
            ->when($query, function ($q, $search) {
                $q->whereHas('purchaseReturn.purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchaseReturn', function ($pq) use ($search) {
                    $pq->where('return_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            })
            ->when($paymentMethod !== 'all', function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            });

        if ($filter === 'received_for_purchase_return') {
            $returnPaymentsQuery->whereHas('purchaseReturn');
        }

        $returnPayments = $returnPaymentsQuery->get()->map(function ($payment) {
            return [
                'invoice_no' => $payment->purchaseReturn->return_invoice_no,
                'date' => $payment->created_at?->format('Y-m-d'),
                'supplier' => $payment->purchaseReturn->purchase->supplier->name,
                'trx' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'reason' => 'Received For Purchase Return',
                'amount' => (float) $payment->amount,
                'type' => 'receipt',
                'payment_method' => $payment->payment_method,
                'check_number' => $payment->check_number,
                'check_date' => $payment->check_date,
            ];
        });

        $allPayments = $purchasePayments->merge($returnPayments)
            ->sortByDesc('date')
            ->values();

        if ($request->filled('trx')) {
            $selectedTrx = array_filter(explode(',', $request->input('trx')));
            $allPayments = $allPayments->whereIn('trx', $selectedTrx)->values();
        } elseif ($request->filled('ids')) {
            $selectedIds = array_filter(explode(',', $request->input('ids')));
            $allPayments = $allPayments->whereIn('id', $selectedIds)->values();
        }

        $totalPaid = (float) $allPayments->where('type', 'payment')->sum('amount');
        $totalRefunded = (float) $allPayments->where('type', 'receipt')->sum('amount');
        $netTotal = $totalPaid - $totalRefunded;

        $kpis = [
            'total_count' => $allPayments->count(),
            'total_paid' => $totalPaid,
            'total_refunded' => $totalRefunded,
            'net_total' => $netTotal,
        ];

        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'payments' => $allPayments,
            'kpis' => $kpis,
            'search' => $query,
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'paymentMethod' => $paymentMethod,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = 'supplier-payments-' . date('Y-m-d') . '.pdf';

        if ($request->has('print')) {
            return $pdfService->stream('supplier-payments.pdf', $data, $filename, 'landscape');
        }

        return $pdfService->download('supplier-payments.pdf', $data, $filename, 'landscape');
    }



    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $request->input('search', '');
        $filter = $request->input('filter', 'all');
        $startDate = $request->input('date_from');
        $endDate = $request->input('date_to');
        $paymentMethod = $request->input('payment_method', 'all');

        $payments = collect();

        // Similar logic as allPayments but for CSV
        $purchasePaymentsQuery = PurchasePayment::with('purchase.supplier')
            ->when($query, function ($q, $search) {
                $q->whereHas('purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchase', function ($pq) use ($search) {
                    $pq->where('invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            })
            ->when($paymentMethod !== 'all', function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            });

        if ($filter === 'paid_for_purchase') {
            $purchasePaymentsQuery->whereHas('purchase');
        }

        $purchasePayments = $purchasePaymentsQuery->get()->map(function ($payment) {
            return [
                'invoice_no' => $payment->purchase->invoice_no,
                'date' => $payment->created_at->format('Y-m-d'),
                'supplier' => $payment->purchase->supplier->name,
                'trx' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'reason' => 'Paid For Purchase',
                'amount' => (float) $payment->amount,
                'type' => 'payment',
                'payment_method' => $payment->payment_method,
                'check_number' => $payment->check_number,
                'check_date' => $payment->check_date,
            ];
        });

        $returnPaymentsQuery = PurchaseReturnPayment::with('purchaseReturn.purchase.supplier')
            ->when($query, function ($q, $search) {
                $q->whereHas('purchaseReturn.purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchaseReturn', function ($pq) use ($search) {
                    $pq->where('return_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            })
            ->when($paymentMethod !== 'all', function ($q) use ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            });

        if ($filter === 'received_for_purchase_return') {
            $returnPaymentsQuery->whereHas('purchaseReturn');
        }

        $returnPayments = $returnPaymentsQuery->get()->map(function ($payment) {
            return [
                'invoice_no' => $payment->purchaseReturn->return_invoice_no,
                'date' => $payment->created_at?->format('Y-m-d'),
                'supplier' => $payment->purchaseReturn->purchase->supplier->name,
                'trx' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'reason' => 'Received For Purchase Return',
                'amount' => (float) $payment->amount,
                'type' => 'receipt',
                'payment_method' => $payment->payment_method,
                'check_number' => $payment->check_number,
                'check_date' => $payment->check_date,
            ];
        });

        $allPayments = $purchasePayments->merge($returnPayments)
            ->sortByDesc('date')
            ->values();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="supplier-payments.csv"',
        ];

        $callback = function () use ($allPayments) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, ['S.N.', 'Invoice No.', 'Date', 'Supplier', 'TRX', 'Payment Method', 'Check Number', 'Check Date', 'Reason', 'Amount']);
            
            // CSV Data
            foreach ($allPayments as $index => $payment) {
                fputcsv($file, [
                    $index + 1,
                    $payment['invoice_no'],
                    $payment['date'],
                    $payment['supplier'],
                    $payment['trx'],
                    $payment['payment_method'] ?? 'N/A',
                    $payment['check_number'] ?? 'N/A',
                    $payment['check_date'] ? (is_string($payment['check_date']) ? $payment['check_date'] : $payment['check_date']->format('Y-m-d')) : 'N/A',
                    $payment['reason'],
                    formatCurrency($payment['amount'])
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}


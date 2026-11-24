<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Receipt::with('payment.bill')->latest('id')->get()]);
    }

    public function download(int $id): StreamedResponse
    {
        $receipt = Receipt::with('payment.bill')->findOrFail($id);

        return response()->streamDownload(function () use ($receipt): void {
            echo "Квитанція {$receipt->number}\n";
            echo "Нарахування №{$receipt->payment->bill_id}\n";
            echo "Сплачено {$receipt->total} грн\n";
            echo "Дата {$receipt->issued_at->format('Y-m-d H:i')}\n";
        }, $receipt->number.'.txt', ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}

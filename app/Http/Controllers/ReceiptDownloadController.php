<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Receipt;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptDownloadController extends Controller
{
    public function __invoke(Receipt $receipt): StreamedResponse
    {
        $receipt->load('payment.bill.utilityAccount');
        $this->authorize('view', $receipt);

        return response()->streamDownload(function () use ($receipt): void {
            echo "Квитанція {$receipt->number}\n";
            echo "Нарахування №{$receipt->payment->bill_id}\n";
            echo "Сплачено {$receipt->total} грн\n";
            echo "Дата {$receipt->issued_at->format('Y-m-d H:i')}\n";
        }, $receipt->number.'.txt', ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}

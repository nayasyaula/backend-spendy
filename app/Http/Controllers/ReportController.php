<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Transaction;

class ReportController extends Controller
{

    public function index(Request $request)
    {
        $details = Transaction::with(['details.coaFrom', 'details.coaTo'])->orderBy('id', 'asc')->get();

        $saldo = 0;
        $data = [];

        foreach ($details as $detail) {
            if (!$detail->transaction) {
                continue; 
            }

            $transaction = $detail->transaction;

            // Hitung saldo
            $saldo += ($detail->debit - $detail->credit);

            // Simpan data laporan ke database
            Report::create([
                'transaction_id' => $transaction->id,
                'coa_name' => $this->getCoaName($detail),
                'note' => $transaction->notes,
                'debit' => $detail->debit,
                'credit' => $detail->credit,
                'balance' => $saldo,
            ]);

            // Masukkan ke data untuk response
            $data[] = [
                'date' => $transaction->date,
                'coa_name' => $this->getCoaName($detail),
                'note' => $transaction->notes,
                'debit' => $detail->debit,
                'credit' => $detail->credit,
                'balance' => $saldo,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Report Transaction',
            'data' => $data
        ]);
    }

    private function getCoaName($detail)
    {
        if ($detail->coaFrom) {
            return $detail->coaFrom->name ?? '-';
        }

        if ($detail->coaTo) {
            return $detail->coaTo->name ?? '-';
        }

        return '-';
    }

    public function showReports()
    {
        $reports = Report::all();

        return response()->json([
            'success' => true,
            'message' => 'All Report Transactions',
            'data' => $reports
        ]);
    }
}

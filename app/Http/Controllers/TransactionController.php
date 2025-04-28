<?php

namespace App\Http\Controllers;

use App\Models\Coas;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // Get all transactions
    public function index()
    {
        $transactions = Transaction::with('details')->get();
        return response()->json($transactions);
    }

    // Store a new transaction
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'noref' => 'required|string',
    //         'type' => 'required|in:INCOME,EXPENSE',
    //         'date' => 'required|date',
    //         'notes' => 'nullable|string',
    //         'details' => 'required|array',
    //         'details.*.coa_from' => 'nullable|integer',
    //         'details.*.coa_to' => 'nullable|integer',
    //         'details.*.debit' => 'numeric|min:0',
    //         'details.*.credit' => 'numeric|min:0',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $transaction = Transaction::create($request->only(['noref', 'type', 'date', 'notes']));

    //         foreach ($request->details as $detail) {
    //             $transaction->details()->create($detail);
    //         }

    //         DB::commit();
    //         return response()->json($transaction->load('details'), 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function store(Request $request)
    {
        $request->validate([
            'noref' => 'required|string',
            'type' => 'required|in:INCOME,EXPENSE',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'details' => 'required|array',
            'details.*.coa_from' => 'nullable|integer',
            'details.*.coa_to' => 'nullable|integer',
            'details.*.debit' => 'numeric|min:0',
            'details.*.credit' => 'numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // 1. Ambil saldo awal dari laporan terakhir
            $lastReport = Report::orderBy('created_at', 'desc')->first();
            $currentBalance = $lastReport ? $lastReport->balance : 0;  // Jika tidak ada laporan sebelumnya, gunakan saldo default 5.000.000

            // 2. Simpan transaksi utama
            $transaction = Transaction::create($request->only(['noref', 'type', 'date', 'notes']));

            // 3. Simpan detail transaksi
            foreach ($request->details as $detail) {
                $transaction->details()->create([
                    'transaction_id' => $transaction->id,
                    'coa_from' => $detail['coa_from'],
                    'coa_to' => $detail['coa_to'],
                    'debit' => $detail['debit'],
                    'credit' => $detail['credit']
                ]);
            }

            // 4. Buat laporan dan update saldo berdasarkan jenis transaksi
            $saldo = $currentBalance;  // Mulai dengan saldo awal

            $coaId = isset($detail['coa_to']) && !empty($detail['coa_to']) ? $detail['coa_to'] : $detail['coa_from'];

            // Menggunakan query builder untuk mengambil nama COA
            $coaName = DB::table('coas')->where('id', $coaId)->value('name');

            foreach ($request->details as $detail) {
                if ($transaction->type == 'INCOME') {
                    // INCOME: saldo bertambah dari debit
                    $saldo += $detail['debit'];
                } elseif ($transaction->type == 'EXPENSE') {
                    // EXPENSE: saldo berkurang dari credit
                    $saldo -= $detail['credit'];
                }

                // Buat laporan untuk transaksi
                Report::create([
                    'transaction_id' => $transaction->id,
                    'coa_name' => $coaName,
                    'note' => $transaction->notes,
                    'debit' => $detail['debit'],
                    'credit' => $detail['credit'],
                    'balance' => $saldo, // Saldo yang diperbarui
                ]);
            }

            // 5. Simpan saldo yang baru setelah transaksi selesai (jika diperlukan)
            // Misalnya, update saldo di model Balance
            // Balance::updateOrCreate(['id' => 1], ['balance' => $saldo]);

            DB::commit(); // Commit jika berhasil
            return response()->json($transaction->load('details'), 201); // Return data transaksi beserta detail
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback jika ada error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // private function getCoaName($detail)
    // {
    //     // Jika $detail adalah objek
    //     if (is_object($detail)) {
    //         // Cek apakah ada coaFrom dan ambil namanya dari relasi
    //         if ($detail->coaFrom) {
    //             return $detail->coaFrom->name ?? '-';  // Mengambil nama COA dari coaFrom
    //         }

    //         // Jika tidak ada coaFrom, cek coaTo dan ambil namanya dari relasi
    //         if ($detail->coaTo) {
    //             return $detail->coaTo->name ?? '-';  // Mengambil nama COA dari coaTo
    //         }
    //     }

    //     // Jika $detail adalah array, pastikan array memiliki akses COA dengan ID
    //     elseif (is_array($detail)) {
    //         if (isset($detail['coa_from']) && isset($detail['coa_from']['id'])) {
    //             // Ambil nama COA dari id coa_from
    //             $coa = Coas::find($detail['coa_from']['id']);
    //             return $coa ? $coa->name : '-';
    //         }

    //         if (isset($detail['coa_to']) && isset($detail['coa_to']['id'])) {
    //             // Ambil nama COA dari id coa_to
    //             $coa = Coas::find($detail['coa_to']['id']);
    //             return $coa ? $coa->name : '-';
    //         }
    //     }

    //     // Jika tidak ditemukan COA, kembalikan tanda '-'
    //     return '-';
    // }



    // Get a specific transaction
    public function show($id)
    {
        $transaction = Transaction::with('details')->find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }
        return response()->json($transaction);
    }

    // Update a transaction
    public function update(Request $request, $id)
    {
        $request->validate([
            'noref' => 'required|string',
            'type' => 'required|in:INCOME,EXPENSE',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'details' => 'required|array',
            'details.*.coa_from' => 'nullable|integer',
            'details.*.coa_to' => 'nullable|integer',
            'details.*.debit' => 'numeric|min:0',
            'details.*.credit' => 'numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->update($request->only(['noref', 'type', 'date', 'notes']));

            $transaction->details()->delete();
            foreach ($request->details as $detail) {
                $transaction->details()->create($detail);
            }

            DB::commit();
            return response()->json($transaction->load('details'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Delete a transaction
    public function destroy($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        DB::beginTransaction();
        try {
            $transaction->details()->delete();
            $transaction->delete();
            DB::commit();
            return response()->json(['message' => 'Transaction deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

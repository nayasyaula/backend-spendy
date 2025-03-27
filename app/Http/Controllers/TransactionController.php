<?php

namespace App\Http\Controllers;

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
            $transaction = Transaction::create($request->only(['noref', 'type', 'date', 'notes']));

            foreach ($request->details as $detail) {
                $transaction->details()->create($detail);
            }

            DB::commit();
            return response()->json($transaction->load('details'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

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

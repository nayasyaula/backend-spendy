<?php

namespace App\Http\Controllers;

use App\Models\Coas;
use Illuminate\Http\Request;

class CoasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coas = Coas::all();
        return response()->json($coas);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'base' => 'required|in:debit,credit',
        ]);

        $coa = Coas::create($request->all());

        return response()->json(['message' => 'COA created successfully', 'data' => $coa], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $coa = Coas::findOrFail($id);
        return response()->json($coa);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Coas $coas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'base' => 'sometimes|required|in:debit,credit',
        ]);

        $coa = Coas::findOrFail($id);
        $coa->update($request->all());

        return response()->json(['message' => 'COA updated successfully', 'data' => $coa]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $coa = Coas::findOrFail($id);
        $coa->delete();

        return response()->json(['message' => 'COA deleted successfully']);
    }
}

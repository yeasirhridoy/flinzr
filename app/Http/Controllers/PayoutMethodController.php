<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\PayoutMethod;
use Illuminate\Http\Request;

class PayoutMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $rules = [
            'country_code' => 'required|exists:countries,code',
            'full_name' => 'required|string',
            'id_no' => 'required|string',
            'phone' => 'required|string',
        ];

        $request->validate($rules);

        $payoutMethod = PayoutMethod::query()->updateOrCreate([
            'user_id' => auth()->id(),
        ], [
            'country_id' => Country::where('code', $request->country_code)->first()->id,
            'full_name' => $request->full_name,
            'id_no' => $request->id_no,
            'phone' => $request->phone,
        ]);

        return response()->json($payoutMethod);
    }

    /**
     * Display the specified resource.
     */
    public function show(PayoutMethod $payoutMethod)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PayoutMethod $payoutMethod)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayoutMethod $payoutMethod)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayoutMethod $payoutMethod)
    {
        //
    }
}

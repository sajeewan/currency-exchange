<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\AuditTrails;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;

class ExchangeRateController extends Controller
{
    public function getUsdRates(Request $request)
    {
        try {
            $date = $request->query('date', Carbon::today()->toDateString());
            $startDate = Carbon::parse($date)->subDays(6)->toDateString();
            $endDate = $date;
    
            $rates = ExchangeRate::where('base_currency', 'USD')
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->get();
    
            $average = $rates->avg('rate');
    
            return response()->json([
                'rates' => $rates,
                'average' => round($average, 4),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve USD exchange rates',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'base_currency' => 'nullable|string|size:3',
                'date' => 'nullable|date',
            ]);
    
            $baseCurrency = $request->query('base_currency', 'USD');
            $date = $request->query('date', Carbon::today()->toDateString());
            $startDate = Carbon::parse($date)->subDays(6)->toDateString();
            $endDate = $date;
    
            $rates = ExchangeRate::where('base_currency', $baseCurrency)
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->get();
    
            $average = $rates->avg('rate');
    
            AuditTrails::create([
                'user_id' => auth()->id(),
                'action' => 'view',
                'resource' => 'exchange_rates',
                'details' => "Viewed rates for $baseCurrency from $startDate to $endDate",
            ]);
    
            return response()->json([
                'rates' => $rates,
                'average' => round($average, 4),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve exchange rates',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'base_currency' => 'required|string|size:3',
                'rate' => 'required|numeric|min:0',
                'date' => 'required|date',
            ]);

            $exchangeRate = ExchangeRate::updateOrCreate(
                [
                    'base_currency' => $request->base_currency,
                    'date' => $request->date,
                ],
                [
                    'rate' => $request->rate,
                ]
            );

            AuditTrails::create([
                'user_id' => auth()->id(),
                'action' => 'store',
                'resource' => 'exchange_rate',
                'details' => "Stored rate for {$request->base_currency}/" . " on {$request->date}",
            ]);

            return response()->json($exchangeRate, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to store exchange rate',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $rate = ExchangeRate::findOrFail($id);

            AuditTrails::create([
                'user_id' => auth()->id(),
                'action' => 'view',
                'resource' => 'exchange_rate',
                'details' => "Viewed rate ID $id for {$rate->base_currency} on {$rate->date}",
            ]);

            return response()->json($rate);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Exchange rate not found',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'base_currency' => 'required|string|size:3',
                'rate' => 'required|numeric|min:0',
                'date' => 'required|date',
            ]);

            $exchangeRate = ExchangeRate::findOrFail($id);

            

            $exchangeRate->update([
                'base_currency' => $request->base_currency,
                'rate' => $request->rate,
                'date' => $request->date,
            ]);

            AuditTrails::create([
                'user_id' => auth()->id(),
                'action' => 'update',
                'resource' => 'exchange_rate',
                'details' => "Updated rate ID $id for {$request->base_currency} on {$request->date}",
            ]);

            return response()->json($exchangeRate, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Exchange rate not found',
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $exchangeRate = ExchangeRate::findOrFail($id);
            $details = "Deleted rate ID $id for {$exchangeRate->base_currency}/" . ($exchangeRate->target_currency ?? 'N/A') . " on {$exchangeRate->date}";
            $exchangeRate->delete();

            AuditTrails::create([
                'user_id' => auth()->id(),
                'action' => 'delete',
                'resource' => 'exchange_rate',
                'details' => $details,
            ]);

            return response()->json([
                'message' => 'Exchange rate deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Exchange rate not found',
            ], 404);
        }
    }

    public function getAuditTrails()
    {
        $trails = AuditTrails::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($trails);
    }
}

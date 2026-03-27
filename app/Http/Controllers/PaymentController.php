<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Payment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Club $club)
    {
        $payments = Payment::where('club_id', $club->id)->get();

        return Inertia::render('Payment/Index', compact('club', 'payments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:999999',
            'club_id' => 'required|integer|exists:clubs,id',
            'note' => 'nullable|string|max:500',
        ]);

        // SEGURIDAD: Usar only() para prevenir mass assignment
        Payment::create($request->only(['club_id', 'amount', 'note']));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:999999',
            'note' => 'nullable|string|max:500',
        ]);

        // SEGURIDAD: Usar only() para prevenir mass assignment
        $payment->update($request->only(['amount', 'note']));
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
    }
}

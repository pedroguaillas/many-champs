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
            'amount' => 'required|numeric'
        ]);

        Payment::create($request->all());
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'amount' => 'required|numeric'
        ]);

        $payment->update($request->all());
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
    }
}

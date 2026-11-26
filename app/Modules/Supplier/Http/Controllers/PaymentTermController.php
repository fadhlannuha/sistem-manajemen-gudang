<?php

declare(strict_types=1);

namespace App\Modules\Supplier\Http\Controllers;

use App\Modules\Supplier\Http\Requests\StorePaymentTermRequest;
use App\Modules\Supplier\Http\Requests\UpdatePaymentTermRequest;
use App\Modules\Supplier\Models\PaymentTerm;
use App\Modules\Supplier\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentTermController extends Controller
{
    public function index(): View
    {
        $paymentTerms = PaymentTerm::query()
            ->withCount('suppliers')
            ->withCount(['suppliers as all_suppliers_count' => fn ($query) => $query->withTrashed()])
            ->orderBy('days')
            ->orderBy('name')
            ->paginate(15);

        return view('supplier::payment_terms.index', [
            'paymentTerms' => $paymentTerms,
        ]);
    }

    public function create(): View
    {
        return view('supplier::payment_terms.create');
    }

    public function store(StorePaymentTermRequest $request): RedirectResponse
    {
        PaymentTerm::query()->create($request->validated());

        return redirect()
            ->route('payment-terms.index')
            ->with('status', 'Termin pembayaran berhasil ditambahkan.');
    }

    public function edit(PaymentTerm $paymentTerm): View
    {
        return view('supplier::payment_terms.edit', [
            'paymentTerm' => $paymentTerm,
        ]);
    }

    public function update(UpdatePaymentTermRequest $request, PaymentTerm $paymentTerm): RedirectResponse
    {
        $paymentTerm->update($request->validated());

        return redirect()
            ->route('payment-terms.index')
            ->with('status', 'Termin pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentTerm $paymentTerm): RedirectResponse
    {
        $isUsed = Supplier::query()
            ->withTrashed()
            ->where('payment_term_id', $paymentTerm->id)
            ->exists();

        if ($isUsed) {
            throw ValidationException::withMessages([
                'delete' => 'Termin pembayaran masih digunakan oleh pemasok dan tidak dapat dihapus.',
            ]);
        }

        $paymentTerm->delete();

        return redirect()
            ->route('payment-terms.index')
            ->with('status', 'Termin pembayaran berhasil dihapus.');
    }
}

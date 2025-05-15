<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use Livewire\WithFileUploads;

class PaymentConfirmationPage extends Component
{
    use WithFileUploads;
    public $order;

    // form fields
    public $payment_proof;

    protected $rules = [
        'payment_proof' => 'required|image',
    ];

    protected $messages = [
        'payment_proof.required' => 'Upload bukti transfer',
        'payment_proof.image' => 'File harus berupa gambar',
        'payment_proof.max' => 'Ukuran file maksimal 2MB',
    ];

    public function mount($orderNumber)
    {
        $this->order = Order::where('order_number', $orderNumber)->firstOrFail();
    }

    public function updatedPaymentProof()
    {
        $this->validate([
            'payment_proof' => 'image|'
        ]);
    }

    public function submit()
    {
        $this->validate();

        try {
            // Upload image
            $imagePath = $this->payment_proof->store('payment-proofs', 'public');

            // Update order with payment proof
            $this->order->update([
                'payment_proof' => $imagePath,
            ]);

            $this->dispatch('showAlert', [
                'message' => 'Bukti pembayaran berhasil diunggah',
                'type' => 'success'
            ]);

            return redirect()->route('orders');

        } catch (\Exception $e) {
            $this->dispatch('showAlert', [
                'message' => $e->getMessage(),
                'type' => 'danger'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.payment-confirmation')
            ->layout('components.layouts.app', ['hideBottomNav' => true]);
    }
}
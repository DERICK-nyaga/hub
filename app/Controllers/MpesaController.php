<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\InternetPayment;
use App\Models\MpesaTransaction;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller{

    /**
     * Initiate M-Pesa STK Push
     */
    public function initiateMpesaPayment(Request $request, $id)
    {
        try {
            $request->validate([
                'phone_number' => 'required|string|size:9',
                'amount' => 'required|numeric|min:1'
            ]);

            $payment = InternetPayment::findOrFail($id);

            $phoneNumber = '254' . $request->phone_number;

            try {
                $mpesaService = new MpesaService();
                $response = $mpesaService->stkPush(
                    $phoneNumber,
                    $request->amount,
                    $payment->account_number,
                    'Internet Payment for ' . $payment->station->name
                );

                if ($response->ResponseCode == '0') {
                    // Store transaction reference
                    $transactionId = $response->CheckoutRequestID;

                    // Create a pending transaction record using Eloquent
                    $mpesaTransaction = MpesaTransaction::create([
                        'checkout_request_id' => $transactionId,
                        'payment_id' => $payment->id,
                        'phone_number' => $phoneNumber,
                        'amount' => $request->amount,
                        'status' => 'pending'
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'STK Push initiated successfully',
                        'transaction_id' => $transactionId
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $response->ResponseDescription ?? 'Failed to initiate payment'
                    ], 400);
                }
            } catch (\Exception $e) {
                Log::error('M-Pesa STK Push Error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initiate M-Pesa payment: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check M-Pesa transaction status
     */
    public function checkMpesaTransaction($transactionId)
    {
        try {
            $transaction = MpesaTransaction::where('checkout_request_id', $transactionId)
                ->with('payment') // Eager load the payment relationship
                ->first();

            if (!$transaction) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Transaction not found'
                ]);
            }

            if ($transaction->status === 'completed') {
                // Update the payment record using Eloquent
                $payment = $transaction->payment;
                if ($payment) {
                    $payment->update([
                        'status' => 'paid',
                        'payment_date' => $transaction->updated_at->format('Y-m-d'),
                        'payment_method' => 'M-Pesa',
                        'mpesa_receipt' => $transaction->mpesa_receipt,
                        'transaction_id' => $transaction->mpesa_receipt
                    ]);
                }

                return response()->json([
                    'status' => 'completed',
                    'transaction_id' => $transaction->mpesa_receipt
                ]);
            } elseif ($transaction->status === 'failed') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Transaction failed'
                ]);
            }

            return response()->json([
                'status' => 'pending'
            ]);

        } catch (\Exception $e) {
            Log::error('Check Transaction Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * M-Pesa Callback Handler
     */
    public function mpesaCallback(Request $request)
    {
        Log::info('M-Pesa Callback Received:', $request->all());

        try {
            $data = $request->Body->stkCallback;
            $checkoutRequestId = $data->CheckoutRequestID;

            // Find transaction using Eloquent
            $transaction = MpesaTransaction::where('checkout_request_id', $checkoutRequestId)
                ->with('payment')
                ->first();

            if (!$transaction) {
                Log::error('Transaction not found for CheckoutRequestID: ' . $checkoutRequestId);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Transaction not found']);
            }

            if ($data->ResultCode == 0) {
                // Payment successful
                $callbackData = $data->CallbackMetadata->Item;
                $mpesaReceipt = '';

                foreach ($callbackData as $item) {
                    if ($item->Name == 'MpesaReceiptNumber') {
                        $mpesaReceipt = $item->Value;
                        break;
                    }
                }

                // Update transaction using Eloquent
                $transaction->update([
                    'status' => 'completed',
                    'mpesa_receipt' => $mpesaReceipt
                ]);

                // Auto-update the payment using Eloquent
                $payment = $transaction->payment;
                if ($payment) {
                    $payment->update([
                        'status' => 'paid',
                        'payment_date' => now()->format('Y-m-d'),
                        'payment_method' => 'M-Pesa',
                        'mpesa_receipt' => $mpesaReceipt,
                        'transaction_id' => $mpesaReceipt
                    ]);

                    Log::info('Payment updated successfully', [
                        'payment_id' => $payment->id,
                        'mpesa_receipt' => $mpesaReceipt
                    ]);
                }

                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
            } else {
                // Payment failed
                $transaction->update([
                    'status' => 'failed'
                ]);

                Log::info('Transaction failed', [
                    'checkout_request_id' => $checkoutRequestId,
                    'result_code' => $data->ResultCode,
                    'result_desc' => $data->ResultDesc ?? 'No description'
                ]);

                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Payment failed']);
            }
        } catch (\Exception $e) {
            Log::error('M-Pesa Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Internal server error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Optional: Get transaction history for a payment
     */
    public function getPaymentTransactions($paymentId)
    {
        try {
            $payment = InternetPayment::findOrFail($paymentId);

            $transactions = $payment->mpesaTransactions()
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}

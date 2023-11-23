<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Nette\Utils\Json;

class PaymentController extends Controller
{
    public function index(Request $request, $bookingId)
    {
        $booking =  Booking::with(['item.brand', 'item.type'])->findOrFail($bookingId);

        return view('payment', [
            'booking' => $booking,
        ]);
    }


    public function update(Request $request, $bookingId)
    {
        // Load bookings data
        $booking = Booking::findOrFail($bookingId);

        // Set payment method
        $booking->payment_method = $request->payment_method;

        // Handle midtrans payment_method
        if($request->payment_method == 'midtrans'){
            \Midtrans\Config::$serverKey = config('services.midtrans.serverKey');

            \Midtrans\Config::$isProduction = config('services.midtrans.isProduction');

            \Midtrans\Config::$isSanitized = config('services.midtrans.isSanitized');

            \Midtrans\Config::$is3ds = config('services.midtrans.is3ds');

            // Get USD to IDR rate using from https://www.exchangerate-api.com/ using Guzzle
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', 'https://api.exchangerate-api.com/v4/latest/USD');
            $body = $response->getBody();
            $rate = json_decode($body)->rates->IDR;

            // Convert to IDR
            $totalPrice = $booking->total_price * $rate;

            // create midtrans params
            $midtransParams = [
                'transaction_details' => [
                    'order_id' => $booking->id,
                    'gross_amount' => (int) $totalPrice,
                ],
                'customer_details' => [
                    'first_name' => $booking->customer_name,
                    'email' => $booking->customer_email,
                ],
                'enabled_payments' => ['gopay', 'bank_transfer'],
            ];

             // Get Snap Payment Page URL
             $paymentUrl = \Midtrans\Snap::createTransaction($midtransParams)->redirect_url;

             // Save payment URL to booking
             $booking->payment_url = $paymentUrl;
             $booking->save();
 
             // Redirect to Snap Payment Page
             return redirect($paymentUrl);
         }
 
        return redirect()->route('front.index');
    }

    public function success(Request $request)
    {
        return view('success');
    }
}

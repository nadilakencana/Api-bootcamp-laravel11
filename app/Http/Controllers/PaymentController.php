<?php

namespace App\Http\Controllers;
use App\Models\Payment_Methode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{

    // menampilkan data payment
    public function getPayment(){
        try{
            $payment = Payment_Methode::all();

            return response()->json([
                'message' => 'Data Brhasil Di ambil',
                'data' => $payment
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Data Error',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function Createpayment(Request $request){
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try{
            $payment = Payment_Methode::create($request->all());
            return response()->json([
                'message' => 'Payment created successfully',
                'data' => $payment
            ], 200);
        }catch(\Exception $e){
            
            return response()->json(['message' => 'Failed to create payment','data' => $e->getMessage()], 500);
        }
    }

    public function upadatePayment(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try{
            $payment = Payment_Methode::find($id);
            $payment->update($request->all());
            return response()->json([
                'message' => 'Payment updated successfully',
                'data' => $payment
            ], 200);
        }catch(\Exception $e){

            return response()->json(['message' => 'Failed to update payment','data' => $e->getMessage()], 500);
        }
    }

    public function DeletePayment($id){
        try{
            $payment = Payment_Methode::find($id);
            $payment->delete();
            return response()->json(['message' => 'Payment deleted successfully'], 200);
        }catch(\Exception $e){
            return response()->json(['message' => 'Failed to delete payment','data' => $e->getMessage()], 500);
        }
    }
}

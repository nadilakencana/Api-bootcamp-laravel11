<?php

namespace App\Http\Controllers;
use App\Models\OrderItem;
use App\Models\Orders;
use App\Models\Payment_Methode;
use App\Models\ProductLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function code_order($length=5){
        $str ='';
        $charecters=array_merge(range('A','Z'),range('a','z'));
        $max = count($charecters)-1;
        for($i = 0; $i < $length; $i++){
            $rand = mt_rand(0, $max);
            $str .=$charecters[$rand];
        }
        return $str;
    }

    public function order(Request $request){

        $validator = Validator::make($request->all(), [

            'cart' => 'required|array',
            'cart.*.id_product' => 'required',
            'cart.*.quantity' => 'required|numeric',
            'cart.*.price' => 'required',


        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try{
            DB::beginTransaction();

            $order = new Orders();
            $order->status_order = 'Open Order';
            $order->code_order = $this->code_order();
            $order->sub_amount = 0;
            $order->amount = 0;
            if (Auth::user()) {
                $order->created_by = Auth::user()->id;
            }

            $order->save();

            if($order){
                foreach($request->cart as $item){
                    $order_items =  OrderItem::create([
                        'id_order' => $order->id,
                        'id_product' => $item['id_product'],
                        'qty' => $item['quantity'],
                        'price' => $item['price'],
                        'amount' => $item['quantity'] * $item['price'],
                    ]);
                    if (Auth::user()) {
                        $order_items['created_by'] = Auth::user()->id;
                    }

                    $order_items->save();


                        $product_logs = ProductLog::create([
                            'id_order' => $order->id,
                            'id_product' => $order_items->id_product,
                            'id_order_item' =>  $order_items->id,
                        ]);

                        $product_logs->save();

                }
            }


            $order_items_sum = OrderItem::where('id_order', $order->id)->sum('amount');

            $order = Orders::find($order->id);
            $order->sub_amount = $order_items_sum;
            $order->amount = $order_items_sum;

            if($request->payment){
                $order->id_payment = $request->payment;
                $order->status_order = 'Paid';

            }

            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'data' =>[
                    'order' => $order,
                    'detail_order' => $order_items,
                    'product_logs' => $product_logs
                ]
            ], 201);
        }catch(\Exception $e){
            return response()->json(['message' => 'Failed to create order','data' => $e->getMessage()], 500);
        }
    }

    public function Detailorder($code){
        try{
            $order = Orders::where('code_order', $code)->first();
            $order_items = OrderItem::where('id_order', $order->id)->get();

            return response()->json([
                'message' => 'Detail Order fetched successfully',
                'data' => [
                    'order' => $order,
                    'detail_order' => $order_items
                ]

            ], 200);
        }catch(\Exception $e){

            return response()->json(['message' => 'Failed to fetch order','data' => $e->getMessage()], 500);
        }
    }


     public function payment_order(Request $request, $code){

        try{
            $order = Orders::where('code_order', $code)->first();
            $order_items = OrderItem::where('id_order', $order->id)->get();

            $order->update([
                'status_order' => 'Paid',
                'id_payment' => $request->payment,
            ]);

            return response()->json([
                'message' => 'Payment Order successfully',
                'data' => [
                    'order' => $order,
                    'detail_order' => $order_items
                ]

            ], 200);


        }catch(\Exception $e){
            return response()->json(['message' => 'Failed to fetch Payment Order','data' => $e->getMessage()], 500);
        }

    }

    public function modify_order(Request $request){
        //
        try{
            DB::beginTransaction();

            $order = Orders::where('code_order', $request->get('code_order'))->first();

            $order_items = OrderItem::where('id_order', $order->id)->get();

            if($order->status_order !== 'Paid'){
                foreach ($request->items as $item) {
                    if (!empty($item['id'])) {

                        $orderItem = OrderItem::findOrFail($item['id']);

                        $orderItem->update([
                            'id_product' => $item['id_product'],
                            'qty' => $item['quantity'],
                            'price' => $item['price'],
                            'amount' => $item['quantity'] * $item['price'],
                        ]);
                        $orderItem->save();


                        $product_logs = ProductLog::where('id_order_item', $orderItem->id)->first();

                        if($product_logs){
                            $product_logs->update([
                                'id_product' => $orderItem->id_product,
                            ]);
                            $product_logs->save();
                        }
                    } else {
                        // Create new item
                        $orderItem = OrderItem::create([
                            'id_order' => $order->id,
                            'id_product' => $item['id_product'],
                            'qty' => $item['quantity'],
                            'price' => $item['price'],
                            'amount' => $item['quantity'] * $item['price'],
                            'created_by' => Auth::user()->id
                        ]);

                        $orderItem->save();

                        foreach ($item['products_logs'] as $log) {
                            ProductLog::create([
                                'id_order' => $order->id,
                                'id_product' => $log['id_product'],
                                'id_order_item' => $orderItem->id,
                            ]);

                        }
                    }
                }

                // Update order totals
                $order_items_sum = OrderItem::where('id_order', $order->id)->sum('amount');

                $order->sub_amount = $order_items_sum;
                $order->amount = $order->sub_amount;
                $order->created_by = Auth::user()->id ;
                $order->save();

            }else{
                return response()->json(['message' => 'Order already paid'], 500);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order items added/updated successfully',
                'data' => [
                    'order' => $order,
                    'order_items' =>  $order_items,
                    'product_logs' => ProductLog::where('id_order', $order->id)->get(),
                ]

            ], 200);
        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['message' => 'Failed to fetch Modify Order','data' => $e->getMessage()], 500);
        }
    }

    public function modify_delete_item(Request $request){
        try{

            $order = Orders::where('code_order', $request->get('order_code'))->first();

            if($order->status_order !== 'Paid'){

                $order_item = OrderItem::where('id', $request->get('id_items_order'))->where('id_order', $order->id)->first();
                $order_item->delete();

                $sum_order_item = OrderItem::where('id_order', $order->id)->sum('amount');
                $order->sub_amount = $sum_order_item;
                $order->amount = $order->sub_amount;
                $order->save();

                return response()->json([
                    'message' => 'Order item deleted successfully',
                    'data' => [
                         'order' => $order,
                        'detail_order' => OrderItem::where('id_order', $order->id)->get()
                    ]

                ], 200);
            }else{
                return response()->json(['message' => 'Order already paid'], 500);
            }

        }catch(\Exception $e){
            return response()->json(['message' => 'Failed to fetch Modify Order','data' => $e->getMessage()], 500);
        }
    }

    // report Order Sales

    public function dailyReports(Request $request){
        try{

            $startDate = Carbon::parse($request->query('startDate'))->startOfDay()->toDateTimeString();
            $endDate = Carbon::parse($request->query('endDate'))->endOfDay()->toDateTimeString();


            $orders = Orders::where('status_order', 'Paid')
            ->with('OrderItem')
            ->whereBetween('created_at', [$startDate, $endDate])->get();

            $totalAmount = Orders::where('status_order', 'Paid')->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
            $itm_sum = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('status_order', 'Paid');
            })->sum('qty');

            return response()->json([
                'message' => 'Daily reports fetched successfully',
                'data' =>[
                    'sum_amount' => $totalAmount,
                    'orders' => $orders,
                    'sum_item_sold' =>$itm_sum

                ]
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message'=> 'Faild to fetch daily reports',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function weeklyReports(Request $request)
    {
        try {

            $startDate = Carbon::parse($request->query('startDate'))->startOfWeek()->toDateTimeString();
            $endDate = Carbon::parse($request->query('endDate'))->endOfWeek()->toDateTimeString();


            $orders = Orders::where('status_order', 'Paid')
                ->with('OrderItem')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();


            $totalAmount = Orders::where('status_order', 'Paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            $itm_sum = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('status_order', 'Paid');
            })->sum('qty');

            return response()->json([
                'message' => 'Weekly reports fetched successfully',
                'data' => [
                    'sum_amount' => $totalAmount,
                    'orders' => $orders,
                    'sum_item_sold' => $itm_sum
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch weekly reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function monthlyReports(Request $request)
    {
        try {

            $startDate = Carbon::parse($request->query('startDate'))->startOfMonth()->toDateTimeString();
            $endDate = Carbon::parse($request->query('endDate'))->endOfMonth()->toDateTimeString();


            $orders = Orders::where('status_order', 'Paid')
                ->with('OrderItem')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();


            $totalAmount = Orders::where('status_order', 'Paid')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('amount');

            $itm_sum = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                ->where('status_order', 'Paid');
            })->sum('qty');

            return response()->json([
                'message' => 'Monthly reports fetched successfully',
                'data' => [
                    'sum_amount' => $totalAmount,
                    'orders' => $orders,
                    'sum_item_sold' => $itm_sum

                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch monthly reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

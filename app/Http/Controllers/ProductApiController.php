<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductApiController extends Controller
{
    public function quantity()
    {    

        if (!request()->number || request()->number == '' || request()->number == 'undefined' || request()->number == 'null' || request()->number == null) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Part Number is required to get quantity in perameter'
            ], 422);
        }

        
        $domain = env('FISHBOWL_DOMAIN');

        $response = Http::post($domain.'/api/login', [
            'appName' => env('FISHBOWL_APP_NAME'),
            'appDescription' => env('FISHBOWL_APP_DESCRIPTION'),
            'appId' => env('FISHBOWL_APP_ID'),
            'username' => env('FISHBOWL_USERNAME'),
            'password' => env('FISHBOWL_PASSWORD')
        ], [
            'Content-Type: application/json'
        ]);

        try {
                
            $token = $response->json()['token'];
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])->get($domain.'/api/parts/inventory?pageSize=100000&number='.request()->number);
            

            $data = $response->json();

            $inventory = null;

            foreach ($data['results'] as $product) {
                
                if ($product['partNumber'] == request()->number) {
                    $inventory  = $product['quantity'];
                    break;
                }
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->post($domain.'/api/logout');

            return response()->json([
                'status'   => 'Success',
                "quantity" => $inventory
            ]);


        } catch (\Throwable $th) {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->post($domain.'/api/logout');

            return response()->json([
                'status' => 'Error',
                'message' => $th->getMessage()
            ], 500);
        }
        
        
    }


    public function quantityAll()
    {    

        
        $domain = env('FISHBOWL_DOMAIN');

        $response = Http::post($domain.'/api/login', [
            'appName' => env('FISHBOWL_APP_NAME'),
            'appDescription' => env('FISHBOWL_APP_DESCRIPTION'),
            'appId' => env('FISHBOWL_APP_ID'),
            'username' => env('FISHBOWL_USERNAME'),
            'password' => env('FISHBOWL_PASSWORD')
        ], [
            'Content-Type: application/json'
        ]);
        

        try {
            $token = $response->json()['token'];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])->get($domain.'/api/parts/inventory?pageSize=100000');
            

            $data = $response->json();

            $allData = [];

            foreach ($data['results'] as $product) {
                
                $databseCheck = Products::where('partNumber', $product['partNumber'])->first();

                if ($databseCheck) {

                    if ($databseCheck->quantity != number_format($product['quantity'], 2, '.', '')) {
                        array_push($allData, $product);
                    }

                }else {

                    Products::create([
                        'partNumber'     => $product['partNumber'],
                        'quantity'        => $product['quantity'],
                        'partDescription' => $product['partDescription'],
                    ]);

                    array_push($allData, $product);
                }
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->post($domain.'/api/logout');

            return response()->json([
                'status'   => 'Success',
                "data" => $allData
            ],200);


        } catch (\Throwable $th) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->post($domain.'/api/logout');

            return response()->json([
                'status' => 'Error',
                'message' => $th->getMessage()
            ], 500);
        }
        
        
    }



    public function storeSales(Request $request)
    {
        $validate =  Validator::make($request->all(), [
                        'number' => ['required'],
                    ]);

        if ($validate->fails()) {

            return response()->json([
                'status' => 'Error',
                'message' => $validate->errors()
            ], 422);
        }            

        $domain = env('FISHBOWL_DOMAIN');

        try {
            $response = Http::post($domain.'/api/login', [
                'appName' => env('FISHBOWL_APP_NAME'),
                'appDescription' => env('FISHBOWL_APP_DESCRIPTION'),
                'appId' => env('FISHBOWL_APP_ID'),
                'username' => env('FISHBOWL_USERNAME'),
                'password' => env('FISHBOWL_PASSWORD')
            ], [
                'Content-Type: application/json'
            ]);
    
            $token = $response->json()['token'];
    
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])->post($domain.'/api/purchase-orders', [
    
                'number'             =>  $request->number,
                'status'             =>  $request->status,
                "class"              =>  $request->class,
                "carrier"            =>  $request->carrier,
                "fobPointName"       =>  $request->fobPointName,
                "paymentTerms"       =>  $request->paymentTerms,
                "shipTerms"          =>  $request->shipTerms,
                "vendor"             =>  $request->vendor,
                "vendorSoNumber"     =>  $request->vendorSoNumber,
                "customerSoNumber"   =>  $request->customerSoNumber,
                "buyer"              =>  $request->buyer,
                "deliverTo"          =>  $request->deliverTo,
                "revisionNumber"     =>  $request->revisionNumber,
                "dateCreated"        =>  $request->dateCreated,
                "dateConfirmed"      =>  $request->dateConfirmed,
                "dateScheduled"      =>  $request->dateScheduled,
                "taxRate"            =>  $request->taxRate,
                "totalIncludesTax"   =>  $request->totalIncludesTax,    
                "locationGroup"      =>  $request->locationGroup,   
                "note"               =>  $request->note,  
                "url"                =>  $request->url,  
                "currency"           =>  $request->currency,  
                "email"              =>  $request->email,  
                "phone"              =>  $request->phone,  
                "shipToAddress"      =>  $request->shipToAddress,  
                "remitToAddress"     =>  $request->remitToAddress,  
                "poItems"            =>  $request->poItems,  
                "customFields"       =>  $request->customFields,  

            ]);



            $data = $response->json();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->post($domain.'/api/logout');
    
            return response()->json([
                'status'   => 'Success',
                "data" => $data
            ]);



        } catch (\Throwable $th) {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->post($domain.'/api/logout');


            return response()->json([
                'status' => 'Error',
                'message' => $th->getMessage()
            ], 500);
        }

    }



    public function vendor()
    {    

        $domain = env('FISHBOWL_DOMAIN');

        $response = Http::post($domain.'/api/login', [
            'appName' => env('FISHBOWL_APP_NAME'),
            'appDescription' => env('FISHBOWL_APP_DESCRIPTION'),
            'appId' => env('FISHBOWL_APP_ID'),
            'username' => env('FISHBOWL_USERNAME'),
            'password' => env('FISHBOWL_PASSWORD')
        ], [
            'Content-Type: application/json'
        ]);
        
        try {
            
            $token = $response->json()['token'];
                
                

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ])->get($domain.'/api/vendors?pageSize=100000');
            

            $data = $response->json();

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->post($domain.'/api/logout');

            return response()->json([
                'status'   => 'Success',
                "data" => $data['results']
            ]);

        } catch (\Throwable $th) {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$token,
            ])->post($domain.'/api/logout');

            return response()->json([
                'status'   => 'Error',
                "message" => $th->getMessage()
            ]);
        }
        
        
    }
    
}

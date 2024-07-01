<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductApiController extends Controller
{
    public function quality()
    {    

        if (!request()->number || request()->number == '' || request()->number == 'undefined' || request()->number == 'null' || request()->number == null) {
            return response()->json([
                'status' => 'Error',
                'message' => 'Part Number is required to get quality in perameter'
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
        
        $status = $response->status();

        if ($status != 200) {
            return response()->json([
                'status' => 'Error',
                'message' => $response->json()['message']
            ], $status);
        }

        $token = $response->json()['token'];
        
        

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->get($domain.'/api/parts/inventory?pageSize=100000&number='.request()->number);
        

        $status = $response->status();

        if ($status != 200) {
            return response()->json([
                'status' => 'Error',
                'message' => $response->json()['message']
            ], $status);
        }

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
        
        
    }


    public function qualityAll()
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
        
        $status = $response->status();

        if ($status != 200) {
            return response()->json([
                'status' => 'Error',
                'message' => $response->json()['message']
            ], $status);
        }

        $token = $response->json()['token'];
        
        

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->get($domain.'/api/parts/inventory?pageSize=100000');
        

        $status = $response->status();

        if ($status != 200) {
            return response()->json([
                'status' => 'Error',
                'message' => $response->json()['message']
            ], $status);
        }

        $data = $response->json();

        $allData = [];

        foreach ($data['results'] as $product) {
            
            $databseCheck = Products::where('part_number', $product['partNumber'])->first();

            if ($databseCheck) {

                if ($databseCheck->quantity != $product['quantity']) {
                    array_push($allData, $product);
                }

            }else {

                Products::create([
                    'part_number'     => $product['partNumber'],
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
        
        
    }



    
}

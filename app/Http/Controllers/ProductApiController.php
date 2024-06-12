<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductApiController extends Controller
{
    public function quality()
    {
        $domain = '<YOUR SERVER>';

        $response = Http::post($domain.'/api/login', [
            'appName' => 'Ecom Gateway',
            'appDescription' => 'Ecom Gateway',
            'appId' => 1995,
            'username' => 'API',
            'password' => 'D@t@m@t1c01'
        ], [
            'Content-Type: application/json'
        ]);
        
        // You can then handle the response accordingly
        $status = $response->status();
        $token = $response->json()['token'];
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->get($domain.'/api/parts/inventory');
        
        // You can then handle the response accordingly
        $status = $response->status();
        $data = $response->json();
    }
}

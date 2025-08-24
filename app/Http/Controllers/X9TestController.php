<?php

namespace App\Http\Controllers;

use App\Services\X9Service;
use Illuminate\Http\Request;

class X9TestController extends Controller
{
    protected $x9Service;

    public function __construct(X9Service $x9Service)
    {
        $this->x9Service = $x9Service;
    }

    public function testConnection()
    {
        $response = $this->x9Service->testConnection();

        return response()->json([
            'status' => $response['status'],
            'message' => $response['message'],
            'data' => $response['data']
        ]);
    }

    public function testDemoPage()
    {
        return view('x9-test', [
            'title' => 'X9 Integration Test',
            'description' => 'This page tests the X9 integration functionality.'
        ]);
    }
}

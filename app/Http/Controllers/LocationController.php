<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;



class LocationController extends Controller
{
    public function getWarehouses()
    {
        $warehouses = Warehouse::pluck('name'); // Fetch unique warehouse names
        return response()->json($warehouses);
    }
}


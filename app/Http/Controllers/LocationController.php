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
        $warehouses = Warehouse::where('id', '!=', 1)->get(['id', 'name']);
        return response()->json($warehouses);
    }
}

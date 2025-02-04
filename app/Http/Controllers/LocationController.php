<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getWarehouses()
    {
        $warehouses = Warehouse::pluck('name'); // Fetch unique warehouse names
        return response()->json($warehouses);
    }
}

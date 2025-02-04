<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    // Show all warehouses
    public function index()
    {
        $warehouses = Warehouse::all();
        return view('management.warehouses', compact('warehouses'));
    }

    // Store a new warehouse
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone_number' => 'required|string|max:15',
    ]);

    // Create the new warehouse
    $warehouse = Warehouse::create($request->all());

    // Log the warehouse creation with user details
    $user = Auth::user(); // Get the currently authenticated user
    Log::channel('warehouse_creation')->info('Warehouse Added', [
        'user' => $user->first_name . ' ' . $user->last_name,
        'warehouse_name' => $warehouse->name,
        'address' => $warehouse->address,
        'phone_number' => $warehouse->phone_number,
    ]);

    return redirect()->route('warehouses.index')->with('success', 'Warehouse added successfully.');
}
}

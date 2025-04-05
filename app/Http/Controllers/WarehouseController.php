<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            // Common address rules: required string with maximum length
            'address' => 'required|string|max:255',
            // Phone number must be numeric and exactly 10 digits long
            'phone_number' => 'required|numeric|digits:10',
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

    // Show a single warehouse
    public function show($id)
    {
        $warehouse = Warehouse::findOrFail($id); // Fetch the warehouse or throw a 404

        $distributedCylinders = DB::table('agent_cylinders_distribution')
            ->where('warehouse', $warehouse->name) // Ensure warehouse matches
            ->get();

        return view('warehouses.show', compact('warehouse', 'distributedCylinders'));
    }

    // Update the warehouse
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        // Get the previous details for logging
        $previousData = [
            'name' => $warehouse->name,
            'address' => $warehouse->address,
            'phone_number' => $warehouse->phone_number,
        ];

        // Validate the request
        $request->validate([
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|numeric|digits:10',
        ]);

        // Update only the fields that are provided
        $warehouse->update($request->only(['name', 'address', 'phone_number']));

        // Log the update details with user info
        $user = Auth::user();
        Log::channel('warehouse_update')->info('Warehouse Updated', [
            'user' => $user->first_name . ' ' . $user->last_name,
            'previous_data' => $previousData,
            'new_data' => $request->only(['name', 'address', 'phone_number']),
        ]);

        return redirect()->route('warehouses.show', $warehouse->id)->with('success', 'Warehouse updated successfully.');
    }

    public function confirmAgentPickup(Request $request, $warehouseId)
    {
        $request->validate([
            'cylinders' => 'required|array',
            'cylinders.*' => 'exists:agent_cylinders_distribution,id',
        ]);

        // Mark selected cylinders as picked up (you can also move them to another table if needed)
        DB::table('agent_cylinders_distribution')
            ->whereIn('id', $request->cylinders)
            ->delete(); // Or update status instead of deleting

        return redirect()->route('warehouses.show', $warehouseId)
            ->with('success', 'Selected cylinders have been confirmed as picked up.');
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        if (Auth::user()->position !== 'Manager') {
            return redirect()->route('warehouses.index')->with('error', 'Unauthorized action.');
        }

        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }
}

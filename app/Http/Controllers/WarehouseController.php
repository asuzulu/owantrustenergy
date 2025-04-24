<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\State;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    // Show all warehouses
    public function index()
    {
        $states = State::all(); // Assuming your model is State

        $warehouses = Warehouse::all();
        return view('management.warehouses', compact('warehouses', 'states'));
    }

    // Store a new warehouse
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'phone_number' => 'required|numeric|digits:10',
        ]);

        // Convert state ID to state name
        $stateName = State::find($request->input('state'))?->name;

        if (!$stateName) {
            return redirect()->back()->withErrors(['state' => 'Invalid state selected.'])->withInput();
        }

        // Create the new warehouse
        $warehouse = Warehouse::create([
            'name' => $request->input('name'),
            'street' => $request->input('street'),
            'city' => $request->input('city'),
            'state' => $stateName, // Store the name instead of ID
            'phone_number' => $request->input('phone_number'),
        ]);

        // Log the warehouse creation with user details
        $user = Auth::user(); // Get the currently authenticated user
        Log::channel('warehouse_creation')->info('Warehouse Added', [
            'user' => $user->first_name . ' ' . $user->last_name,
            'warehouse_name' => $warehouse->name,
            'street' => $warehouse->street,
            'city' => $warehouse->city,
            'state' => $warehouse->state,
            'phone_number' => $warehouse->phone_number,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse added successfully.');
    }

    // Show a single warehouse
    public function show(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        // Agent cylinders (always paginated and ordered)
        $agentCylinders = DB::table('agent_cylinders_distribution')
            ->where('warehouse', $warehouse->name)
            ->orderBy('agent_name')
            ->orderByRaw("
            CASE cylinder_size
                WHEN 'Small' THEN 1
                WHEN 'Medium' THEN 2
                WHEN 'Large' THEN 3
                WHEN 'Extra Large' THEN 4
                ELSE 5
            END
        ")
            ->paginate(10, ['*'], 'agent_page');

        // Warehouse cylinders, with optional size filter
        $warehouseCylindersQuery = DB::table('cylinders')
            ->where('location', $warehouse->name);

        if ($request->filled('size')) {
            $warehouseCylindersQuery->where('size', $request->input('size'));
        }

        $warehouseCylinders = $warehouseCylindersQuery
            ->orderByRaw("
            CASE size
                WHEN 'Small' THEN 1
                WHEN 'Medium' THEN 2
                WHEN 'Large' THEN 3
                WHEN 'Extra Large' THEN 4
                ELSE 5
            END
        ")
            ->paginate(10, ['*'], 'cylinder_page');

        // AJAX request handling
        if ($request->ajax()) {
            $table = $request->input('table');

            if ($table === 'agent') {
                return view('partials.dashboard.agent-distribution-table', compact('agentCylinders'))->render();
            }

            if ($table === 'warehouse') {
                return response()->json([
                    'html' => view('partials.dashboard.warehouse-cylinders-size-table', compact('warehouseCylinders'))->render()
                ]);
            }
        }

        // Full page load
        return view('warehouses.show', compact('warehouse', 'agentCylinders', 'warehouseCylinders'));
    }

    // Update a warehouse
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'phone_number' => 'required|digits:10',
        ]);

        $stateName = State::find($request->input('state'))?->name ?? $request->input('state');

        $warehouse->update([
            'name' => $request->input('name'),
            'street' => $request->input('street'),
            'city' => $request->input('city'),
            'state' => $stateName,
            'phone_number' => $request->input('phone_number'),
        ]);

        Log::channel('warehouse_update')->info('Warehouse Updated', [
            'user' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
            'warehouse_id' => $warehouse->id,
            'new_data' => $request->only(['name', 'street', 'city', 'state', 'phone_number']),
        ]);

        $message = 'Warehouse updated successfully.';

        if ($request->ajax()) {
            return response()->json(
                array_merge((array) $warehouse->only(['name', 'street', 'city', 'state', 'phone_number']), ['message' => $message])
            );
        }

        return redirect()->route('warehouses.show', $warehouse->id)->with('success', $message);
    }

    // Delete a warehouse
    public function destroy(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        if (Auth::user()->position !== 'Manager') {
            $msg = 'Unauthorized action.';
            if ($request->ajax()) {
                return response()->json(['message' => $msg], 403);
            }
            return redirect()->route('warehouses.index')->with('error', $msg);
        }

        $warehouse->delete();
        $message = 'Warehouse deleted successfully.';

        if ($request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('warehouses.index')->with('success', $message);
    }

    // Confirm agent pickup
    public function confirmAgentPickup(Request $request, $warehouseId)
    {
        $request->validate([
            'cylinders' => 'required|array',
            'cylinders.*' => 'exists:agent_cylinders_distribution,id',
        ]);

        DB::table('agent_cylinders_distribution')
            ->whereIn('id', $request->cylinders)
            ->delete(); // Or update status instead of deleting

        return redirect()->route('warehouses.show', $warehouseId)
            ->with('success', 'Selected cylinders have been confirmed as picked up.');
    }
}

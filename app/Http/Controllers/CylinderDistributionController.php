<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cylinder;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CylinderDistributionController extends Controller
{
    public function distribute(Request $request, $id)
    {
        // Find the agent user by id
        $agent = User::findOrFail($id);
        // Validate that the user is an agent
        if ($agent->position !== 'Agent') {
            return redirect()->back()->with('error', 'Invalid user position for distribution.');
        }
        // The distribution data will be an array like:
        // distribution[cylinder_size][warehouse_id] = number of cylinders to assign
        $distributionData = $request->input('distribution', []);
        DB::beginTransaction();
        try {
            $totalDistributed = 0;
            // Loop through each cylinder size
            foreach ($distributionData as $size => $warehouses) {
                // For each warehouse in that size
                foreach ($warehouses as $warehouseId => $amount) {
                    $amount = (int)$amount;
                    if ($amount > 0) {
                        // Retrieve the warehouse
                        $warehouse = Warehouse::find($warehouseId);
                        if (!$warehouse) {
                            continue;
                        }
                        // Count available cylinders of this size in the warehouse
                        $available = Cylinder::where('size', $size)
                            ->where('location', $warehouse->name)
                            ->whereNull('user_id')
                            ->count();
                        // Check if the amount requested is less than or equal to available
                        if ($amount > $available) {
                            DB::rollBack();
                            return redirect()->back()->with('error', 'Requested amount exceeds available cylinders in ' . $warehouse->name . ' for size ' . $size);
                        }
                        // Update cylinders: assign the first $amount available cylinders to the agent
                        $cylinders = Cylinder::where('size', $size)
                            ->where('location', $warehouse->name)
                            ->whereNull('user_id')
                            ->limit($amount)
                            ->get();
                        foreach ($cylinders as $cylinder) {
                            $cylinder->user_id = $agent->id;
                            $cylinder->save();
                        }
                        $totalDistributed += $amount;
                    }
                }
            }
            DB::commit();
            return redirect()->back()->with('success', 'Successfully distributed a total of ' . $totalDistributed . ' cylinders.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Distribution failed: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cylinder;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CylinderDistributionController extends Controller
{
    /**
     * Handle the distribution of cylinders to an agent.
     */
    public function distribute(Request $request, $id)
    {
        Log::info("Distribution process started for agent ID: {$id}");
        // Find the agent user by id
        $agent = User::findOrFail($id);

        // Validate that the user is an agent
        if ($agent->position !== 'Agent') {
            Log::error("Distribution error: User ID {$id} is not an Agent.");
            return redirect()->back()->with('error', 'Invalid user position for distribution.');
        }

        $distributionData = $request->input('distribution', []);

        // Mapping for cylinder weights based on size
        $weightMapping = [
            'small'       => '3kg',
            'medium'      => '5kg',
            'large'       => '12kg',
            'extra large' => '25kg'
        ];

        // Generate a 7-character alphanumeric passcode (upper-case)
        $passcode = strtoupper(Str::random(7));
        Log::info("Generated passcode: {$passcode}");

        DB::beginTransaction();
        try {
            $totalDistributed = 0;
            Log::info("Starting distribution loop", ['distributionData' => $distributionData]);

            foreach ($distributionData as $size => $warehouses) {
                foreach ($warehouses as $warehouseId => $amount) {
                    $amount = (int)$amount;

                    if ($amount > 0) {
                        // Get warehouse
                        $warehouse = Warehouse::find($warehouseId);
                        if (!$warehouse) {
                            Log::warning("Warehouse ID {$warehouseId} not found.");
                            continue;
                        }

                        // Count cylinders in that warehouse by size using case-insensitive matching
                        $available = Cylinder::whereRaw('LOWER(size) = ?', [strtolower($size)])
                            ->whereRaw('LOWER(location) = ?', [strtolower(trim($warehouse->name))])
                            ->count();

                        Log::info("Warehouse {$warehouse->name} has {$available} cylinders of size {$size}");

                        // Check if enough cylinders are in that warehouse
                        if ($amount > $available) {
                            DB::rollBack();
                            Log::error("Not enough cylinders in {$warehouse->name} for size {$size}. Requested: {$amount}, available: {$available}");
                            return redirect()->back()->with('error', 'Not enough cylinders in ' . $warehouse->name . ' for size ' . $size);
                        }

                        // Get and assign the first $amount cylinders from that warehouse using case-insensitive matching
                        $cylinders = Cylinder::whereRaw('LOWER(size) = ?', [strtolower($size)])
                            ->whereRaw('LOWER(location) = ?', [strtolower(trim($warehouse->name))])
                            ->limit($amount)
                            ->get();

                        foreach ($cylinders as $cylinder) {
                            // Update cylinder: assign to agent and update location
                            $cylinder->user_id = $agent->id;
                            $cylinder->location = $agent->first_name . ' ' . $agent->last_name;
                            $cylinder->save();
                            Log::info("Cylinder ID {$cylinder->id} assigned to agent {$agent->id}");

                            // Insert into agent_cylinders_distribution table with passcode
                            DB::table('agent_cylinders_distribution')->insert([
                                'agent_name'      => $agent->first_name . ' ' . $agent->last_name,
                                'agent_id'        => $agent->id,
                                'cylinder_id'     => $cylinder->id,
                                'cylinder_size'   => $cylinder->size,
                                'cylinder_weight' => isset($weightMapping[$size]) ? $weightMapping[$size] : '',
                                'warehouse'       => $warehouse->name,
                                'passcode'        => $passcode,
                                'pick_up_date'    => null,
                                'created_at'      => now(),
                                'updated_at'      => now()
                            ]);
                            Log::info("Record inserted in agent_cylinders_distribution for cylinder ID {$cylinder->id}");
                        }

                        $totalDistributed += $amount;
                    }
                }
            }

            DB::commit();
            Log::info("Distribution process completed successfully. Total distributed: {$totalDistributed}");
            return redirect()->back()->with('success', 'Successfully distributed ' . $totalDistributed . ' cylinders. Passcode: ' . $passcode);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Distribution failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Distribution failed: ' . $e->getMessage());
        }
    }

    /**
     * Get warehouse cylinder data for the modal.
     */
    public function warehouseData()
    {
        $warehouses = Warehouse::all();
        $cylinderData = [];

        foreach ($warehouses as $warehouse) {
            // Count cylinders in each warehouse (regardless of user_id)
            $cylinders = Cylinder::where('location', $warehouse->name)
                ->selectRaw('size, count(*) as total')
                ->groupBy('size')
                ->pluck('total', 'size');

            $cylinderData[$warehouse->id] = [
                'name'      => $warehouse->name,
                'cylinders' => $cylinders,
            ];
        }

        return response()->json($cylinderData);
    }
}

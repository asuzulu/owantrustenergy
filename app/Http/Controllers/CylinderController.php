<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cylinder;
use App\Models\User;
use App\Models\Pickup;
use App\Models\Delivery;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CylinderController extends Controller
{
    public function index($userId = null)
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            abort(400, 'User ID is required.');
        }

        $user = User::findOrFail($userId);

        // Build the query with filtering options
        $query = Cylinder::query();

        if ($user->position === 'Customer') {
            $query->where('user_id', $user->id);
        }

        // Apply warehouse filter if set
        if ($warehouse = request('warehouse')) {
            $query->where('location', $warehouse);
        }

        // Apply size filter if set
        if ($size = request('size')) {
            $query->where('size', $size);
        }

        // Paginate the results
        $cylinders = $query->paginate(10);

        return ($user->position === 'Customer')
            ? view('dashboard.cylinder', compact('cylinders', 'user'))
            : view('management.home', [
                'cylinders' => $cylinders,
                'warehouses' => Warehouse::all(),
            ]);
    }

    public function show($id)
    {
        // Find the cylinder by its (possibly zero-padded) string ID
        $cylinder   = Cylinder::findOrFail($id);

        // All warehouses (for your assign modal, etc.)
        $warehouses = DB::table('warehouses')->get();

        // Fetch at most one matching delivery record
        $delivery = Delivery::where('cylinder', $id)->first();

        // Fetch at most one matching pickup record
        $pickup   = Pickup::where('cylinder', $id)->first();

        // Pass exactly these four variables into the view:
        return view('cylinders.show', compact(
            'cylinder',
            'warehouses',
            'delivery',
            'pickup'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'size' => 'required|string',
            'location' => 'required|string',
            'amount' => 'required|integer|min:1',
        ]);

        $amount = $validated['amount'];

        DB::beginTransaction();
        try {
            for ($i = 0; $i < $amount; $i++) {
                $cylinderId = $this->generateUniqueCylinderId();
                $qrFileName = "cylinder_{$cylinderId}.svg";
                $qrPath = "qrcodes/{$qrFileName}";

                // Ensure the directory exists
                Storage::makeDirectory('public/qrcodes');

                // Generate QR Code with URL to the cylinder details page
                $qrCode = QrCode::format('svg')
                    ->size(300)
                    ->generate(route('cylinders.show', $cylinderId));

                // Save the QR Code file
                Storage::put("public/{$qrPath}", $qrCode);

                // Create the cylinder
                Cylinder::create([
                    'id' => $cylinderId,
                    'size' => $validated['size'],
                    'location' => $validated['location'],
                    'allocated_date' => now(),
                    'user_id' => Auth::id(),
                    'qr_code_path' => $qrPath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Log each cylinder creation
                Log::channel('cylinder_creation')->info('Cylinder Added', [
                    'user' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                    'cylinder_id' => $cylinderId,
                    'size' => $validated['size'],
                    'location' => $validated['location']
                ]);
            }

            DB::commit();
            return redirect()->route('cylinders.index')->with('success', "{$amount} cylinder(s) added successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding cylinders: ' . $e->getMessage());
            return redirect()->route('cylinders.index')->with('error', 'Failed to add cylinders.');
        }
    }

    public function showUnassigned()
    {
        $cylinders = Cylinder::where('location', 'Unassigned')->paginate(15);
        $warehouses = Warehouse::all();
        return view('cylinders.unassigned', compact('cylinders', 'warehouses'));
    }

    public function assignWarehouses(Request $request)
    {
        $data = $request->input('warehouses', []);

        foreach ($data as $cylinderId => $warehouseName) {
            Cylinder::where('id', $cylinderId)->update([
                'location' => $warehouseName,
            ]);
        }

        return redirect()->back()->with('success', 'Cylinders assigned successfully.');
    }

    // Assign a cylinder to a user from User page
    public function assignCylinder(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'cylinder_id' => 'required|exists:cylinders,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $cylinder = Cylinder::findOrFail($request->cylinder_id);

        if ($user->position !== 'Customer') {
            return response()->json(['error' => 'Only customers can be assigned cylinders.'], 403);
        }

        $cylinder->user_id = $user->id;
        $cylinder->location = $user->first_name . ' ' . $user->last_name;
        $cylinder->save();

        return response()->json(['success' => 'Cylinder assigned successfully.']);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'cylinder_id' => 'required|exists:cylinders,id',
        ]);

        $cylinder = Cylinder::findOrFail($request->cylinder_id);
        $user = User::findOrFail($request->user_id);

        $cylinder->user_id = $user->id;
        $cylinder->location = $user->first_name . ' ' . $user->last_name;
        $cylinder->save();

        return response()->json(['success' => true, 'message' => 'Cylinder assigned successfully']);
    }

    private function generateUniqueCylinderId()
    {
        do {
            $id = str_pad(rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        } while (Cylinder::where('id', $id)->exists());

        return $id;
    }

    public function destroy($id)
    {
        $cylinder = Cylinder::findOrFail($id);

        if (Auth::user()->position !== 'Manager') {
            return redirect()->route('cylinders.index')->with('error', 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $cylinder->delete();
            DB::commit();
            return redirect()->route('cylinders.index')->with('success', 'Cylinder deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete cylinder.');
        }
    }
}

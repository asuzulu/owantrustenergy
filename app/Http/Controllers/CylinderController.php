<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cylinder;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CylinderController extends Controller
{
    public function index($userId = null)
    {
        Log::debug('Index Method triggered');
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            Log::debug('User ID not provided and no authenticated user found.');
            abort(400, 'User ID is required.');
        }

        try {
            $user = User::findOrFail($userId);
            Log::debug('User Data Retrieved: ', $user->toArray());
        } catch (\Exception $e) {
            Log::debug('Error retrieving User: ', ['error' => $e->getMessage()]);
            abort(404, 'User not found.');
        }

        try {
            $cylinders = Cylinder::when($user->position === 'Customer', function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->orderBy('allocated_date', 'desc')
            ->orderByRaw("CASE
                WHEN size = 'Extra Large' THEN 1
                WHEN size = 'Large' THEN 2
                WHEN size = 'Medium' THEN 3
                WHEN size = 'Small' THEN 4
                ELSE 5
            END")
            ->paginate(10);
        } catch (\Exception $e) {
            Log::debug('Error retrieving Cylinders: ', ['error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve cylinders.');
        }

        return ($user->position === 'Customer')
            ? view('dashboard.cylinder', compact('cylinders', 'user'))
            : view('management.home', compact('cylinders'));
    }

    public function show($id)
    {
        $cylinder = Cylinder::findOrFail($id);
        $deliveries = Delivery::where('cylinder', $cylinder->id)->get();
        $warehouses = DB::table('warehouses')->get();

        return view('cylinders.show', compact('cylinder', 'warehouses', 'deliveries' ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'size' => 'required|string',
            'location' => 'required|string',
        ]);

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

        // Begin database transaction
        DB::beginTransaction();
        try {
            $cylinder = Cylinder::create([
                'id' => $cylinderId,
                'size' => $validated['size'],
                'location' => $validated['location'],
                'allocated_date' => now(),
                'user_id' => Auth::id(),
                'qr_code_path' => $qrPath,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Log the creation of the new cylinder
            Log::channel('cylinder_creation')->info('Cylinder Added', [
                'user' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'cylinder_id' => $cylinderId,
                'size' => $validated['size'],
                'location' => $validated['location']
            ]);

            DB::commit();
            return redirect()->route('cylinders.index')->with('success', 'Cylinder added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding cylinder: ' . $e->getMessage());
            return redirect()->route('cylinders.index')->with('error', 'Failed to add cylinder.');
        }
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

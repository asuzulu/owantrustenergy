<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cylinder;
use App\Models\User;
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
            })->paginate(10);
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
        return view('cylinders.show', compact('cylinder'));
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

        // Generate QR Code using SVG format
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
            Log::channel('cylinder_creators')->info('Cylinder Added', [
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

    public function assignCylinder(Request $request, $userId)
    {
        Log::debug('Assign Cylinder Method Triggered');

        try {
            $user = User::findOrFail($userId);
        } catch (\Exception $e) {
            abort(404, 'User not found.');
        }

        if (!in_array(Auth::user()->position, ['Manager', 'Employee'])) {
            return redirect()->back()->with('error', 'You do not have permission to assign cylinders.');
        }

        $validated = $request->validate([
            'cylinder_id' => 'required|exists:cylinders,id|regex:/^0+\d{8}$/',
        ]);

        $cylinder = Cylinder::findOrFail($validated['cylinder_id']);
        if ($user->position !== 'Customer') {
            return redirect()->back()->with('error', 'Only customers can be assigned cylinders.');
        }

        $cylinder->user_id = $user->id;
        $cylinder->location = $user->first_name . ' ' . $user->last_name;

        DB::beginTransaction();
        try {
            $cylinder->save();
            DB::commit();
            return redirect()->route('users.profile', ['id' => $user->id])->with('success', 'Cylinder assigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to assign cylinder.');
        }
    }

    public function destroy($id)
    {
        Log::debug('Destroy Method triggered');

        $cylinder = Cylinder::findOrFail($id);

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

    private function generateUniqueCylinderId()
    {
        do {
            $id = str_pad(rand(1, 999999999), 9, '0', STR_PAD_LEFT);
        } while (Cylinder::where('id', $id)->exists());

        return $id;
    }
}

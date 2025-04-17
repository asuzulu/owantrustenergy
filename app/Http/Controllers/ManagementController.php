<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cylinder;
use App\Models\Warehouse;
use App\Models\State;
use App\Models\Delivery;
use App\Models\Pickup;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ManagementController extends Controller
{
    public function index()
    {
        $cylinders = Cylinder::orderBy('id')->paginate(10);
        return view('management.home', compact('cylinders'));
    }

    public function accounts()
    {
        // Fetch all users with the role 'customer'
        $users = User::where('position', 'Customer')->paginate(10);

        // Fetch states for the Add Customer form
        $states = State::all();

        return view('management.accounts', compact('users', 'states'));
    }

    public function employees()
    {
        // Fetch all users with the role 'employee'
        $employees = User::where('position', 'Employee')->get();
        return view('management.employees', compact('employees'));
    }

    public function agents()
    {
        try {
            $agents = User::where('position', 'Agent')->paginate(10);
            $states = State::all();  // Retrieve all states
        } catch (\Exception $e) {
            Log::error('Error retrieving agents: ' . $e->getMessage());
            abort(500, 'Error retrieving agents.');
        }

        return view('management.agents', compact('agents', 'states'));
    }

    /**
     * Display a paginated list of cylinders, with optional warehouse & size filters.
     */
    public function cylindersPage(Request $request)
    {
        // Redirect customers back to home
        if (! Auth::check() || Auth::user()->position === 'Customer') {
            return redirect()->route('home');
        }

        // All warehouses for the dropdown
        $warehouses = Warehouse::all();

        // Base query
        $query = Cylinder::orderBy('id', 'asc');

        // Apply warehouse filter if provided
        if ($request->filled('warehouse')) {
            $query->where('location', $request->input('warehouse'));
        }

        // Apply size filter if provided
        if ($request->filled('size')) {
            $query->where('size', $request->input('size'));
        }

        // Paginate 10 per page, append filters so links keep them
        $cylinders = $query
            ->paginate(10)
            ->appends($request->only(['warehouse', 'size']));

        return view('management.home', compact('cylinders', 'warehouses'));
    }

    public function assignCylinder(Request $request, User $user)
    {
        $request->validate([
            'cylinder_id' => 'required|exists:cylinders,id',
        ]);

        $user->assignedCylinder()->associate(Cylinder::find($request->cylinder_id));
        $user->save();

        // Assign the 'Employee' role to the user if not already assigned
        if (!$user->hasRole('Employee')) {
            $user->assignRole('Employee');
        }

        return redirect()->route('users.profile', $user->id)->with('success', 'Cylinder assigned and role updated successfully!');
    }

    public function statistics()
    {
        // Fetch assigned cylinders per month
        $cylindersAssignedChart = Cylinder::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->whereNotNull('user_id') // Only assigned cylinders
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fetch new customer registrations per month
        $customerRegistrationsChart = User::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as count")
            ->where('position', 'Customer')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('management.statistics', compact('cylindersAssignedChart', 'customerRegistrationsChart'));
    }
    public function drivers()
    {
        if (Auth::user()->position === 'Customer') {
            abort(403, 'Unauthorized action.');
        }
        try {
            // Use paginate(10) so that $drivers is a paginator, not a collection
            $drivers = User::where('position', 'Driver')->paginate(10);
            $states = State::all();
        } catch (\Exception $e) {
            Log::debug('Error retrieving Drivers: ', ['error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve drivers.');
        }
        return view('management.drivers', compact('drivers', 'states'));
    }

    public function showCylinder($id)
    {
        $cylinder = Cylinder::findOrFail($id);
        $warehouses = DB::table('warehouses')->get();
        $deliveries = Delivery::where('cylinder', $id)->get();
        $pickups = Pickup::where('cylinder', $id)->get();

        return view('cylinders.show', compact('cylinder', 'deliveries', 'pickups', 'warehouses' ));
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
}

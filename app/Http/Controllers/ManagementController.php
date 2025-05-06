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
    public function index(Request $request)
    {
        // Redirect non‑logged‑in or Customer users
        if (! Auth::check() || Auth::user()->position === 'Customer') {
            return redirect()->route('home');
        }

        // Fetch all warehouses for the Location dropdown
        $warehouses = Warehouse::all();

        // Base query for cylinders
        $query = Cylinder::orderBy('id', 'asc');

        // Apply Location (warehouse/customer) filter
        if ($request->filled('warehouse')) {
            if ($request->warehouse === 'Customer') {
                $warehouseNames = $warehouses->pluck('name')->toArray();

                $customerNames = User::where('position', 'Customer')
                    ->get()
                    ->map(fn($u) => $u->first_name . ' ' . $u->last_name)
                    ->toArray();

                $query->whereNotIn('location', $warehouseNames)
                    ->whereIn('location', $customerNames);
            } else {
                $query->where('location', $request->input('warehouse'));
            }
        }

        // Apply Size filter
        if ($request->filled('size')) {
            $query->where('size', $request->input('size'));
        }

        // Apply Search filter (server‑side)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('size', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Paginate and preserve all three query params
        $cylinders = $query
            ->paginate(10)
            ->appends($request->only(['warehouse', 'size', 'search']));

        return view('management.home', compact('cylinders', 'warehouses'));
    }

    public function accounts(Request $request)
    {
        // Redirect non‑logged‑in users or Customers
        if (! Auth::check() || Auth::user()->position === 'Customer') {
            return redirect()->route('home');
        }

        $query = User::where('position', 'Customer');

        $search = trim($request->input('search', ''));
        if ($search !== '') {
            // split on whitespace
            $terms = preg_split('/\s+/', mb_strtolower($search, 'UTF-8'));

            // columns to include in matching
            $columns = ['first_name', 'last_name', 'email', 'phone_number', 'street', 'city', 'state'];

            // build relevance parts
            $bindings = [];
            $relevanceParts = [];
            foreach ($terms as $term) {
                $wild = "%{$term}%";
                foreach ($columns as $col) {
                    $relevanceParts[] = "CASE WHEN LOWER($col) LIKE ? THEN 1 ELSE 0 END";
                    $bindings[] = $wild;
                }
            }
            $relevanceSql = implode(' + ', $relevanceParts) . ' as relevance';

            // apply search + ranking
            $query = $query
                ->select('*')
                ->selectRaw($relevanceSql, $bindings)
                ->where(function ($q) use ($terms, $columns) {
                    foreach ($terms as $term) {
                        $q->where(function ($q2) use ($term, $columns) {
                            foreach ($columns as $col) {
                                $q2->orWhere($col, 'like', "%{$term}%");
                            }
                        });
                    }
                })
                ->orderByDesc('relevance');
        }

        // final ordering & pagination (with search still in the query string)
        $users = $query
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        $states = State::all();

        return view('management.accounts', compact('users', 'states'));
    }

    public function employees(Request $request)
    {
        // Redirect Customers away
        if (! Auth::check() || Auth::user()->position === 'Customer') {
            return redirect()->route('home');
        }

        $query = User::where('position', 'Employee');

        // Server‑side search (name / phone / email)
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('phone_number', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $states = State::all();

        $employees = $query
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        return view('management.employees', compact('employees', 'states'));
    }

    public function agents(Request $request)
    {
        // Redirect non‑logged‑in or Customers away
        if (! Auth::check() || Auth::user()->position === 'Customer') {
            return redirect()->route('home');
        }

        $query = User::where('position', 'Agent');

        // Server‑side search across first_name, last_name, phone_number, email
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name',  'like', "%{$term}%")
                    ->orWhere('phone_number', 'like', "%{$term}%")
                    ->orWhere('email',       'like', "%{$term}%");
            });
        }

        $agents = $query
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        $states = State::all();

        return view('management.agents', compact('agents', 'states'));
    }

    public function cylindersPage(Request $request)
    {
        // Redirect non‑logged‑in or Customer users
        if (! Auth::check() || Auth::user()->position === 'Customer') {
            return redirect()->route('home');
        }

        // Fetch all warehouses for the Location dropdown
        $warehouses = Warehouse::all();

        // Base query for cylinders
        $query = Cylinder::orderBy('id', 'asc');

        // Apply Location (warehouse/customer/agent) filter
        if ($request->filled('warehouse')) {
            if ($request->warehouse === 'Customer') {
                $warehouseNames = $warehouses->pluck('name')->toArray();

                $customerNames = User::where('position', 'Customer')
                    ->get()
                    ->map(fn($u) => $u->first_name . ' ' . $u->last_name)
                    ->toArray();

                $query->whereNotIn('location', $warehouseNames)
                    ->whereIn('location', $customerNames);
            } elseif ($request->warehouse === 'Agent') {
                $agentNames = DB::table('agent_cylinders_distribution')
                    ->pluck('agent_name')
                    ->toArray();

                $query->whereIn('location', $agentNames);
            } else {
                $query->where('location', $request->input('warehouse'));
            }
        }

        // Apply Size filter
        if ($request->filled('size')) {
            $query->where('size', $request->input('size'));
        }

        // Apply Search filter (server‑side)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('size', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Paginate and preserve all three query params
        $cylinders = $query
            ->paginate(10)
            ->appends($request->only(['warehouse', 'size', 'search']));

        return view('management.home', compact('cylinders', 'warehouses'));
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
    public function drivers(Request $request)
    {
        // Redirect Customers away
        if (! Auth::check() || Auth::user()->position === 'Customer') {
            return redirect()->route('dashboard');
        }

        // Base query for drivers
        $query = User::where('position', 'Driver');

        // Server‑side search across name, phone, email, city, state
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('phone_number', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('state', 'like', "%{$term}%");
            });
        }

        // Paginate and preserve search query
        $drivers = $query
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        // States for the Add Driver modal
        $states = State::all();

        return view('management.drivers', compact('drivers', 'states'));
    }

    public function showCylinder($id)
    {
        // $id is the padded 9-digit string, e.g. "000123456"
        $cylinder   = Cylinder::findOrFail($id);
        $warehouses = DB::table('warehouses')->get();
        $today = now()->toDateString();

        // Grab at most one matching delivery or pickup
        $delivery = Delivery::where('cylinder', $id)->first();
        $pickup   = Pickup::where('cylinder', $id)->first();

        return view('cylinders.show', compact(
            'cylinder',
            'warehouses',
            'delivery',
            'pickup',
            'today'
        ));
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

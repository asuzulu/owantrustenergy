<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Cylinder;
use App\Models\State;

class AgentController extends Controller
{
    public function index()
    {
        if (Auth::user()->position === 'Customer') {
            abort(403, 'Unauthorized action.');
        }
        try {
            $agents = User::where('position', 'Agent')->paginate(10);
            $states = State::all();
        } catch (\Exception $e) {
            Log::debug('Error retrieving Agents: ', ['error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve agents.');
        }
        return view('management.agents', compact('agents', 'states'));
    }

    public function cylindersPage(Request $request)
    {
        // Only Employees, Managers and Agents may view
        if (! in_array(Auth::user()->position, ['Employee', 'Manager', 'Agent'])) {
            abort(403, 'Unauthorized action.');
        }

        $agent = Auth::user();

        $query = DB::table('agent_cylinders_distribution')
            ->where('agent_id', $agent->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('size')) {
            $query->where('cylinder_size', $request->size);
        }

        if ($request->filled('search')) {
            // strip leading zeros for search
            $query->where('cylinder_id', 'like', '%' . ltrim($request->search, '0') . '%');
        }

        $distributed = $query->paginate(10);

        return view('agent.cylinders', compact('distributed', 'agent'));
    }

    public function customers()
    {
        // Fetch all users with the role 'Customer'
        $users = User::where('position', 'Customer')->paginate(10);

        // Fetch states for the Add Customer form
        $states = State::all();

        // **Add this line** to make $agent available to your agent‐navbar partial
        $agent = Auth::user();

        // Pass 'agent' into the view alongside 'users' and 'states'
        return view('management.accounts', compact('users', 'states', 'agent'));
    }

    public function dashboard()
    {
        $user = Auth::user();
        $agent = $user->position === 'Agent' ? $user : null; // Check if the user is an agent
        $warehouseCylinders = collect();

        return view('users.profile', compact('user', 'agent', 'warehouseCylinders'));
    }

    public function show($id)
    {
        $agent = User::findOrFail($id);
        $user = User::findOrFail($id);
        $warehouseCylinders = collect();
        return view('users.profile', compact('user', 'warehouseCylinders', 'agent'));
    }

    // Register Agent Modal
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'firstName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'lastName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'phoneNumber' => 'required|digits:10',
            'gender' => 'required|string|in:male,female',
            'street' => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]+/'],
            'city' => 'required|string|max:255',
            'state' => 'required|exists:states,id',
            'bvn' => 'required|digits:11',
            'nin' => 'required|digits:11',
            'email' => ['required', 'email', 'max:255', 'email', 'unique:users,email'],
            'dob' => [
                'required',
                'date',
                'before:' . now()->subYears(18)->toDateString(), // Ensuring the user is at least 16 years old
                'after:' . now()->subYears(130)->toDateString(), // Ensuring the user is not older than 130 years
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',       // At least one uppercase
                'regex:/[a-z]/',       // At least one lowercase
                'regex:/[0-9]/',       // At least one number
                'regex:/[@$!%*?&]/'    // At least one special character
            ],
            'position' => 'nullable|string|max:255',
            'photo_id' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'dob.before' => 'You must be at least 16 years old to register.',
            'dob.after' => 'The date of birth must not be older than 130 years.',
            'password.regex' => 'Password must include at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'phoneNumber.regex' => 'Phone number must be a valid number (e.g., 08012345678).',
            'firstName.regex' => 'First name can only contain letters and spaces.',
            'lastName.regex' => 'Last name can only contain letters and spaces.',
        ]);

        $position = $validatedData['position'] ?? 'Customer';

        try {
            $user = User::create([
                'first_name'   => $validatedData['firstName'],
                'last_name'    => $validatedData['lastName'],
                'phone_number' => $validatedData['phoneNumber'],
                'gender'       => $validatedData['gender'],
                'street'       => $validatedData['street'],
                'city'         => $validatedData['city'],
                'state'        => State::where('id', $validatedData['state'])->value('name'),
                'bvn'          => $validatedData['bvn'],
                'nin'          => $validatedData['nin'],
                'email'        => $validatedData['email'],
                'dob'          => $validatedData['dob'],
                'password'     => Hash::make($validatedData['password']),
                'position'     => $position,
            ]);

            return response()->json(['success' => true, 'message' => 'Agent registered successfully!']);
        } catch (\Exception $e) {
            \Log::error('Agent registration error: ' . $e->getMessage());
            if (config('app.debug')) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return response()->json(['success' => false, 'message' => 'Failed to register agent.'], 500);
        }
    }
}

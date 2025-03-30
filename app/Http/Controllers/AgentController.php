<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
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

    // Added dashboard method to fix the error
    public function dashboard()
    {
        $user = Auth::user();
        $warehouseCylinders = collect(); // Adjust or load data as needed
        return view('users.profile', compact('user', 'warehouseCylinders'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $warehouseCylinders = collect();
        return view('users.profile', compact('user', 'warehouseCylinders'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'firstName'         => 'required|string|max:255',
            'lastName'          => 'required|string|max:255',
            'phoneNumber'       => 'required|string|max:15',
            'gender'            => 'required|string',
            'street'            => 'required|string|max:255',
            'city'              => 'required|string|max:255',
            'state'             => 'required|exists:states,id',
            'bvn'               => 'required|digits:11',
            'nin'               => 'required|digits:11',
            'email'             => 'required|email|unique:users,email',
            'dob'               => 'required|date|before:today',
            'password'          => 'required|string|min:8|confirmed',
            'position'          => 'required|string|in:Agent',
        ]);

        try {
            $stateName = State::where('id', $validatedData['state'])->value('name');

            $agent = User::create([
                'first_name'   => $validatedData['firstName'],
                'last_name'    => $validatedData['lastName'],
                'phone_number' => $validatedData['phoneNumber'],
                'gender'       => $validatedData['gender'],
                'street'       => $validatedData['street'],
                'city'         => $validatedData['city'],
                'state'        => $stateName,
                'bvn'          => $validatedData['bvn'],
                'nin'          => $validatedData['nin'],
                'email'        => $validatedData['email'],
                'dob'          => $validatedData['dob'],
                'password'     => Hash::make($validatedData['password']),
                'position'     => $validatedData['position'],
            ]);

            return redirect()->route('agents.index')->with('success', 'Agent added successfully.');
        } catch (\Exception $e) {
            Log::debug('Error adding Agent: ', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to add agent.');
        }
    }

    public function destroy($id)
    {
        $agent = User::findOrFail($id);

        if (Auth::user()->position !== 'Manager') {
            return redirect()->route('agents.index')->with('error', 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            $agent->delete();
            DB::commit();
            return redirect()->route('agents.index')->with('success', 'Agent deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete agent.');
        }
    }
}

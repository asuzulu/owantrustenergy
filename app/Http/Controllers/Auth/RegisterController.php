<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        $states = State::all();
        return view('register', compact('states'));
    }

    public function registerModal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'lastName' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'phoneNumber' => 'required|digits:10',
            'gender' => 'required|string|in:male,female',
            'street' => ['required', 'string', 'max:255', 'regex:/[a-zA-Z]+/'],
            'city' => 'required|string|max:255',
            'state' => 'required|exists:states,id',
            'bvn' => 'required|digits:11',
            'nin' => 'required|digits:11',
            'email' => ['required', 'email', 'max:255', 'email' /*:rfc,dns'*/, 'unique:users,email'],
            'dob' => ['required', 'date', 'before:' . now()->subYears(18)->toDateString()],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/',       // At least one uppercase
                'regex:/[a-z]/',       // At least one lowercase
                'regex:/[0-9]/',       // At least one number
                'regex:/[@$!%*?&]/'     // At least one special character
            ],
            'position' => 'nullable|string|max:255',
            'photo_id' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'dob.before' => 'You must be at least 18 years old to register.',
            'password.regex' => 'Password must include at least 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.',
            'phoneNumber.regex' => 'Phone number must be a valid number (e.g., 08012345678).',
            'firstName.regex' => 'First name can only contain letters and spaces.',
            'lastName.regex' => 'Last name can only contain letters and spaces.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
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

            return response()->json(['success' => true, 'message' => 'User registered successfully!']);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            if (config('app.debug')) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return response()->json(['success' => false, 'message' => 'Failed to register user.'], 500);
        }
    }

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
            'email' => ['required', 'email', 'max:255', 'email' /*:rfc,dns'*/, 'unique:users,email'],
            'dob' => ['required', 'date', 'before:' . now()->subYears(18)->toDateString()],
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
            'dob.before' => 'You must be at least 18 years old to register.',
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

            // Automatically log in the new user
            Auth::login($user);

            return redirect()->route('dashboard.profile')->with('success', 'Registration successful.');
        } catch (\Exception $e) {
            return back()->with('error', 'Registration failed, please try again later.');
        }
    }
}

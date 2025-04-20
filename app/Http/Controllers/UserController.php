<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Cylinder;
use App\Models\Warehouse;
use Carbon\Carbon;

class UserController extends Controller
{
    // Handle NIN image upload
    public function uploadNin(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->hasFile('nin_image')) {
            $file = $request->file('nin_image');

            // Remove old image if exists
            if ($user->photo_id && Storage::disk('public')->exists('nin-images/' . $user->photo_id)) {
                Storage::disk('public')->delete('nin-images/' . $user->photo_id);
            }

            // Generate new filename
            $filename = strtolower($user->last_name . '-' . $user->first_name . '-' . $user->id . '.' . $file->getClientOriginalExtension());

            // Store image
            $file->storeAs('nin-images', $filename, 'public');

            // Update database
            $user->photo_id = $filename;
            $user->save();

            return response()->json([
                'success' => true,
                'preview_url' => asset('storage/nin-images/' . $filename),
            ]);
        }

        return response()->json(['success' => false], 400);
    }

    private function redirectUserByPosition($user)
    {
        return match ($user->position) {
            'Customer' => redirect()->route('dashboard.profile'),
            'Employee' => redirect()->route('dashboard.employee'),
            'Manager' => redirect()->route('management.home'),
            'Agent' => redirect()->route('agent.dashboard'),
            'Driver' => redirect()->route('dashboard.driver'),
            default => redirect('/'),
        };
    }

    public function showDashboard()
    {
        $userId = Auth::id();
        $cylinders = Cylinder::where('user_id', $userId)->get();
        $totalCylinders = $cylinders->count();

        return view('dashboard.profile', compact('cylinders', 'totalCylinders'));
    }

    /**
     * Display the list of cylinders assigned to the given user.
     */
    public function cylinders($id)
    {
        $user = User::findOrFail($id);
        $cylinders = Cylinder::where('user_id', $user->id)->get();
        $orders     = Order::where('customer_id', $user->id)->get();

        // Only managers/employees/agents can view others; customers only their own
        if (Auth::user()->position === 'Customer' && Auth::id() !== $user->id) {
            return redirect()->route('dashboard.profile');
        }

        $cylinders = Cylinder::where('user_id', $user->id)
            ->orderBy('allocated_date', 'desc')
            ->get();

        $orders = Order::where('customer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('users.cylinders', compact('user', 'cylinders', 'orders'));
    }

    // Update the profile image
    public function updateProfileImage(Request $request, $id)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::findOrFail($id);

        // Delete old image if it exists
        if ($user->profile_image) {
            Storage::disk('public')->delete('profile-images/' . $user->profile_image);
        }

        // Create new filename format: firstName_lastName_YYYYMMDD.extension
        $filename = $user->first_name . '_' . $user->last_name . '_' . now()->format('Ymd') . '.' . $request->file('profile_image')->getClientOriginalExtension();

        // Store image
        $imagePath = $request->file('profile_image')->storeAs('profile-images', $filename, 'public');

        // Update user record
        $user->update([
            'profile_image' => $filename,
        ]);

        return redirect()->route('profile.view', $user->id)->with('success', 'Profile image updated successfully.');
    }

    public function profile($id)
    {
        // Find the user by ID
        $user = User::findOrFail($id);
        $cylinders = Cylinder::where('user_id', $id)->get();
        $user->age = Carbon::parse($user->dob)->age;
        $warehouseCylinders = collect();
        // Get total assigned cylinders for the user
        $totalCylinders = Cylinder::where('user_id', $user->id)->count();
        // Get the user's orders (assuming 'customer_id' is used for relation)
        $orders = Order::where('customer_id', $user->id)->get();
        if (in_array(Auth::user()->position, ['Manager', 'Employee'])) {
            $warehouseCylinders = Cylinder::whereIn('location', Warehouse::pluck('name')->toArray())
                ->orWhereNull('user_id')
                ->select(['id', 'size'])
                ->get();
        }

        $users = Auth::user()->position === 'Manager' ? User::all() : null;

        return view('users.profile', compact('user', 'orders', 'cylinders', 'warehouseCylinders', 'totalCylinders'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $states = State::all();
        return view('users.edit', compact('user', 'states'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $updateData = array_filter([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'street' => $request->street,
            'city' => $request->city,
            'state' => $request->state ? State::where('id', $request->state)->value('name') : null,
        ], fn($value) => !is_null($value) && $value !== '');

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Return the response as JSON with a success message and redirect URL
        return response()->json([
            'success' => true,
            'message' => 'User details updated successfully.',
            'redirect' => route('users.profile', $id),
        ]);
    }

    public function destroy($id)
    {
        \Log::info('Entered destroy method for user ID: ' . $id);

        try {
            $user = User::findOrFail($id);
            \Log::info('Found user: ' . $user->id);

            Cylinder::where('user_id', $user->id)->update(['user_id' => null]);
            \Log::info('Unlinked cylinders from user ID: ' . $user->id);

            $user->delete();
            \Log::info('User deleted: ' . $user->id);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
                'redirect' => route('management.cylinders')  // Correct redirect route
            ]);
        } catch (\Throwable $e) {
            \Log::error('User deletion failed: ' . $e->getMessage());
            \Log::error($e);
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }
}

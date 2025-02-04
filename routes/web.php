<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UserController,
    DashboardController,
    Auth\PasswordController,
    Auth\SignInController,
    Auth\AuthController,
    CylinderController,
    ManagementController,
    CustomerController,
    EmployeeController,
    LocationController,
    WarehouseController
};

// Home page
Route::get('/', function () {
    return view('home');
})->name('home');

// About page
Route::get('/about', function () {
    return view('about');
})->name('about');

// Products page
Route::get('/products', function () {
    return view('products');
})->name('products');

// Contact page
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Register page and routes
Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [UserController::class, 'store'])->name('register.store');

// Sign In page for each portal
Route::get('/signin/{portal}', [SignInController::class, 'showSignInForm'])->name('signin.form');

// Handle sign-in submission
Route::post('/signin', [SignInController::class, 'store'])->name('signin.store');

// AuthController routes to handle login for customer, employee, and manager
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store');

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/home', [DashboardController::class, 'customerHome'])->name('dashboard.home');
    Route::get('/employee/home', [EmployeeController::class, 'dashboard'])->name('employee.home');
    Route::get('/management/home', [DashboardController::class, 'managementHome'])->name('management.home');
});

// Password reset routes
Route::get('password/reset', [PasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [PasswordController::class, 'reset'])->name('password.update');

// Custom Logout route
Route::post('/logout', [SignInController::class, 'customLogout'])->name('logout');

// Employee and Management portal static pages
Route::view('/employee-portal', 'employee-portal')->name('employee.portal');
Route::view('/management-portal', 'management-portal')->name('management.portal');

// Profile route
Route::get('/dashboard/home', [UserController::class, 'showDashboard'])->name('dashboard.home')->middleware('auth');

// Profile image
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/{id}', [UserController::class, 'profile'])->name('profile.view');
    Route::post('/profile/update-image', [UserController::class, 'updateProfileImage'])->name('profile.updateImage');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/{id}/update', [UserController::class, 'update'])->name('users.update');
});

// Cylinder management routes
Route::middleware(['auth'])->group(function () {
    Route::get('/management/cylinders', [ManagementController::class, 'cylindersPage'])->name('management.cylinders');
    Route::delete('cylinders/destroy/{id}', [CylinderController::class, 'destroyCustom'])->name('cylinders.destroy.custom');  // updated route name
    Route::get('/cylinders/create', [CylinderController::class, 'create'])->name('cylinders.create');
    Route::get('/cylinders', [CylinderController::class, 'index'])->name('cylinders.index');
});

// Route for Customer Cylinder List
Route::get('/dashboard/cylinder/{userId?}', [CylinderController::class, 'index'])->name('dashboard.cylinder');

// Management Dashboard
Route::get('/management-dashboard', [ManagementController::class, 'index'])->name('dashboard.management');

// Statistics Page Route
Route::get('/management/statistics', [ManagementController::class, 'statistics'])->name('management.statistics');

// Management Cylinder Page Route
Route::get('management/cylinder', [ManagementController::class, 'cylinder'])->name('management.cylindersPage');

// Route to assign cylinder
Route::post('/management/assign-cylinder/{user}', [CylinderController::class, 'assignCylinder'])->name('management.assign-cylinder');

// Route for managing customers
Route::get('/management/accounts', [ManagementController::class, 'accounts'])->name('management.accounts');

// Route for managing employees
Route::get('/management/employees', [ManagementController::class, 'employees'])->name('management.employees');

// Route for managing agents
Route::get('/management/agents', [ManagementController::class, 'agents'])->name('management.agents');

// Route for viewing the user profile
Route::get('/users/{id}', [UserController::class, 'profile'])->name('users.profile');

// Route for Customer's view of Cylinder List
Route::middleware(['auth', 'role:Customer'])->group(function () {
    Route::get('/customer/cylinders', [CustomerController::class, 'showCylinders'])->name('customer.cylinder');
});

// Employee-specific routes
Route::middleware(['auth'])->group(function () {
    Route::get('/employee/accounts', [EmployeeController::class, 'accounts'])->name('employee.accounts');
    Route::get('/employee/agents', [EmployeeController::class, 'agents'])->name('employee.agents');
    Route::get('/employee/statistics', [EmployeeController::class, 'statistics'])->name('employee.statistics');
    Route::get('/employee/cylinders', [EmployeeController::class, 'cylindersPage'])->name('employee.cylinders');
});

// Routes for the cylinder detail page
Route::prefix('cylinders')->name('cylinders.')->group(function () {
    Route::get('/', [CylinderController::class, 'index'])->name('index');
    Route::get('detail/{id}', [CylinderController::class, 'show'])->name('show.detail');  // Renamed route to avoid conflict
    Route::delete('destroy/{id}', [CylinderController::class, 'destroyCustom'])->name('destroy.custom');  // updated route name
});

// Remove the previous conflicting route for `cylinders.show`
Route::resource('cylinders', CylinderController::class)->parameters([
    'cylinder' => 'id',
])->except(['show']);

Route::middleware(['auth'])->group(function () {
    Route::get('/cylinders/{id}', [CylinderController::class, 'show'])->name('cylinders.show');
});

//Add New Cylinder form Warehouse location field population logic
Route::middleware(['auth'])->group(function () {
    Route::get('/cylinders/create', [CylinderController::class, 'create'])->name('cylinders.create');
    Route::post('/cylinders', [CylinderController::class, 'store'])->name('cylinders.store');
});

//Warehouse Management route
Route::resource('warehouses', WarehouseController::class);

//Add new cylinder location dropdown
Route::get('/locations/warehouses', [LocationController::class, 'getWarehouses'])->name('locations.getWarehouseLocations');
Route::get('/locations/getWarehouses', [LocationController::class, 'getWarehouses'])->name('locations.getWarehouses');


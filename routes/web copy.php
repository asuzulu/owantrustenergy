<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectIfNotAuthenticated;
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
    WarehouseController,
    StatisticsController,
    AgentController,
    DriversController,
    SearchAutoCompleteController,
    DeliveryController,
    PickupController,
    OrdersController
};

// Public pages
Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');
Route::view('/products', 'products')->name('products');
Route::view('/contact', 'contact')->name('contact');

// Authentication routes
Auth::routes(['register' => false]);
Route::get('/register', [UserController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [UserController::class, 'store'])->name('register.store');
Route::get('/sign-in', [SignInController::class, 'showSignInForm'])->name('signin.form');
Route::post('/signin', [SignInController::class, 'store'])->name('signin.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [SignInController::class, 'customLogout'])->name('logout');
Route::get('/login', [SignInController::class, 'showSignInForm'])->name('login');
Route::post('/login', [SignInController::class, 'store'])->name('login.store');
Route::get('/logout', function () {
    return redirect('/');
});

// Password reset routes
Route::get('password/reset', [PasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [PasswordController::class, 'reset'])->name('password.update');

// Dashboard routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/profile', [DashboardController::class, 'customerDashboard'])->name('dashboard.profile');
    Route::get('/employee/home', [EmployeeController::class, 'dashboard'])->name('employee.home');
    Route::get('/management/home', [DashboardController::class, 'managementHome'])->name('management.home');
});

// Profile routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/{id}', [UserController::class, 'profile'])->name('profile.view');
    Route::post('/users/{id}/update-profile-image', [UserController::class, 'updateProfileImage'])->name('users.update-profile-image');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{id}', [UserController::class, 'profile'])->name('users.profile');
});

// Cylinder management routes
Route::middleware(['auth'])->prefix('management/cylinders')->name('management.cylinders.')->group(function () {
    Route::get('/', [ManagementController::class, 'cylindersPage'])->name('index');
    Route::get('/{id}', [ManagementController::class, 'showCylinder'])->name('show');
    Route::delete('cylinders/destroy/{id}', [CylinderController::class, 'destroyCustom'])->name('cylinders.destroy.custom');
    Route::get('/cylinders/create', [CylinderController::class, 'create'])->name('cylinders.create');
    Route::get('/cylinders', [CylinderController::class, 'index'])->name('cylinders.index');
    Route::post('/management/assign-cylinder/{user}', [CylinderController::class, 'assignCylinder'])->name('management.assign-cylinder');
});

// Cylinder detail routes
Route::prefix('cylinders')->name('cylinders.')->group(function () {
    Route::get('/', [CylinderController::class, 'index'])->name('index');
    Route::get('detail/{id}', [CylinderController::class, 'show'])->name('show.detail');
    Route::delete('destroy/{id}', [CylinderController::class, 'destroyCustom'])->name('destroy.custom');
});
Route::resource('cylinders', CylinderController::class)->parameters(['cylinder' => 'id']);

// Customer-specific routes
Route::middleware(['auth'])->group(function () {
    // Display Customer Cylinder page
    Route::get('/dashboard/cylinders', [CustomerController::class, 'showCylinders'])->name('dashboard.cylinder');

    // Display Order Cylinder page
    Route::get('/dashboard/ordercylinder', function () {
        return view('dashboard.ordercylinder');
    })->name('dashboard.ordercylinder');

    // Handle Order Placement
    Route::post('/order/place', function () {
        return redirect()->route('dashboard.ordercylinder')->with('success', 'Your order has been placed successfully.');
    })->name('order.place');
});

// Employee-specific routes
Route::middleware(['auth'])->group(function () {
    Route::get('/employee/dashboard', [ManagementController::class, 'index'])->name('dashboard.employee');
    Route::get('/employee/accounts', [EmployeeController::class, 'accounts'])->name('employee.accounts');
    Route::get('/employee/agents', [EmployeeController::class, 'agents'])->name('employee.agents');
    Route::get('/employee/statistics', [EmployeeController::class, 'statistics'])->name('employee.statistics');
    Route::get('/employee/cylinders', [EmployeeController::class, 'cylindersPage'])->name('employee.cylinders');
});

// Agent-specific routes
Route::get('/agent/dashboard', [AgentController::class, 'dashboard'])->name('dashboard.agent');
Route::get('/agent/cylinders', [AgentController::class, 'cylindersPage'])->name('agent.cylinders');
Route::get('/agent/accounts', [AgentController::class, 'accounts'])->name('agent.accounts');

//Driver-specific routes
Route::get('/drivers/cylinders', [DriversController::class, 'dashboard'])->name('drivers.cylinders');
Route::get('/drivers/{id}/profile', [DriversController::class, 'driverProfile'])->name('drivers.profile');
// Driver cylinders show page route
Route::middleware(['auth'])->prefix('drivers/cylinders')->name('drivers.cylinders.')->group(function () {
    Route::get('/', [DriversController::class, 'dashboard'])->name('index');
    Route::get('/{id}', [DriversController::class, 'showCylinder'])->name('show');

// Management routes
Route::middleware(['auth'])->prefix('management')->name('management.')->group(function () {
    Route::get('/orders/requests', [OrdersController::class, 'requests'])->name('orders.requests');
});
Route::get('/management/dashboard', [ManagementController::class, 'index'])->name('dashboard.management');
Route::get('/management/statistics', [StatisticsController::class, 'index'])->name('management.statistics');
Route::get('/management/accounts', [ManagementController::class, 'accounts'])->name('management.accounts');
Route::get('/management/employees', [ManagementController::class, 'employees'])->name('management.employees');
Route::get('/management/agents', [ManagementController::class, 'agents'])->name('management.agents');
Route::get('management/drivers', [ManagementController::class, 'drivers'])->name('management.drivers');

// Warehouse management routes
Route::resource('warehouses', WarehouseController::class)->except(['show']);
Route::get('/warehouses/{id}', [WarehouseController::class, 'show'])->name('warehouses.show');

// Location routes
Route::get('/locations/warehouses', [LocationController::class, 'getWarehouses'])->name('locations.getWarehouseLocations');
Route::get('/locations/getWarehouses', [LocationController::class, 'getWarehouses'])->name('locations.getWarehouses');

// Employee statistics page
Route::get('/employee/statistics', [StatisticsController::class, 'index'])
    ->name('employee.statistics')
    ->middleware(['auth']);

// Redirect unauthenticated users to
Route::get('/management/cylinders/{id}', [ManagementController::class, 'showCylinder'])
    ->name('management.cylinders.show')
    ->middleware(['auth']);

// Statistics charts data
Route::get('/statistics/data', [StatisticsController::class, 'getStatisticsData']);

// Assign Cylinder Search Bar
Route::post('/assign-cylinder', [CylinderController::class, 'assign'])->name('cylinders.assign');
Route::get('/search/customers', [SearchAutoCompleteController::class, 'searchCustomers'])->name('search.customers');
Route::get('/search/drivers', [SearchAutoCompleteController::class, 'searchDrivers'])->name('search.drivers');

// Cylinder delivery route
Route::post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');

// Cylinder Pickup routes
Route::post('/pickups/store', [PickupController::class, 'store'])->name('pickups.store');
Route::get('/orders/pickup', [PickupController::class, 'index'])->name('orders.pickup');
Route::post('/orders/update-pickup', [OrdersController::class, 'updatePickup'])->name('orders.updatePickup');

// Route for uploading NIN image
Route::post('/users/{id}/upload-nin', [UserController::class, 'uploadNin'])->name('upload.nin');

Route::middleware(['auth'])->group(function () {
    // Employee Management Routes under the 'management' prefix
    Route::prefix('management')->group(function () {
        // List all employees
        Route::get('/employees', [EmployeeController::class, 'index'])->name('management.employees');
        // Show a single employee's details
        Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
        // Store a new employee (from the modal form)
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        // Delete an employee (only Manager authorized)
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home.dashboard');

Route::middleware(['auth'])->group(function () {
    // List all agents (paginated)
    Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');

    // Show a single agent's profile
    Route::get('/agents/{id}', [AgentController::class, 'show'])->name('agents.show');

    // Store a new agent (from the modal form)
    Route::post('/agents', [AgentController::class, 'store'])->name('agents.store');

    // Delete an agent (only Manager authorized)
    Route::delete('/agents/{id}', [AgentController::class, 'destroy'])->name('agents.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/management/drivers', [DriversController::class, 'index'])->name('drivers.index');
    Route::post('/management/drivers', [DriversController::class, 'store'])->name('drivers.store');
    Route::get('/management/drivers/profile/{id}', [DriversController::class, 'driverProfile'])->name('management.drivers.profile');
    Route::put('/management/drivers/profile/{id}', [DriversController::class, 'update'])->name('drivers.update');
    Route::delete('/management/drivers/{id}', [DriversController::class, 'destroy'])->name('drivers.destroy');
    Route::get('/drivers/cylinders', [DriversController::class, 'dashboard'])->name('drivers.dashboard');
});

// Customer Order Routes
Route::post('/order/place', [OrdersController::class, 'placeOrder'])->name('order.place');
Route::delete('/orders/delete', [OrdersController::class, 'destroy'])->name('orders.destroy');
Route::delete('/orders', [OrdersController::class, 'destroy'])->name('orders.delete');
Route::get('/orders/requests', [OrdersController::class, 'requests'])->name('orders.requests');
});

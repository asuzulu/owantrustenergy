<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RedirectIfNotAuthenticated;
use App\Http\Middleware\AuthMiddleware;
use App\Models\User;
use App\Http\Controllers\{
    Auth\PasswordController,
    Auth\SignInController,
    Auth\RegisterController,
    UserController,
    DashboardController,
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
    OrdersController,
    CylinderDistributionController,
    GlobalSettingsController
};

// Public pages
Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');
Route::view('/products', 'products')->name('products');
Route::view('/contact', 'contact')->name('contact');

// Authentication routes
Auth::routes(['register' => false]);
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// Login routes (sign-in and login routes merged)
Route::get('/sign-in', [SignInController::class, 'showSignInForm'])->name('signin.form');
Route::get('/login', [SignInController::class, 'showSignInForm'])->name('login');
Route::post('/signin', [SignInController::class, 'store'])->name('signin.store');
Route::post('/login', [SignInController::class, 'store'])->name('login.store');

// Logout route
Route::post('/logout', [SignInController::class, 'customLogout'])->name('logout');
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
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{id}/profile', [UserController::class, 'profile'])->name('users.profile');
});

// Cylinder management routes
Route::middleware(['auth'])->prefix('management/cylinders')->name('management.cylinders.')->group(function () {
    // Main Cylinder Management page (add a name here)
    Route::get('/', [ManagementController::class, 'index'])->name('index');

    // Show a single cylinder
    Route::get('/{id}', [ManagementController::class, 'showCylinder'])->name('show');

    // List all cylinders (another listing route)
    Route::get('/', [CylinderController::class, 'index'])->name('list');

    // Assign a cylinder to a user
    Route::post('/assign-cylinder/{user}', [CylinderController::class, 'assignCylinder'])->name('assign-cylinder');
});

// Cylinders list page
Route::get('/management/cylinders', [ManagementController::class, 'cylindersPage'])->name('management.cylinders');
Route::post('/cylinders', [CylinderController::class, 'store'])->name('cylinders.store');
Route::post('/register-modal', [RegisterController::class, 'registerModal'])->name('register.modal');

// Cylinder detail routes
Route::prefix('cylinders')->name('cylinders.')->group(function () {
    Route::get('/', [CylinderController::class, 'index'])->name('index');
    Route::get('detail/{id}', [CylinderController::class, 'show'])->name('show.detail');
    Route::delete('destroy/{id}', [CylinderController::class, 'destroy'])->name('destroy');
});

// Customer-specific routes
Route::middleware(['auth'])->group(function () {
    // Display Customer Cylinder page
    Route::get('/dashboard/cylinders', [CustomerController::class, 'showCylinders'])->name('dashboard.cylinder');

    // Display Order Cylinder page
    Route::get('/dashboard/ordercylinder', function () {
        return view('dashboard.ordercylinder');
    })->name('dashboard.ordercylinder');

    // Handle Order Placement
    Route::post('/order/place', [OrdersController::class, 'placeOrder'])->name('order.place');
});

// Employee-specific routes
Route::middleware(['auth'])->group(function () {
    Route::get('/employee/dashboard', [ManagementController::class, 'index'])->name('dashboard.employee');
    Route::get('/employee/accounts', [ManagementController::class, 'accounts'])->name('employee.accounts');
    Route::get('/employee/agents', [ManagementController::class, 'agents'])->name('employee.agents');
    Route::get('/employee/cylinders', [ManagementController::class, 'cylindersPage'])->name('employee.cylinders');
});
Route::get('/employee/profile', [EmployeeController::class, 'profile'])->name('employee.profile')->middleware('auth');

// Agent-specific routes
Route::get('/agent/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');
Route::get('/agent/cylinders', [AgentController::class, 'cylindersPage'])->name('agent.cylinders');
Route::get('/agent/customers', [AgentController::class, 'customers'])->name('agent.customers');
Route::get('/agent/profile', [AgentController::class, 'dashboard'])->name('agent.profile');

// Driver-specific routes
Route::get('/drivers/{id}/profile', [DriversController::class, 'driverProfile'])->name('drivers.profile');
Route::middleware(['auth'])->group(function() {
    // show the delivering upload form
    Route::get('/drivers/delivering/{cylinder}', [DriversController::class, 'delivering'])
         ->name('drivers.delivering');

    // handle the image upload & mark delivered
    Route::post('/drivers/delivering/{cylinder}', [DriversController::class, 'storeDeliveryImage'])
         ->name('drivers.delivering.store');
});

// Driver cylinders show page route
Route::middleware(['auth'])->prefix('drivers/cylinders')->name('drivers.cylinders.')->group(function () {
    Route::get('/', [DriversController::class, 'dashboard'])->name('index');
    Route::get('/{id}', [DriversController::class, 'showCylinder'])->name('show');
});

// Management routes
Route::middleware(['auth'])->prefix('management')->name('management.')->group(function () {
    Route::get('/orders/requests', [OrdersController::class, 'requests'])->name('orders.requests');
});
Route::get('/management/dashboard', [ManagementController::class, 'index'])->name('dashboard.management');
Route::get('/management/statistics', [StatisticsController::class, 'index'])->name('management.statistics');
Route::get('/management/accounts', [ManagementController::class, 'accounts'])->name('management.accounts');
Route::post('/management/accounts', [ManagementController::class, 'store']);
Route::get('/management/employees', [ManagementController::class, 'employees'])->name('management.employees');
Route::get('/management/agents', [ManagementController::class, 'agents'])->name('management.agents');
Route::get('management/drivers', [ManagementController::class, 'drivers'])->name('management.drivers');
Route::get('/management/deliveries', [DeliveryController::class, 'index'])->name('management.deliveries');
Route::delete('/management/deliveries/delete', [DeliveryController::class, 'destroy'])->name('management.deliveries.delete');

// Warehouse management routes
Route::get('/warehouses/{id}', [WarehouseController::class, 'show'])->name('warehouses.show');
Route::get('/warehouses/{warehouse}/agent-cylinders', [WarehouseController::class, 'loadAgentCylinders'])->name('warehouses.agentCylinders');
Route::get('/warehouses/{warehouse}/warehouse-cylinders', [WarehouseController::class, 'loadWarehouseCylinders'])->name('warehouses.warehouseCylinders');

// Location routes
Route::get('/locations/warehouses', [LocationController::class, 'getWarehouses'])->name('locations.getWarehouseLocations');
Route::get('/locations/getWarehouses', [LocationController::class, 'getWarehouses'])->name('locations.getWarehouses');

// Employee statistics page
Route::get('/employee/statistics', [StatisticsController::class, 'index'])
    ->name('employee.statistics')
    ->middleware(['auth']);

// Cylinders details page
Route::get('/management/cylinders/{id}', [ManagementController::class, 'showCylinder'])
    ->name('management.cylinders.show')
    ->middleware(['auth']);

// Distribute cylinders to Agents
Route::get('/cylinders/warehouse/data', [CylinderDistributionController::class, 'warehouseData'])
    ->name('cylinders.warehouse.data');
Route::post('/cylinders/distribute/{id}', [CylinderDistributionController::class, 'distribute'])
    ->name('cylinders.distribute');

// Pick up distributed cylinder from warehouse by agent
Route::post('/warehouses/{warehouse}/confirm-agent-pickup', [WarehouseController::class, 'confirmAgentPickup'])
    ->name('warehouses.confirmAgentPickup');

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
Route::post('/pickups/update', [PickupController::class, 'updatePickup'])->name('pickups.update');

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
    Route::post('/agents/add', [AgentController::class, 'store'])->name('agents.store');

    // Delete an agent (only Manager authorized)
    Route::delete('/agents/{id}', [AgentController::class, 'destroy'])->name('agents.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/drivers/index', [DriversController::class, 'index'])->name('drivers.index');
    Route::post('/management/drivers', [DriversController::class, 'store'])->name('drivers.store');
    Route::get('/management/drivers/profile/{id}', [DriversController::class, 'driverProfile'])->name('management.drivers.profile');
    Route::put('/management/drivers/profile/{id}', [DriversController::class, 'update'])->name('drivers.update');
    Route::delete('/management/drivers/{id}', [DriversController::class, 'destroy'])->name('drivers.destroy');
    Route::get('/drivers/cylinders', [DriversController::class, 'dashboard'])->name('drivers.cylinders');
});

// Customer Order Routes
Route::delete('/orders/delete', [OrdersController::class, 'destroy'])->name('orders.destroy');
Route::delete('/orders', [OrdersController::class, 'destroy'])->name('orders.delete');
Route::get('/orders/requests', [OrdersController::class, 'requests'])->name('orders.requests');

// Admin settings page routes
Route::middleware(['auth', 'role:Manager'])->group(function () {
    Route::get('/manager/global-settings', [GlobalSettingsController::class, 'index'])->name('manager.settings.index');
    Route::post('/manager/global-settings/update', [GlobalSettingsController::class, 'update'])->name('manager.settings.update');
});

// Show a specific user's cylinders (assigned + orders)
Route::middleware(['auth'])
     ->get('/users/{id}/cylinders', [UserController::class, 'cylinders'])
     ->name('users.cylinders');

// Unassigned cylinders list page
Route::get('/cylinders/unassigned', [CylinderController::class, 'showUnassigned'])
    ->name('cylinders.unassigned');
Route::post('/cylinders/assign', [CylinderController::class, 'assignWarehouses'])->name('warehouses.assign');

// Resource routes
Route::resource('cylinders', CylinderController::class)->parameters(['cylinder' => 'id']);
Route::resource('warehouses', WarehouseController::class)->except(['show']);

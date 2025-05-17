<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
    HomeController,
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

// Cylinder management routes (management/cylinders)
Route::middleware(['auth'])
    ->prefix('management/cylinders')
    ->name('management.cylinders.')
    ->group(function () {
        Route::get('/', [ManagementController::class, 'index'])->name('index');
        Route::get('/{id}', [ManagementController::class, 'showCylinder'])->name('show');
        Route::get('/', [CylinderController::class, 'index'])->name('list');
        Route::post('/assign-cylinder/{user}', [CylinderController::class, 'assignCylinder'])->name('assign-cylinder');
    });

// Cylinders list page (legacy)
Route::get('/management/cylinders', [ManagementController::class, 'cylindersPage'])->name('management.cylinders');
Route::post('/register-modal', [RegisterController::class, 'registerModal'])->name('register.modal');

// Customer-specific routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/cylinders', [CustomerController::class, 'showCylinders'])->name('dashboard.cylinder');
    Route::get('/dashboard/ordercylinder', fn() => view('dashboard.ordercylinder'))->name('dashboard.ordercylinder');
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
Route::get('/agent/{id}/cylinders', [AgentController::class, 'cylindersPage'])->name('agent.cylinders');
Route::get('/agent/customers', [AgentController::class, 'customers'])->name('agent.customers');
Route::get('/agent/profile', [AgentController::class, 'dashboard'])->name('agent.profile');

// Driver-specific routes (upload form)
Route::get('/drivers/{id}/profile', [DriversController::class, 'driverProfile'])->name('drivers.profile');
Route::middleware(['auth'])->group(function () {
    // ── Delivery start ───────────────────────────────────────────────
    Route::post('/deliveries/{delivery}/start', [DeliveryController::class, 'start'])
        ->name('deliveries.start');
    // ── Delivering page routes ───────────────────────────────────────────────
    Route::get('/drivers/delivering/{cylinder}', [DriversController::class, 'delivering'])->name('drivers.delivering');
    Route::post('/drivers/delivering/{cylinder}', [DriversController::class, 'storeDeliveryImage'])->name('drivers.delivering.store');
});

Route::post('/deliveries/{delivery}/approve', [DeliveryController::class, 'approve'])
    ->name('deliveries.approve');
Route::post('/deliveries/{delivery}/disapprove', [DeliveryController::class, 'disapprove'])
    ->name('deliveries.disapprove');

// Driver cylinders show page route
Route::middleware(['auth'])
    ->prefix('drivers/cylinders')
    ->name('drivers.cylinders.')
    ->group(function () {
        Route::get('/', [DriversController::class, 'dashboard'])->name('index');
        Route::get('/{id}', [DriversController::class, 'showCylinder'])->name('show');
    });

// Management routes
Route::middleware(['auth'])
    ->prefix('management')
    ->name('management.')
    ->group(function () {
        Route::get('/orders/requests', [OrdersController::class, 'requests'])->name('orders.requests');
    });
Route::get('/management/dashboard', [ManagementController::class, 'index'])->name('dashboard.management');
Route::get('/management/statistics', [StatisticsController::class, 'index'])->name('management.statistics');
Route::get('/management/accounts', [ManagementController::class, 'accounts'])->name('management.accounts');
Route::post('/management/accounts', [ManagementController::class, 'store']);
Route::get('/management/employees', [ManagementController::class, 'employees'])->name('management.employees');
Route::get('/management/agents', [ManagementController::class, 'agents'])->name('management.agents');
Route::get('/management/drivers', [ManagementController::class, 'drivers'])->name('management.drivers');
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

// Cylinders details page (management)
Route::get('/management/cylinders/{id}', [ManagementController::class, 'showCylinder'])
    ->name('management.cylinders.show')
    ->middleware(['auth']);

// Distribute cylinders to Agents
Route::get('/cylinders/warehouse/data', [CylinderDistributionController::class, 'warehouseData'])
    ->name('cylinders.warehouse.data');
Route::post('/cylinders/distribute/{id}', [CylinderDistributionController::class, 'distribute'])
    ->name('cylinders.distribute');
Route::post('/agents/{id}/update-pickup-date', [CylinderDistributionController::class, 'updatePickUpDate'])->name('agent.updatePickUpDate');

// Pick up distributed cylinder from warehouse by agent
Route::post('/warehouses/{warehouse}/confirm-agent-pickup', [WarehouseController::class, 'confirmAgentPickup'])
    ->name('warehouses.confirmAgentPickup');

// Statistics charts data
Route::get('/statistics/data', [StatisticsController::class, 'getStatisticsData']);

// Assign Cylinder Search Bar
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

// Employee Management Routes under the 'management' prefix
Route::middleware(['auth'])->group(function () {
    Route::prefix('management')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('management.employees');
        Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });
});

Route::get('/home', [HomeController::class, 'index'])->name('home.dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/{id}', [AgentController::class, 'show'])->name('agents.show');
    Route::post('/agents/add', [AgentController::class, 'store'])->name('agents.store');
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

// Driver Cylinder Delivery Pick Up listing
Route::get('/delivery-pickup', [DeliveryController::class, 'deliveryListing'])
    ->middleware('auth')
    ->name('delivery.pickup');

Route::get('/delivery-index', [DeliveryController::class, 'index'])
    ->middleware('auth')
    ->name('deliveries.index');

// Handle approval
Route::post('deliveries/approve', [DeliveryController::class, 'updateApproval'])
    ->name(name: 'deliveries.updateApproval')
    ->middleware('auth');

// Confirm delivery (Manager/Employee/Agent)
Route::post('/deliveries/{driver}/confirm', [DeliveryController::class, 'confirm'])
    ->name('deliveries.confirm');

// Driver start delivery
Route::get('/deliveries/{id}/start', [DeliveryController::class, 'start'])->name('deliveries.start');


// Centralized /cylinders routes
Route::prefix('cylinders')->name('cylinders.')->group(function () {
    // Listing & pagination & search
    Route::get('/', [CylinderController::class, 'index'])->name('index');

    // Unassigned cylinders view
    Route::get('/unassigned', [CylinderController::class, 'showUnassigned'])->name('unassigned');

    // Create a new cylinder
    Route::post('/', [CylinderController::class, 'store'])->name('store');

    // Show cylinder details
    Route::get('/detail/{id}', [CylinderController::class, 'show'])->name('show');

    // Delete a cylinder
    Route::delete('/destroy/{id}', [CylinderController::class, 'destroy'])->name('destroy');

    // Assign warehouse to cylinder
    Route::post('/assign', [CylinderController::class, 'assignWarehouses'])->name('assign');

    // Assign cylinder to user
    Route::post('/assign-cylinder', [CylinderController::class, 'assign'])->name('assign-cylinder');

    // Return cylinder to warehouse
    Route::post('/{id}/return', [CylinderController::class, 'returnToWarehouse'])->name('return');

    // Warehouse data for distribution
    Route::get('/warehouse/data', [CylinderDistributionController::class, 'warehouseData'])->name('warehouse.data');

    // Distribute cylinder
    Route::post('/distribute/{id}', [CylinderDistributionController::class, 'distribute'])->name('distribute');
});

// Resource routes
Route::resource('cylinders', CylinderController::class)->parameters(['cylinder' => 'id']);
Route::resource('warehouses', WarehouseController::class)->except(['show']);

<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    Auth\PasswordController,
    Auth\RegisterController,
    Auth\SignInController,
    AgentController,
    CustomerController,
    DashboardController,
    DeliveryController,
    DriversController,
    CylinderController,
    CylinderDistributionController,
    EmployeeController,
    GlobalSettingsController,
    HomeController,
    LocationController,
    ManagementController,
    OrdersController,
    PickupController,
    SearchAutoCompleteController,
    StatisticsController,
    UserController,
    WarehouseController
};

/*
|--------------------------------------------------------------------------
| Public Pages
|--------------------------------------------------------------------------
*/

Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');
Route::view('/products', 'products')->name('products');
Route::view('/contact', 'contact')->name('contact');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
// Disable default registration routes, but allow custom register
Auth::routes(['register' => false]);
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// Sign-in and login merged
Route::get('/sign-in', [SignInController::class, 'showSignInForm'])->name('signin.form');
Route::get('/login', [SignInController::class, 'showSignInForm'])->name('login');
Route::post('/signin', [SignInController::class, 'store'])->name('signin.store');
Route::post('/login', [SignInController::class, 'store'])->name('login.store');

// Logout
Route::post('/logout', [SignInController::class, 'customLogout'])->name('logout');
Route::get('/logout', function () {
    return redirect('/');
});

// Password reset
Route::get('password/reset', [PasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [PasswordController::class, 'reset'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/profile', [DashboardController::class, 'customerDashboard'])->name('dashboard.profile');
    Route::get('/employee/home', [EmployeeController::class, 'dashboard'])->name('employee.home');
    Route::get('/management/home', [DashboardController::class, 'managementHome'])->name('management.home');
});

/*
|--------------------------------------------------------------------------
| User & Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/{id}', [UserController::class, 'profile'])->name('profile.view');
    Route::post('/users/{id}/update-profile-image', [UserController::class, 'updateProfileImage'])->name('users.update-profile-image');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/{id}/profile', [UserController::class, 'profile'])->name('users.profile');
});

/*
|--------------------------------------------------------------------------
| Show User's Cylinders (Assigned + Orders)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/users/{id}/cylinders', [UserController::class, 'cylinders'])->name('users.cylinders');

/*
|--------------------------------------------------------------------------
| Agents Management (Global)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/{id}', [AgentController::class, 'show'])->name('agents.show');
    Route::post('/agents/add', [AgentController::class, 'store'])->name('agents.store');
    Route::delete('/agents/{id}', [AgentController::class, 'destroy'])->name('agents.destroy');
});

/*
|--------------------------------------------------------------------------
| Customer-Specific Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/cylinders', [CustomerController::class, 'showCylinders'])->name('dashboard.cylinder');
    Route::get('/dashboard/ordercylinder', fn() => view('dashboard.ordercylinder'))->name('dashboard.ordercylinder');
    Route::post('/order/place', [OrdersController::class, 'placeOrder'])->name('order.place');
});

/*
|--------------------------------------------------------------------------
| Customer Order Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::delete('/orders/delete', [OrdersController::class, 'destroy'])->name('orders.destroy');
    Route::delete('/orders', [OrdersController::class, 'destroy'])->name('orders.delete');
    Route::get('/orders/requests', [OrdersController::class, 'requests'])->name('orders.requests');
});

/*
|--------------------------------------------------------------------------
| Employee-Specific Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/employee/dashboard', [ManagementController::class, 'index'])->name('dashboard.employee');
    Route::get('/employee/accounts', [ManagementController::class, 'accounts'])->name('employee.accounts');
    Route::get('/employee/agents', [ManagementController::class, 'agents'])->name('employee.agents');
    Route::get('/employee/cylinders', [ManagementController::class, 'cylindersPage'])->name('employee.cylinders');
    Route::get('/employee/profile', [EmployeeController::class, 'profile'])->name('employee.profile');
    Route::get('/employee/statistics', [StatisticsController::class, 'index'])->name('employee.statistics');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
});

/*
|--------------------------------------------------------------------------
| Employee Management Routes under 'management' prefix
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::prefix('management')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('management.employees');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Admin (Manager) Settings Page Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Manager'])->group(function () {
    Route::get('/manager/global-settings', [GlobalSettingsController::class, 'index'])->name('manager.settings.index');
    Route::post('/manager/global-settings/update', [GlobalSettingsController::class, 'update'])->name('manager.settings.update');
});

/*
|--------------------------------------------------------------------------
| Agent-Specific Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/agent/dashboard', [AgentController::class, 'dashboard'])->name('agent.dashboard');
    Route::get('/agent/{id}/cylinders', [AgentController::class, 'cylindersPage'])->name('agent.cylinders');
    Route::get('/agent/customers', [AgentController::class, 'customers'])->name('agent.customers');
    Route::get('/agent/profile', [AgentController::class, 'dashboard'])->name('agent.profile');
});

/*
|--------------------------------------------------------------------------
| Warehouse Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/warehouses/{id}', [WarehouseController::class, 'show'])->name('warehouses.show');
    Route::get('/warehouses/{warehouse}/agent-cylinders', [WarehouseController::class, 'loadAgentCylinders'])->name('warehouses.agentCylinders');
    Route::get('/warehouses/{warehouse}/warehouse-cylinders', [WarehouseController::class, 'loadWarehouseCylinders'])->name('warehouses.warehouseCylinders');
    Route::post('/warehouses/{warehouse}/confirm-agent-pickup', [WarehouseController::class, 'confirmAgentPickup'])->name('warehouses.confirmAgentPickup');
});

/*
|--------------------------------------------------------------------------
| Location Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/locations/warehouses', [LocationController::class, 'getWarehouses'])->name('locations.getWarehouseLocations');
    Route::get('/locations/getWarehouses', [LocationController::class, 'getWarehouses'])->name('locations.getWarehouses');
});

/*
|--------------------------------------------------------------------------
| Cylinder Distribution to Agents
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/cylinders/warehouse/data', [CylinderDistributionController::class, 'warehouseData'])->name('cylinders.warehouse.data');
    Route::post('/cylinders/distribute/{id}', [CylinderDistributionController::class, 'distribute'])->name('cylinders.distribute');
    Route::post('/agents/{id}/update-pickup-date', [CylinderDistributionController::class, 'updatePickUpDate'])->name('agent.updatePickUpDate');
});

/*
|--------------------------------------------------------------------------
| Location & Statistics Data Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/statistics/data', [StatisticsController::class, 'getStatisticsData'])->name('statistics.data');

/*
|--------------------------------------------------------------------------
| Assign Cylinder Search Bar Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/search/customers', [SearchAutoCompleteController::class, 'searchCustomers'])->name('search.customers');
    Route::get('/search/drivers', [SearchAutoCompleteController::class, 'searchDrivers'])->name('search.drivers');
});

/*
|--------------------------------------------------------------------------
| Cylinder Delivery Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->post('/deliveries', [DeliveryController::class, 'store'])->name('deliveries.store');

/*
|--------------------------------------------------------------------------
| Delivery Approval & Review Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('/deliveries/{delivery}/approve', [DeliveryController::class, 'approve'])->name('deliveries.approve');
    Route::post('/deliveries/{delivery}/disapprove', [DeliveryController::class, 'disapprove'])->name('deliveries.disapprove');
    Route::get('/deliveries/{delivery}/review', [DeliveryController::class, 'review'])->name('deliveries.review');
    Route::get('/delivery-approval', [DeliveryController::class, 'deliveryListing'])->name('delivery.approval');
    Route::get('/delivery-index', [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::post('/deliveries/approve', [DeliveryController::class, 'updateApproval'])->name('deliveries.updateApproval');
    Route::post('/deliveries/{driver}/confirm', [DeliveryController::class, 'confirm'])->name('deliveries.confirm');
});

/*
|--------------------------------------------------------------------------
| Driver-Specific Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Profile
    Route::get('/drivers/{id}/profile', [DriversController::class, 'driverProfile'])->name('drivers.profile');

    // ── Delivery start ───────────────────────────────────────────────
    Route::post('/deliveries/{id}/start', [DeliveryController::class, 'start'])->name('deliveries.start');

    // ── Delivering page routes ───────────────────────────────────────
    Route::get('/drivers/delivering/{cylinder}', [DriversController::class, 'delivering'])->name('drivers.delivering');
    Route::post('/drivers/delivering/{cylinder}', [DriversController::class, 'storeDeliveryImage'])->name('drivers.delivering.store');

    // Driver cylinders show page routes
    Route::prefix('drivers/cylinders')->name('drivers.cylinders.')->group(function () {
        Route::get('/', [DriversController::class, 'dashboard'])->name('index');
        Route::get('/{id}', [DriversController::class, 'showCylinder'])->name('show');
    });

    // Drivers index and management
    Route::get('/drivers/index', [DriversController::class, 'index'])->name('drivers.index');
    Route::post('/management/drivers', [DriversController::class, 'store'])->name('drivers.store');
    Route::get('/management/drivers/profile/{id}', [DriversController::class, 'driverProfile'])->name('management.drivers.profile');
    Route::put('/management/drivers/profile/{id}', [DriversController::class, 'update'])->name('drivers.update');
    Route::delete('/management/drivers/{id}', [DriversController::class, 'destroy'])->name('drivers.destroy');
    Route::get('/drivers/cylinders', [DriversController::class, 'dashboard'])->name('drivers.cylinders');
});

/*
|--------------------------------------------------------------------------
| Cylinder Management (Management/Cylinders)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])
    ->prefix('management/cylinders')
    ->name('management.cylinders.')
    ->group(function () {
        Route::get('/', [ManagementController::class, 'index'])->name('index');
        Route::get('/{id}', [ManagementController::class, 'showCylinder'])->name('show');
        Route::get('/', [CylinderController::class, 'index'])->name('list');
        Route::post('/assign-cylinder/{user}', [CylinderController::class, 'assignCylinder'])->name('assign-cylinder');
    });

/*
|--------------------------------------------------------------------------
| Legacy Cylinders List Page (Management/Cylinders)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/management/cylinders', [ManagementController::class, 'cylindersPage'])->name('management.cylinders');
    Route::post('/register-modal', [RegisterController::class, 'registerModal'])->name('register.modal');
});

/*
|--------------------------------------------------------------------------
| Centralized /cylinders Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('cylinders')->name('cylinders.')->group(function () {
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
    Route::post('/{id}/return', [WarehouseController::class, 'returnToWarehouse'])->name('return');

    // Warehouse data for distribution
    Route::get('/warehouse/data', [CylinderDistributionController::class, 'warehouseData'])->name('warehouse.data');

    // Distribute cylinder
    Route::post('/distribute/{id}', [CylinderDistributionController::class, 'distribute'])->name('distribute');
});

/*
|--------------------------------------------------------------------------
| Cylinder Pickup Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('/pickups/store', [PickupController::class, 'store'])->name('pickups.store');
    Route::get('/orders/pickup', [PickupController::class, 'index'])->name('orders.pickup');
    Route::post('/orders/update-pickup', [OrdersController::class, 'updatePickup'])->name('orders.updatePickup');
    Route::post('/pickups/update', [PickupController::class, 'updatePickup'])->name('pickups.update');
});

/*
|--------------------------------------------------------------------------
| Route for Uploading NIN Image
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->post('/users/{id}/upload-nin', [UserController::class, 'uploadNin'])->name('upload.nin');

/*
|--------------------------------------------------------------------------
| Management Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::prefix('management')->name('management.')->group(function () {
        Route::get('/orders/requests', [OrdersController::class, 'requests'])->name('orders.requests');
        Route::get('/dashboard', [ManagementController::class, 'index'])->name('dashboard.management');
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');
        Route::get('/accounts', [ManagementController::class, 'accounts'])->name('accounts');
        Route::post('/accounts', [ManagementController::class, 'store']);
        Route::get('/employees', [ManagementController::class, 'employees'])->name('employees');
        Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::get('/agents', [ManagementController::class, 'agents'])->name('agents');
        Route::get('/drivers', [ManagementController::class, 'drivers'])->name('drivers');
        Route::get('/deliveries', [DeliveryController::class, 'index'])->name('deliveries');
        Route::delete('/deliveries/delete', [DeliveryController::class, 'destroy'])->name('deliveries.delete');
        Route::get('/cylinders/{id}', [ManagementController::class, 'showCylinder'])->name('cylinders.show');
    });
});

/*
|--------------------------------------------------------------------------
| Home Route After Login
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->get('/home', [HomeController::class, 'index'])->name('home.dashboard');

/*
|--------------------------------------------------------------------------
| Driver Management (Redundant with Earlier Driver-Specific)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/drivers/index', [DriversController::class, 'index'])->name('drivers.index');
    Route::post('/management/drivers', [DriversController::class, 'store'])->name('drivers.store');
    Route::get('/management/drivers/profile/{id}', [DriversController::class, 'driverProfile'])->name('management.drivers.profile');
    Route::put('/management/drivers/profile/{id}', [DriversController::class, 'update'])->name('drivers.update');
    Route::delete('/management/drivers/{id}', [DriversController::class, 'destroy'])->name('drivers.destroy');
    Route::get('/drivers/cylinders', [DriversController::class, 'dashboard'])->name('drivers.cylinders');
});

Route::get('/layouts.default-dashboard', function () {
    return redirect('/');
});

// Mark delivered (no‐reason) or show “was not delivered” form
Route::post('/deliveries/mark-delivered', [DeliveryController::class, 'markDelivered'])
    ->name('deliveries.markDelivered');

// Contact customer response
Route::post('/deliveries/contact-customer', [DeliveryController::class, 'contactCustomer'])
    ->name('deliveries.contactCustomer');

// Contact driver response
Route::post('/deliveries/contact-driver', [DeliveryController::class, 'contactDriver'])
    ->name('deliveries.contactDriver');

// Return JSON with customer contact info
Route::get('/deliveries/customer-info', [DeliveryController::class, 'customerInfo'])
    ->name('deliveries.customerInfo');

// Return JSON with driver contact info
Route::get('/deliveries/driver-info', [DeliveryController::class, 'driverInfo'])
    ->name('deliveries.driverInfo');

/*
|--------------------------------------------------------------------------
| Resource Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::resource('cylinders', CylinderController::class)->parameters(['cylinder' => 'id']);
    Route::resource('warehouses', WarehouseController::class)->except(['show']);
});

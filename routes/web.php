<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AiReplenishmentAgentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', LandingController::class);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.perform');

Route::get('/predicciones', [\App\Http\Controllers\PredictController::class, 'index'])->name('predicciones.index');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/agente-reposicion', [AiReplenishmentAgentController::class, 'index'])->name('agent.replenishment');
    Route::get('/agente-reposicion/reporte/pdf', [AiReplenishmentAgentController::class, 'report'])->name('agent.replenishment.report');
    Route::get('/agente-reposicion/estado', [AiReplenishmentAgentController::class, 'status'])->name('agent.replenishment.status');
    Route::post('/agente-reposicion/run', [AiReplenishmentAgentController::class, 'runNow'])->name('agent.replenishment.run');
    Route::post('/agente-reposicion/solicitudes/{id}/aprobar', [AiReplenishmentAgentController::class, 'approveTransferRequest'])->name('agent.replenishment.approve');
    Route::post('/agente-reposicion/solicitudes/{id}/rechazar', [AiReplenishmentAgentController::class, 'rejectTransferRequest'])->name('agent.replenishment.reject');
});

Route::prefix('dashboard')->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('dashboard.admin');
    Route::get('/admin/live-stats', [AdminController::class, 'liveStats'])->name('dashboard.admin.live-stats');
    Route::get('/vendedor', [\App\Http\Controllers\VendedorDashboardController::class, 'index'])->name('dashboard.vendedor.home');
    Route::prefix('vendedor')->name('dashboard.vendedor.')->group(function () {
        Route::get('/', [\App\Http\Controllers\VendedorDashboardController::class, 'index'])->name('home');
        Route::get('/live-stats', [\App\Http\Controllers\VendedorDashboardController::class, 'liveStats'])->name('live-stats');
        Route::get('/clientes', [\App\Http\Controllers\VendorCompanyController::class, 'index'])->name('companies');
        Route::post('/clientes', [\App\Http\Controllers\VendorCompanyController::class, 'store'])->name('companies.store');
        Route::put('/clientes/{company}', [\App\Http\Controllers\VendorCompanyController::class, 'update'])->name('companies.update');
        Route::delete('/clientes/{company}', [\App\Http\Controllers\VendorCompanyController::class, 'destroy'])->name('companies.destroy');
        Route::patch('/clientes/{company}/restore', [\App\Http\Controllers\VendorCompanyController::class, 'restore'])->name('companies.restore');
        Route::get('/clientes/reporte/pdf', [\App\Http\Controllers\VendorCompanyController::class, 'report'])->name('companies.report');
        Route::get('/ventas', [SaleController::class, 'index'])->name('sales');
        Route::get('/ventas/crear', [SaleController::class, 'vendorCreate'])->name('sales.create');
        Route::post('/ventas', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/ventas/product-lookup', [SaleController::class, 'lookupProduct'])->name('sales.lookup');
        Route::put('/ventas/{sale}', [SaleController::class, 'update'])->name('sales.update');
        Route::get('/ventas/registro', [SaleController::class, 'vendorLog'])->name('sales.log');
        Route::get('/ventas/reporte/pdf', [SaleController::class, 'vendorReport'])->name('sales.report');
        Route::get('/visitas', [\App\Http\Controllers\VendorVisitController::class, 'index'])->name('visits');
        Route::post('/visitas', [\App\Http\Controllers\VendorVisitController::class, 'store'])->name('visits.store');
        Route::put('/visitas/{visit}', [\App\Http\Controllers\VendorVisitController::class, 'update'])->name('visits.update');
        Route::delete('/visitas/{visit}', [\App\Http\Controllers\VendorVisitController::class, 'destroy'])->name('visits.destroy');
        Route::get('/cotizaciones', [QuotationController::class, 'index'])->name('quotations');
        Route::post('/cotizaciones', [QuotationController::class, 'store'])->name('quotations.store');
        Route::get('/cotizaciones/product-lookup', [QuotationController::class, 'lookupProduct'])->name('quotations.lookup');
        Route::get('/cotizaciones/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    });
    Route::get('/comprador', [\App\Http\Controllers\BuyerController::class, 'index'])->name('dashboard.comprador');
    Route::get('/almacen', \App\Http\Controllers\WarehouseDashboardController::class)->name('dashboard.almacen');
    Route::get('/almacen/live-stats', [\App\Http\Controllers\WarehouseDashboardController::class, 'liveStats'])->name('dashboard.almacen.live-stats');
    Route::get('/almacen/lotes', [\App\Http\Controllers\AlmacenLotController::class, 'index'])->name('dashboard.almacen.lots');
    Route::get('/almacen/lotes/{product}', [\App\Http\Controllers\AlmacenLotController::class, 'show'])->name('dashboard.almacen.lots.show');
    Route::get('/almacen/traspasos', [\App\Http\Controllers\AlmacenTransferController::class, 'index'])->name('dashboard.almacen.transfers');
    Route::get('/almacen/traspasos/{transfer}', [\App\Http\Controllers\AlmacenTransferController::class, 'show'])->name('dashboard.almacen.transfers.show');
    Route::post('/almacen/traspasos/{transfer}/estado', [\App\Http\Controllers\AlmacenTransferController::class, 'updateStatus'])->name('dashboard.almacen.transfers.status');
    Route::post('/almacen/traspasos/items/{item}', [\App\Http\Controllers\AlmacenTransferController::class, 'updateItem'])->name('dashboard.almacen.transfers.items.update');
    Route::get('/almacen/danos', [\App\Http\Controllers\AlmacenDamageController::class, 'index'])->name('dashboard.almacen.damages');
    Route::get('/almacen/danos/registrar', [\App\Http\Controllers\AlmacenDamageController::class, 'create'])->name('dashboard.almacen.damages.create');
    Route::post('/almacen/danos', [\App\Http\Controllers\AlmacenDamageController::class, 'store'])->name('dashboard.almacen.damages.store');
    Route::get('/almacen/danos/lookup', [\App\Http\Controllers\AlmacenDamageController::class, 'lookup'])->name('dashboard.almacen.damages.lookup');
    Route::get('/almacen/recepciones', [\App\Http\Controllers\AlmacenSaleController::class, 'index'])->name('dashboard.almacen.receptions');
    Route::get('/almacen/recepciones/{sale}', [\App\Http\Controllers\AlmacenSaleController::class, 'show'])->name('dashboard.almacen.receptions.show');
    Route::post('/almacen/recepciones/{sale}/estado', [\App\Http\Controllers\AlmacenSaleController::class, 'updateStatus'])->name('dashboard.almacen.receptions.status');
    Route::get('/agente', [\App\Http\Controllers\AdminAiController::class, 'index'])->name('dashboard.agent');
    Route::get('/agente/data', [\App\Http\Controllers\AdminAiController::class, 'data'])->name('dashboard.agent.data');
    Route::get('/agente/reporte/pdf', [\App\Http\Controllers\AdminAiController::class, 'report'])->name('dashboard.agent.report');
    Route::get('/agente/productos/{product}/detalle', [\App\Http\Controllers\AdminAiController::class, 'detail'])->name('dashboard.agent.detail');
    Route::get('/usuarios', [UserController::class, 'index'])->name('dashboard.users');
    Route::post('/usuarios', [UserController::class, 'store'])->name('dashboard.users.store');
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('dashboard.users.update');
    Route::patch('/usuarios/{user}/toggle', [UserController::class, 'toggle'])->name('dashboard.users.toggle');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('dashboard.users.destroy');
    Route::get('/usuarios/reporte/pdf', [UserController::class, 'report'])->name('dashboard.users.report');

    Route::get('/clientes', [CompanyController::class, 'index'])->name('dashboard.companies');
    Route::post('/clientes', [CompanyController::class, 'store'])->name('dashboard.companies.store');
    Route::put('/clientes/{company}', [CompanyController::class, 'update'])->name('dashboard.companies.update');
    Route::delete('/clientes/{company}', [CompanyController::class, 'destroy'])->name('dashboard.companies.destroy');
    Route::patch('/clientes/{company}/restore', [CompanyController::class, 'restore'])->name('dashboard.companies.restore');
    Route::get('/clientes/reporte/pdf', [CompanyController::class, 'report'])->name('dashboard.companies.report');

    Route::get('/productos', [ProductController::class, 'index'])->name('dashboard.products');
    Route::post('/productos', [ProductController::class, 'store'])->name('dashboard.products.store');
    Route::put('/productos/{product}', [ProductController::class, 'update'])->name('dashboard.products.update');
    Route::patch('/productos/{product}/toggle', [ProductController::class, 'toggle'])->name('dashboard.products.toggle');
    Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('dashboard.products.destroy');
    Route::get('/productos/reporte/pdf', [ProductController::class, 'report'])->name('dashboard.products.report');
    Route::get('/lotes', [\App\Http\Controllers\ProductLotController::class, 'index'])->name('dashboard.lots');
    Route::post('/lotes', [\App\Http\Controllers\ProductLotController::class, 'store'])->name('dashboard.lots.store');
    Route::put('/lotes/{lot}', [\App\Http\Controllers\ProductLotController::class, 'update'])->name('dashboard.lots.update');
    Route::post('/lotes/{lot}/adjust', [\App\Http\Controllers\ProductLotController::class, 'adjust'])->name('dashboard.lots.adjust');
    Route::get('/lotes/reporte/pdf', [\App\Http\Controllers\ProductLotController::class, 'report'])->name('dashboard.lots.report');
    Route::get('/lotes/{product}', [\App\Http\Controllers\ProductLotController::class, 'show'])->whereNumber('product')->name('dashboard.lots.show');

    Route::get('/categorias', [CategoryController::class, 'index'])->name('dashboard.categories');
    Route::post('/categorias', [CategoryController::class, 'store'])->name('dashboard.categories.store');
    Route::put('/categorias/{category}', [CategoryController::class, 'update'])->name('dashboard.categories.update');
    Route::delete('/categorias/{category}', [CategoryController::class, 'destroy'])->name('dashboard.categories.destroy');
    Route::patch('/categorias/{category}/restore', [CategoryController::class, 'restore'])->name('dashboard.categories.restore');
    Route::get('/categorias/reporte/pdf', [CategoryController::class, 'report'])->name('dashboard.categories.report');

    Route::get('/traspasos', [TransferController::class, 'index'])->name('dashboard.transfers');
    Route::post('/traspasos', [TransferController::class, 'store'])->name('dashboard.transfers.store');
    Route::get('/traspasos/product-lookup', [TransferController::class, 'lookup'])->name('dashboard.transfers.lookup');
    Route::get('/traspasos/reporte/pdf', [TransferController::class, 'report'])->name('dashboard.transfers.report');
    Route::get('/traspasos/{transfer}/reporte/pdf', [TransferController::class, 'reportSingle'])->name('dashboard.transfers.report.single');

    Route::get('/ventas', [SaleController::class, 'index'])->name('dashboard.sales');
    Route::post('/ventas', [SaleController::class, 'store'])->name('dashboard.sales.store');
    Route::get('/ventas/product-lookup', [SaleController::class, 'lookupProduct'])->name('dashboard.sales.lookup');
    Route::put('/ventas/{sale}', [SaleController::class, 'update'])->name('dashboard.sales.update');

    Route::get('/cotizaciones', [QuotationController::class, 'index'])->name('dashboard.quotations');
    Route::post('/cotizaciones', [QuotationController::class, 'store'])->name('dashboard.quotations.store');
    Route::get('/cotizaciones/product-lookup', [QuotationController::class, 'lookupProduct'])->name('dashboard.quotations.lookup');
    Route::get('/cotizaciones/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('dashboard.quotations.pdf');

    Route::get('/logs', [AuditLogController::class, 'index'])->name('dashboard.logs');
    Route::get('/logs/reporte/pdf', function (Illuminate\Http\Request $request) {
        return app(AuditLogController::class)->report($request);
    })->name('dashboard.logs.report');

    Route::get('/backups', [BackupController::class, 'index'])->name('dashboard.backups');
    Route::post('/backups', [BackupController::class, 'store'])->name('dashboard.backups.store');
    Route::put('/backups/schedule', [BackupController::class, 'updateSchedule'])->name('dashboard.backups.schedule');
    Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('dashboard.backups.download');
    Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('dashboard.backups.destroy');

    // Pasarela de pago (vista)
    Route::match(['get', 'post'], '/pago', [PaymentController::class, 'show'])->name('dashboard.payment');
    Route::post('/pago/confirmar', [PaymentController::class, 'process'])->name('dashboard.payment.process');
    Route::get('/pago/recibo/{number}', [PaymentController::class, 'receipt'])->name('dashboard.payment.receipt');
    Route::get('/pago/recibo/{number}/descargar', [PaymentController::class, 'download'])->name('dashboard.payment.receipt.download');
});

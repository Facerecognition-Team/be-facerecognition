<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('send-image', [FaceRecognitionController::class, 'sendImage']);
Route::post('/proses_absen', [FaceRecognitionController::class, 'prosesAbsen']);
Route::post('/upload_employee', [PegawaiController::class, 'store']);
Route::post('/add_data_pegawai', [PegawaiController::class, 'add_pegawai']);
Route::get('/get-data-pegawai-foto', [PegawaiController::class, 'get_data_pegawai_foto']); // Menambahkan route untuk mengambil semua pegawai
Route::get('/get-data-pegawai', [PegawaiController::class, 'get_data_pegawai']);
Route::get('/log-absen', [FaceRecognitionController::class, 'getLogAbsen']);
Route::post('/update_dataset', [PegawaiController::class, 'update_data_pegawai']);
Route::post('api/upload_face', [PegawaiController::class, 'uploadFace']);
Route::post('/store_dataset', [PegawaiController::class, 'storeDataset']);

Route::post('/admin/login', [AdminController::class, 'loginn']);
Route::post('/admin/logout', [AdminController::class, 'logout']);
Route::get('/log-absen/chart', [AdminController::class, 'getAttendanceChartData']);
Route::get('/log-absen/chart/employee', [AdminController::class, 'getEmployeeChartData']);



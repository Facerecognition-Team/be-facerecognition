<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Models\Absen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function loginn(Request $request)
    {
        // $credentials = $request->only('username', 'password');

        // Log::info('Credentials:', $credentials);
        
        // if (Auth::guard('admin')->attempt($credentials)) {
        //     return response()->json(['success' => true, 'message' => 'Login Berhasil']);
        // } else {
        //     return response()->json(['success' => false, 'message' => 'Username atau Password salah']);
        // }

        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $pelanggan = Admin::where('username', $request->username)->first();

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terdaftar!',
            ], 404);
        }else{

        // Verifikasi password
        if ($request->password == $pelanggan->password) {
            // Periksa apakah pengguna sudah login di perangkat lain
            if ($pelanggan->tokens()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengguna sudah masuk di perangkat lain.',
                ], 422);
            } else {

                // Hapus semua token lama (jika ingin mendukung satu perangkat saja)
                $pelanggan->tokens()->delete();

                // Buat token untuk perangkat saat ini
                $token = $pelanggan->createToken('myappToken')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'token' => $token,
                    'datauser' => $pelanggan,
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Kombinasi email dan password tidak valid!',
            ], 422);
        }
        }
    }

    
    public function logout(Request $request)
    {
        $admin = $request->user(); // Mendapatkan user dari request dengan token
    
        if ($admin) {
            // Hapus semua token milik user
            $admin->tokens()->delete();
    
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil.',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal logout. Pengguna tidak ditemukan.',
            ], 401);
        }
    }

    public function getAttendanceChartData()
{
    // Ambil data log absensi
    $logs = Absen::with('pegawai')->get();

    // Kelompokkan berdasarkan tanggal
    $groupedLogs = $logs->groupBy('tanggal_absen');

    $labels = [];
    $presentData = [];
    $absentData = [];

    foreach ($groupedLogs as $date => $logsOnDate) {
        $labels[] = $date;
        $presentCount = $logsOnDate->whereNotNull('waktu_masuk')->count();
        $absentCount = $logsOnDate->whereNull('waktu_masuk')->count();

        $presentData[] = $presentCount;
        $absentData[] = $absentCount;
    }

    return response()->json([
        'success' => true,
        'chartData' => [
            'labels' => $labels,
            'presentData' => $presentData,
            'absentData' => $absentData,
        ],
    ]);
}

public function getEmployeeChartData(Request $request)
{
    $employeeId = $request->query('employee_id');
    $startDate = $request->query('start_date');
    $endDate = $request->query('end_date');

    if (!$employeeId) {
        return response()->json([
            'success' => false,
            'message' => 'Pegawai tidak ditemukan.',
        ], 400);
    }

    $query = Absen::where('id_pegawai', $employeeId);

    // Filter berdasarkan rentang tanggal jika diberikan
    if ($startDate && $endDate) {
        $query->whereBetween('tanggal_absen', [$startDate, $endDate]);
    }

    $logs = $query->get();

    // Kelompokkan berdasarkan tanggal
    $groupedLogs = $logs->groupBy('tanggal_absen');

    $labels = [];
    $presentData = [];
    $absentData = [];

    foreach ($groupedLogs as $date => $logsOnDate) {
        $labels[] = $date;
        $presentCount = $logsOnDate->whereNotNull('waktu_masuk')->count();
        $absentCount = $logsOnDate->whereNull('waktu_masuk')->count();

        $presentData[] = $presentCount;
        $absentData[] = $absentCount;
    }

    return response()->json([
        'success' => true,
        'chartData' => [
            'labels' => $labels,
            'presentData' => $presentData,
            'absentData' => $absentData,
        ],
    ]);
}

}

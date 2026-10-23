<?php

use Illuminate\Support\Facades\Route;
use Kreait\Laravel\Firebase\Facades\Firebase;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-firebase', function () {
    abort_unless(app()->environment('local'), 404);

    try {
        $database = Firebase::database();

        $reference = $database->getReference('connection_test');

        $reference->set([
            'connected' => true,
            'message' => 'Laravel berhasil terhubung ke Firebase',
            'checkedAt' => now()->timestamp,
        ]);

        return response()->json([
            'success' => true,
            'data' => $reference->getValue(),
        ]);
    } catch (\Throwable $e) {
        report($e);

        return response()->json([
            'success' => false,
            'message' => 'Koneksi Firebase gagal.',
            'error' => config('app.debug')
                ? $e->getMessage()
                : 'Periksa log Laravel.',
        ], 500);
    }
});

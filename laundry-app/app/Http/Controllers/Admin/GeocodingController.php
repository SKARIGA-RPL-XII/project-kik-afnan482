<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodingController extends Controller
{
    /**
     * Reverse geocode menggunakan Nominatim OpenStreetMap
     * Proxy untuk menghindari CORS issues
     */
    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        try {
            // Call Nominatim API dari server (tidak ada CORS di server-side)
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'LaundryApp/1.0 (Laravel)',
                    'Accept' => 'application/json',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lng,
                    'addressdetails' => 1,
                    'accept-language' => 'id',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'address' => $data['display_name'] ?? "Lat: {$lat}, Lng: {$lng}",
                    'data' => $data,
                ]);
            }

            // Fallback jika API gagal
            return response()->json([
                'success' => true,
                'address' => "Lat: " . number_format($lat, 6) . ", Lng: " . number_format($lng, 6),
                'data' => null,
            ]);

        } catch (\Exception $e) {
            // Return coordinates jika error
            return response()->json([
                'success' => true,
                'address' => "Lat: " . number_format($lat, 6) . ", Lng: " . number_format($lng, 6),
                'data' => null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\QrCodes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class QRController extends Controller
{
    /**
     * Display a listing of the QR codes.
     */
    public function index()
    {
        $qrCodes = QrCodes::latest()->get();

        return Inertia::render('AdminSide/Qrcodes/Index', [
            'qrCodes' => $qrCodes
        ]);
    }

    /**
     * Store a newly created QR code in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'qr_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = $request->file('qr_path')->store('qr-codes', 'public');

        QrCodes::create([
            'qr_path' => $path,
        ]);

        return redirect()->route('qr-codes.index')->with('success', 'QR code uploaded successfully');
    }

    /**
     * Remove the specified QR code from storage.
     */
    public function destroy($id)
    {
        $qrCode = QrCodes::findOrFail($id);

        // Delete the file from storage
        if (Storage::disk('public')->exists($qrCode->qr_path)) {
            Storage::disk('public')->delete($qrCode->qr_path);
        }

        $qrCode->delete();

        return redirect()->route('qr-codes.index')->with('success', 'QR code deleted successfully');
    }

    /**
     * Get a random QR code for payment.
     */
    public function getRandomQrCode()
    {
        $qrCode = QrCodes::inRandomOrder()->first();

        if (!$qrCode) {
            return null;
        }

        return $qrCode->qr_path;
    }
}

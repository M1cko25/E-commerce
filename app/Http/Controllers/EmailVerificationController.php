<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationController extends Controller
{
    public function notice() {
        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
            'customer' => Auth::guard('customer')->user(),
        ]);
    }

    public function verify(EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('customer.register.process');
    }

    public function send(Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent');
    }

    /**
     * Handle manual verification when the signed URL approach fails
     */
    public function manualVerify($id) {
        // Ensure the user is authenticated
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login')
                ->with('error', 'You must be logged in to verify your email.');
        }

        $user = Auth::guard('customer')->user();

        // Check that the logged in user matches the ID being verified
        if ($user->id != $id) {
            return redirect()->route('verification.notice')
                ->with('error', 'Invalid user ID for verification.');
        }

        // Mark email as verified if not already verified
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('customer.register.process')
            ->with('success', 'Your email has been verified successfully!');
    }
}

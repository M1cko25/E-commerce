<?php

namespace App\Http\Controllers\Client;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Http\Middleware\EnsureCustomerIsAuthenticated;

class ClientProfileController extends Controller
{

    public function index()
    {
         // Check if user is authenticated


        $customer = Auth::guard('customer')->user();
        $addresses = $customer->addresses;
        $defaultAddress = $addresses->where('id', $customer->default_address_id)->first();
        return Inertia::render('ClientSide/Customer/Profile', [
            'customer' => $customer,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * Show the security settings page.
     */
    public function security()
    {
        $customer = Auth::guard('customer')->user();
        $addresses = $customer->addresses;
        $defaultAddress = $addresses->where('id', $customer->default_address_id)->first();

        return Inertia::render('ClientSide/Customer/Security', [
            'customer' => $customer,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * Update the customer's password.
     */
    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:customer'],
            'password' => ['required', 'confirmed', 'min:8', 'different:current_password', 'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/', Password::defaults()],
        ]);

        $customer->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully');
    }
}

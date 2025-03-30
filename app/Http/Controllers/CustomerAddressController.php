<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddresses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerAddressController extends Controller
{
    /**
     * Display a listing of addresses.
     */
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $addresses = $customer->addresses;

        return Inertia::render('ClientSide/Customer/Addresses', [
            'customer' => $customer,
            'addresses' => $addresses,
        ]);
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'complete_address' => 'required|string|max:500',
            'default_address' => 'boolean',
        ]);

        // If this is the first address or set as default
        if ($validated['default_address'] || $customer->addresses->count() === 0) {
            // Remove default flag from all other addresses
            CustomerAddresses::where('customer_id', $customer->id)
                ->update(['default_address' => false]);

            $address = CustomerAddresses::create([
                'customer_id' => $customer->id,
                'default_address' => true,
                ...$validated
            ]);

            // Update customer's default address ID
            $customer->default_address_id = $address->id;
            $customer->save();
        } else {
            CustomerAddresses::create([
                'customer_id' => $customer->id,
                'default_address' => false,
                ...$validated
            ]);
        }

        return redirect()->route('customer.addresses')
            ->with('success', 'Address added successfully');
    }

    /**
     * Update the specified address in storage.
     */
    public function update(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();
        $address = CustomerAddresses::where('customer_id', $customer->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:15',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'complete_address' => 'required|string|max:500',
            'default_address' => 'boolean',
        ]);

        // Handle default address changes
        if ($validated['default_address'] && !$address->default_address) {
            // If setting this address as default
            CustomerAddresses::where('customer_id', $customer->id)
                ->update(['default_address' => false]);

            // Update customer's default address ID
            $customer->default_address_id = $address->id;
            $customer->save();
        }

        $address->update($validated);

        return redirect()->route('customer.addresses')
            ->with('success', 'Address updated successfully');
    }

    /**
     * Set an address as the default.
     */
    public function setDefault($id)
    {
        $customer = Auth::guard('customer')->user();
        $address = CustomerAddresses::where('customer_id', $customer->id)
            ->findOrFail($id);

        // Remove default from all customer addresses
        CustomerAddresses::where('customer_id', $customer->id)
            ->update(['default_address' => false]);

        // Set this address as default
        $address->default_address = true;
        $address->save();

        // Update customer default address ID
        $customer->default_address_id = $address->id;
        $customer->save();

        return redirect()->back()
            ->with('success', 'Default address updated');
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy($id)
    {
        $customer = Auth::guard('customer')->user();
        $address = CustomerAddresses::where('customer_id', $customer->id)
            ->findOrFail($id);

        // Prevent deletion of default address
        if ($address->default_address) {
            return redirect()->back()
                ->with('error', 'Cannot delete default address. Please set another address as default first.');
        }

        $address->delete();

        return redirect()->route('customer.addresses')
            ->with('success', 'Address deleted successfully');
    }
}

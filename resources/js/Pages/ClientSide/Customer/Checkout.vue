<template>
  <div class="min-h-screen bg-gray-50">
    <NavLink />

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Checkout Process</h1>
        <Link
          href="/cart"
          class="flex items-center text-sm text-navy-600 hover:text-navy-700"
        >
          <ChevronLeft class="h-4 w-4 mr-1" />
          BACK TO CART
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Checkout Form -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Delivery Information -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex justify-between items-start mb-4">
              <!-- Left Section -->
              <div class="flex items-start space-x-2">
                <MapPin class="h-5 w-5 text-gray-500 mt-0.5" />
                <div>
                  <h2 class="font-semibold text-gray-900">Delivery Information</h2>
                  <div class="mt-2 text-sm text-gray-600">
                    <p class="font-semibold text-gray-900">
                      {{ selectedAddress ? `${selectedAddress.first_name} ${selectedAddress.last_name}` : `${customer.first_name} ${customer.last_name}` }}
                    </p>
                    <p class="text-gray-900">
                      {{ selectedAddress ? selectedAddress.phone_number : customer.phone }}
                    </p>
                    <p>
                      {{ customer.email }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Right Section (Addresses aligned to flex-end) -->
              <div class="text-right text-sm">
                <button
                  @click="showAddressSelectionModal = true"
                  class="mt-2 mb-4 px-4 py-1 text-sm bg-navy-900 text-white rounded hover:bg-navy-800"
                >
                  {{ selectedAddress ? 'Change' : 'Select' }} Address
                </button>
                <p v-if="selectedAddress" class="text-gray-900">
                  {{ selectedAddress.complete_address }}
                </p>
                <p v-if="selectedAddress" class="text-gray-900">
                  {{ selectedAddress.province }}, {{ selectedAddress.city }},
                  {{ selectedAddress.zip_code }}
                </p>
                <p v-else class="text-yellow-600 font-medium">
                  Please select a delivery address
                </p>
              </div>
            </div>
          </div>

          <!-- Delivery Method -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-start space-x-2 mb-4">
              <Truck class="h-5 w-5 text-gray-500 mt-0.5" />
              <h2 class="font-semibold text-gray-900">Delivery Method</h2>
            </div>

            <div class="space-y-4">
              <label class="flex items-center space-x-3">
                <input
                  type="radio"
                  v-model="deliveryMethod"
                  value="delivery"
                  class="form-radio text-navy-600 focus:ring-navy-500"
                />
                <span>Address Delivery</span>
              </label>

              <label class="flex items-center space-x-3">
                <input
                  type="radio"
                  v-model="deliveryMethod"
                  value="pickup"
                  class="form-radio text-navy-600 focus:ring-navy-500"
                />
                <span>In-Store Pickup</span>
              </label>

              <!-- Pickup Details -->
              <div v-if="deliveryMethod === 'pickup'" class="mt-4 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Pickup Date
                  </label>
                  <div class="relative">
                    <input
                      type="date"
                      v-model="pickupDate"
                      class="w-full pl-3 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                    />
                    <Calendar
                      class="absolute right-3 top-2.5 h-5 w-5 text-gray-400"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Pickup Time
                  </label>
                  <div class="relative">
                    <input
                      type="time"
                      v-model="pickupTime"
                      class="w-full pl-3 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                    />
                    <Clock class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-start space-x-2 mb-4">
              <CreditCard class="h-5 w-5 text-gray-500 mt-0.5" />
              <h2 class="font-semibold text-gray-900">Payment Method</h2>
            </div>

            <div class="space-y-4">
              <label class="flex items-center space-x-3">
                <input
                  type="radio"
                  v-model="paymentMethod"
                  value="gcash"
                  class="form-radio text-navy-600 focus:ring-navy-500"
                />
                <span>Gcash</span>
              </label>

              <label
                class="flex items-center space-x-3"
              >
                <input
                  type="radio"
                  v-model="paymentMethod"
                  value="cash"
                  class="form-radio text-navy-600 focus:ring-navy-500"
                />
                <span>Cash on Delivery</span>
              </label>
            </div>
          </div>

          <!-- Notes -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2"> Notes </label>
            <textarea
              v-model="notes"
              rows="3"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
              placeholder="Add any special instructions..."
            ></textarea>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm p-6 sticky top-4">
            <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

            <div class="space-y-4">
              <div v-for="item in items" :key="item.id" class="flex space-x-4">
                <img
                  :src="getImageUrl(item.image)"
                  :alt="item.name"
                  class="w-16 h-16 object-cover rounded-lg"
                />
                <div class="flex-1">
                  <h3 class="text-sm font-medium text-gray-900">{{ item.name }}</h3>
                  <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
                  <p class="text-sm font-medium text-navy-600">₱{{ item.price }}</p>
                </div>
              </div>
            </div>

            <div class="border-t mt-6 pt-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-medium">₱{{ summary.subtotal }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Delivery Fee</span>
                <span class="font-medium">₱{{ summary.shipping }}</span>
              </div>
              <div class="flex justify-between text-base font-semibold pt-2">
                <span>Total Amount</span>
                <span class="text-navy-600">₱{{ summary.total }}</span>
              </div>
            </div>

            <div class="mt-6 space-y-4">
              <label class="flex items-center space-x-2 text-sm">
                <input
                  type="checkbox"
                  v-model="agreeToTerms"
                  class="form-checkbox text-navy-600 focus:ring-navy-500"
                />
                <span class="text-gray-600">I agree to the Terms and Conditions</span>
              </label>

              <button
                @click="completePurchase"
                :disabled="!agreeToTerms"
                class="w-full py-3 bg-navy-900 text-white rounded-lg font-medium hover:bg-navy-800 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                COMPLETE PURCHASE
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Address Modal -->
    <TransitionRoot appear :show="showEditModal" as="template">
      <Dialog as="div" @close="showEditModal = false" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel
                class="w-full max-w-md transform overflow-hidden rounded-lg bg-white p-6 text-left align-middle shadow-xl transition-all"
              >
                <DialogTitle as="h3" class="text-lg font-medium text-gray-900 mb-4">
                  Edit Address
                </DialogTitle>

                <form @submit.prevent="updateAddress" class="space-y-4">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        First Name
                      </label>
                      <input
                        v-model="customer.first_name"
                        type="text"
                        required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Last Name
                      </label>
                      <input
                        v-model="customer.last_name"
                        type="text"
                        required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Province
                    </label>
                    <select
                      v-model="customer.address.province"
                      required
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                    >
                      <option value="metro-manila">Metro Manila</option>
                      <!-- Add more provinces -->
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      City
                    </label>
                    <select
                      v-model="customer.address.city"
                      required
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                    >
                      <option value="caloocan">Caloocan City</option>
                      <!-- Add more cities -->
                    </select>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Zip Code
                    </label>
                    <input
                      v-model="customer.address.zip_code"
                      type="text"
                      required
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Complete Address
                    </label>
                    <textarea
                      v-model="customer.address.complete_address"
                      rows="3"
                      required
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                    ></textarea>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Phone Number
                    </label>
                    <input
                      v-model="customer.phone"
                      type="tel"
                      required
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                    />
                  </div>

                  <div class="flex justify-end space-x-3 mt-6">
                    <button
                      type="button"
                      @click="showEditModal = false"
                      class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="px-4 py-2 bg-navy-900 text-white rounded-lg hover:bg-navy-800"
                    >
                      Update
                    </button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Address Selection Modal -->
    <TransitionRoot appear :show="showAddressSelectionModal" as="template">
      <Dialog as="div" @close="showAddressSelectionModal = false" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel
                class="w-full max-w-md transform overflow-hidden rounded-lg bg-white p-6 text-left align-middle shadow-xl transition-all"
              >
                <DialogTitle as="h3" class="text-lg font-medium text-gray-900 mb-4">
                  Select Delivery Address
                </DialogTitle>

                <div class="max-h-80 overflow-y-auto">
                  <div v-if="addresses && addresses.length > 0">
                    <div
                      v-for="address in addresses"
                      :key="address.id"
                      @click="selectAddress(address)"
                      class="border rounded-lg p-4 mb-3 hover:border-navy-600 cursor-pointer"
                      :class="{ 'border-navy-600 bg-navy-50': selectedAddressId === address.id }"
                    >
                      <div class="flex justify-between items-start">
                        <div>
                          <h3 class="font-medium">{{ address.first_name }} {{ address.last_name }}</h3>
                          <p class="text-sm text-gray-600">{{ address.phone_number }}</p>
                          <div class="text-sm text-gray-700 mt-1">
                            <p>{{ address.complete_address }}</p>
                            <p>{{ address.city }}, {{ address.province }} {{ address.zip_code }}</p>
                          </div>
                        </div>
                        <div v-if="address.default_address" class="bg-navy-100 text-navy-700 text-xs px-2 py-1 rounded-full">
                          Default
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-8">
                    <MapPin class="h-12 w-12 mx-auto text-gray-300 mb-3" />
                    <h3 class="text-lg font-medium text-gray-900">No addresses found</h3>
                    <p class="text-gray-500 mt-1 mb-4">Add a new shipping address to continue.</p>
                    <Link
                      :href="route('customer.addresses')"
                      class="inline-block px-4 py-2 bg-navy-600 text-white rounded-lg hover:bg-navy-700"
                    >
                      Add New Address
                    </Link>
                  </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                  <button
                    @click="showAddressSelectionModal = false"
                    class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50"
                  >
                    Cancel
                  </button>
                  <button
                    @click="confirmAddressSelection"
                    :disabled="!selectedAddressId"
                    class="px-4 py-2 bg-navy-600 text-white rounded-lg hover:bg-navy-700 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Use Selected Address
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
          <div class="mb-4 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">
              {{ paymentMethod === 'cash' ? 'Confirm Order' : 'Confirm Payment' }}
            </h3>
            <button
              @click="showPaymentModal = false"
              class="text-gray-400 hover:text-gray-500"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Order Summary -->
          <div class="mb-6 border rounded-lg p-4 bg-gray-50">
            <h4 class="font-medium text-gray-800 mb-2">Order Summary</h4>

            <!-- Item list -->
            <div class="max-h-40 overflow-y-auto mb-3">
              <div v-for="item in items" :key="item.id" class="flex py-2 border-b last:border-b-0">
                <div class="w-12 h-12 flex-shrink-0">
                  <img
                    :src="getImageUrl(item.image)"
                    :alt="item.name"
                    class="w-full h-full object-cover rounded"
                  />
                </div>
                <div class="ml-3 flex-1">
                  <div class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</div>
                  <div class="text-xs text-gray-500">
                    Qty: {{ item.quantity }} × ₱{{ item.price }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Price breakdown -->
            <div class="space-y-1 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium">₱{{ summary.subtotal }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Delivery Fee:</span>
                <span class="font-medium">₱{{ summary.shipping }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t text-base font-semibold">
                <span>Total Amount:</span>
                <span class="text-navy-600">₱{{ summary.total }}</span>
              </div>
            </div>
          </div>

          <!-- Delivery Address for COD -->
          <div v-if="paymentMethod === 'cash'" class="mb-6 border rounded-lg p-4">
            <h4 class="font-medium text-gray-800 mb-2">Delivery Address</h4>
            <p class="text-sm text-gray-600">
              {{ customer.first_name }} {{ customer.last_name }}<br>
              {{ customer.address?.complete_address }}<br>
              {{ customer.address?.city }}, {{ customer.address?.province }}, {{ customer.address?.zip_code }}
            </p>
          </div>

          <!-- COD Notice -->
          <div v-if="paymentMethod === 'cash'" class="mb-6 bg-blue-50 p-4 rounded-lg">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-blue-700">
                  Payment will be collected upon delivery. Please have the exact amount ready.
                </p>
              </div>
            </div>
          </div>

          <!-- GCash Notice -->
          <div v-if="paymentMethod === 'gcash'" class="mb-6 bg-blue-50 p-4 rounded-lg">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-blue-700">
                  You will be redirected to GCash to complete your payment.
                </p>
              </div>
            </div>
          </div>

          <!-- Action buttons -->
          <div class="flex justify-end space-x-3">
            <button
              @click="showPaymentModal = false"
              class="px-4 py-2 bg-gray-200 rounded-lg text-gray-600 hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              @click="proceedToPayment"
              :disabled="isLoading"
              class="flex items-center justify-center w-2/3 bg-navy-600 text-white py-3 rounded-lg font-medium hover:bg-navy-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-if="isLoading">Processing...</span>
              <span v-else>{{ paymentMethod === 'cash' ? 'Place Order' : 'Proceed to Payment' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-full max-w-md text-center">
          <div class="mb-4">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
              <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="mt-3 text-lg font-medium text-gray-900">Order Placed Successfully!</h3>
            <p class="mt-2 text-sm text-gray-500">
              Your order has been placed successfully. You will receive a confirmation soon.
            </p>
          </div>

          <div class="mt-6 flex justify-center space-x-3">
            <Link
              :href="route('product.list')"
              class="px-4 py-2 bg-gray-200 rounded-lg text-gray-600 hover:bg-gray-300"
            >
              Continue Shopping
            </Link>
            <Link
              :href="route('customer.myOrders')"
              class="px-4 py-2 bg-navy-600 text-white rounded-lg hover:bg-navy-700"
            >
              View My Orders
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from "@headlessui/vue";
import {
  ShoppingCart,
  User,
  MapPin,
  Truck,
  CreditCard,
  Calendar,
  Clock,
  ChevronLeft,
} from "lucide-vue-next";
import NavLink from "../../../Components/NavLink.vue";

const props = defineProps({
  items: {
    type: Object,
    required: true,
  },
  customer: {
    type: Object,
    required: true,
  },
  summary: {
    type: Object,
    required: true,
  },
  addresses: {
    type: Array,
    required: true,
  },
});

// Form state
const showEditModal = ref(false);
const showAddressSelectionModal = ref(false);

const getImageUrl = (imagePath) => {
  // if (!imagePath) return "/storage/default2.jpg";
  return imagePath.startsWith("http") ? imagePath : `/storage/${imagePath}`;
};

// Delivery and payment methods
const deliveryMethod = ref("delivery");
const paymentMethod = ref("gcash");
const pickupDate = ref("");
const pickupTime = ref("");
const notes = ref("");
const agreeToTerms = ref(false);
const showPaymentModal = ref(false);
const isLoading = ref(false);
const showSuccessModal = ref(false);
const selectedAddressId = ref(null);

// Computed values
const subtotal = computed(() => {
  return props.items.reduce((total, item) => total + item.price * item.quantity, 0);
});

const deliveryFee = computed(() => {
  return deliveryMethod.value === "delivery" ? 14.0 : 0;
});

const total = computed(() => {
  return subtotal.value + deliveryFee.value;
});

const selectedAddress = computed(() => {
  if (!selectedAddressId.value) {
    return props.customer.address; // Fall back to default address from props
  }
  return props.addresses.find(address => address.id === selectedAddressId.value);
});

// Methods

const updateAddress = () => {
  // Implement address update logic
  showEditModal.value = false;
};

const completePurchase = () => {
  if (!agreeToTerms.value) return;
  if (!selectedAddress.value && deliveryMethod.value === 'delivery') {
    alert('Please select a delivery address before proceeding');
    return;
  }
  showPaymentModal.value = true;
};

// Proceed with the payment and order creation
const proceedToPayment = () => {
  if (paymentMethod.value === 'gcash') {
    isLoading.value = true;

    // Create the shipping address string using the selected address
    const shippingAddress = selectedAddress.value ?
      `${selectedAddress.value.complete_address}, ${selectedAddress.value.city}, ${selectedAddress.value.province}, ${selectedAddress.value.zip_code}` : '';

    // Make a GET request to our backend to create the payment source
    router.get(route('customer.payment'), {
      notes: notes.value,  // Add notes from the form
      payment_method: paymentMethod.value,
      shipping_address: shippingAddress,  // Add formatted shipping address
      address_id: selectedAddress.value?.id // Send the selected address ID
    }, {
      preserveState: true,
      onSuccess: () => {
        isLoading.value = false;
        showPaymentModal.value = false;
        showSuccessModal.value = true;
      },
      onError: (error) => {
        isLoading.value = false;
        console.log(error);
        alert('Failed to initialize payment. Please try again.');
      }
    });
  } else {
    isLoading.value = true;

    // Create the shipping address string using the selected address
    const shippingAddress = selectedAddress.value ?
      `${selectedAddress.value.complete_address}, ${selectedAddress.value.city}, ${selectedAddress.value.province}, ${selectedAddress.value.zip_code}` : '';

    // Process Cash on Delivery order
    router.post(route('customer.processCod'), {
      notes: notes.value,
      shipping_address: shippingAddress,
      address_id: selectedAddress.value?.id,
      delivery_method: deliveryMethod.value,
      payment_method: paymentMethod.value
    }, {
      onSuccess: () => {
        isLoading.value = false;
        showPaymentModal.value = false;
        showSuccessModal.value = true;
      },
      onError: (errors) => {
        isLoading.value = false;
        alert('Failed to process your order: ' + Object.values(errors).flat().join('\n'));
      }
    });
  }
};

const selectAddress = (address) => {
  selectedAddressId.value = address.id;
};

const confirmAddressSelection = () => {
  // Implement logic to confirm address selection
  showAddressSelectionModal.value = false;
};

watch(deliveryMethod, (newValue) => {
  router.get(route('customer.checkout'), {
    delivery_method: newValue
  }, {
    preserveState: true,
    preserveScroll: true
  });
});
</script>

<style scoped>
.bg-navy-800 {
  background-color: #0d1147;
}

.bg-navy-900 {
  background-color: #070b2e;
}

.text-navy-600 {
  color: #1a237e;
}

.hover\:bg-navy-800:hover {
  background-color: #0d1147;
}

.focus\:ring-navy-500:focus {
  --tw-ring-color: #1e2b8f;
}
</style>

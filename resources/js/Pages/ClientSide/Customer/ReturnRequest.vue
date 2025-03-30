<template>
  <div class="min-h-screen bg-gray-50">
    <NavLink />
    <Toast ref="toast" />
    <div class="container mx-auto px-4 py-8">
      <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar -->
        <ProfileSidebar :customer="customer" />

        <!-- Main Content -->
        <div class="flex-1">
          <!-- Return Request Form -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <Link
                  :href="route('customer.orderDetails', order.reference_number)"
                  class="flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-sm hover:bg-gray-50"
                >
                  <ChevronLeft class="h-5 w-5 text-gray-600" />
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Return Request</h1>
              </div>
              <div
                class="px-4 py-2 rounded-full text-sm font-medium bg-yellow-50 text-yellow-700 border border-yellow-200"
              >
                Order: {{ order.reference_number }}
              </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
              <div class="flex">
                <div class="flex-shrink-0">
                  <Info class="h-5 w-5 text-blue-400" />
                </div>
                <div class="ml-3">
                  <p class="text-sm text-blue-700">
                    You can only return items within 7 days of delivery. Please make sure all items are in their original condition with tags and packaging.
                  </p>
                </div>
              </div>
            </div>

            <form @submit.prevent="submitRequest">
              <!-- Return Type Selection -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Return Type</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div
                    class="border rounded-lg p-4 cursor-pointer"
                    :class="{'border-blue-500 bg-blue-50': returnType === 'return', 'border-gray-200': returnType !== 'return'}"
                    @click="returnType = 'return'"
                  >
                    <div class="flex items-start">
                      <div class="flex items-center h-5">
                        <input
                          type="radio"
                          name="return_type"
                          id="return"
                          value="return"
                          v-model="returnType"
                          class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                        />
                      </div>
                      <div class="ml-3">
                        <label for="return" class="font-medium text-gray-900">Return Items</label>
                        <p class="text-gray-500 text-sm">
                          Return items for a full refund. Your items will be inspected before processing the refund.
                        </p>
                      </div>
                    </div>
                  </div>

                  <div
                    class="border rounded-lg p-4 cursor-pointer"
                    :class="{'border-blue-500 bg-blue-50': returnType === 'refund', 'border-gray-200': returnType !== 'refund'}"
                    @click="returnType = 'refund'"
                  >
                    <div class="flex items-start">
                      <div class="flex items-center h-5">
                        <input
                          type="radio"
                          name="return_type"
                          id="refund"
                          value="refund"
                          v-model="returnType"
                          class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                        />
                      </div>
                      <div class="ml-3">
                        <label for="refund" class="font-medium text-gray-900">Request Refund Only</label>
                        <p class="text-gray-500 text-sm">
                          Request a refund without returning the items. This requires approval from our team.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-if="errors.return_type" class="text-red-500 text-sm mt-1">{{ errors.return_type }}</div>
              </div>

              <!-- Reason for Return -->
              <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Return</label>
                <textarea
                  id="reason"
                  v-model="reason"
                  rows="4"
                  class="shadow-sm block w-full focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md"
                  placeholder="Please provide details about your return request..."
                ></textarea>
                <div v-if="errors.reason" class="text-red-500 text-sm mt-1">{{ errors.reason }}</div>
              </div>

              <!-- Items to Return -->
              <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Order Items</h3>
                <div class="border rounded-lg divide-y">
                  <div v-for="item in order.items" :key="item.id" class="p-4">
                    <div class="flex items-start gap-4">
                      <img
                        :src="item.image ? '/storage/' + item.image : '/storage/default.jpg'"
                        :alt="item.name"
                        class="w-20 h-20 rounded-lg object-cover bg-gray-100"
                      />
                      <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-gray-900">{{ item.name }}</h4>
                        <p class="mt-1 text-sm text-gray-500">Quantity: {{ item.quantity }}</p>
                        <p class="mt-1 text-sm font-medium text-gray-900">₱{{ formatPrice(item.unit_amount) }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Submit Buttons -->
              <div class="flex justify-end space-x-4">
                <Link
                  :href="route('customer.orderDetails', order.reference_number)"
                  class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                  Cancel
                </Link>
                <button
                  type="submit"
                  class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-navy-600 hover:bg-navy-700"
                  :disabled="processing"
                >
                  <span v-if="processing">Processing...</span>
                  <span v-else>Submit Return Request</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Info } from 'lucide-vue-next';
import NavLink from '../../../Components/NavLink.vue';
import ProfileSidebar from '../../../Components/ProfileSidebar.vue';
import Toast from '../../../Components/Toast.vue';

const props = defineProps({
  order: Object,
  customer: Object,
});

const toast = ref(null);
const returnType = ref('return');
const reason = ref('');
const processing = ref(false);
const errors = ref({});

const formatPrice = (price) => {
  return Number(price).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const submitRequest = () => {
  errors.value = {};

  // Basic validation
  if (!returnType.value) {
    errors.value.return_type = 'Please select a return type';
  }

  if (!reason.value.trim()) {
    errors.value.reason = 'Please provide a reason for your return';
  }

  if (Object.keys(errors.value).length > 0) {
    return;
  }

  processing.value = true;

  // Submit the return request
  useForm({
    reason: reason.value,
    return_type: returnType.value,
  }).post(route('customer.submitReturn', props.order.reference_number), {
    onSuccess: () => {
      processing.value = false;
      // Success is handled by redirect with flash message
    },
    onError: (err) => {
      processing.value = false;
      errors.value = err;
      toast.value.addToast('There was an error submitting your request. Please try again.', 'error');
    },
  });
};
</script>

<style scoped>
.bg-navy-600 {
  background-color: #1a237e;
}

.bg-navy-700 {
  background-color: #151b60;
}

.hover\:bg-navy-700:hover {
  background-color: #151b60;
}
</style>

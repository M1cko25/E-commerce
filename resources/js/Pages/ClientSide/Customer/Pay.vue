<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import NavLink from '../../../Components/NavLink.vue';
import Footer from '../../../Components/Footer.vue';
import Toast from '../../../Components/Toast.vue';
import { AlertCircleIcon, ArrowLeftIcon, CopyIcon, CheckIcon } from 'lucide-vue-next';

const props = defineProps({
  order: Object,
  qrCode: String,
});

const toast = ref(null);
const paymentRef = ref('');
const loading = ref(false);
const refError = ref('');
const copied = ref(false);

// Form validation
const isRefValid = computed(() => {
  return paymentRef.value && paymentRef.value.length === 4 && /^\d+$/.test(paymentRef.value);
});

const handleSubmit = () => {
  refError.value = '';

  if (!isRefValid.value) {
    refError.value = 'Please enter the last 4 digits of your reference number';
    return;
  }

  loading.value = true;

  router.post(route('customer.payment.confirm'), {
    order_id: props.order.id,
    payment_ref: paymentRef.value,
  }, {
    onSuccess: () => {
      if (toast.value) {
        toast.value.addToast('Payment confirmed successfully', 'success');
      }
      router.visit(route('customer.payment.success'));
    },
    onError: (errors) => {
      loading.value = false;
      if (errors.payment_ref) {
        refError.value = errors.payment_ref;
      } else {
        refError.value = 'An error occurred. Please try again.';
      }
    },
    onFinish: () => {
      loading.value = false;
    }
  });
};

const copyToClipboard = (text) => {
  navigator.clipboard.writeText(text).then(() => {
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  });
};
</script>

<template>
  <Head title="Complete Payment" />
  <Toast ref="toast" />

  <div class="min-h-screen bg-gray-100">
    <NavLink />

    <main class="container mx-auto px-4 py-12">
      <div class="max-w-3xl mx-auto">
        <!-- Back to Checkout Link -->
        <div class="mb-6">
          <a
            :href="route('customer.checkout')"
            class="inline-flex items-center text-gray-600 hover:text-navy-600 transition-colors"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Back to Checkout
          </a>
        </div>

        <!-- Payment Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <!-- Header -->
          <div class="bg-navy-600 px-6 py-4">
            <h1 class="text-xl font-semibold text-white">Complete Your Payment</h1>
          </div>

          <div class="p-6">
            <!-- Order Summary -->
            <div class="mb-6 pb-6 border-b border-gray-200">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>
              <div class="flex justify-between mb-2">
                <span class="text-gray-600">Order ID:</span>
                <span class="font-medium">{{ order.reference_number }}</span>
              </div>
              <div class="flex justify-between mb-2">
                <span class="text-gray-600">Total Amount:</span>
                <span class="font-medium text-lg">₱{{ order.total_amount }}</span>
              </div>
            </div>

            <!-- QR Code Section -->
            <div class="mb-8">
              <h2 class="text-lg font-medium text-gray-900 mb-4">Scan to Pay</h2>
              <div class="flex flex-col items-center">
                <div v-if="qrCode" class="bg-white p-4 rounded-lg border border-gray-200 mb-4">
                  <img :src="`/storage/${qrCode}`" alt="QR Code for Payment" class="max-w-xs mx-auto" />
                </div>
                <div v-else class="bg-red-50 text-red-800 p-4 rounded-lg mb-4 flex items-start">
                  <AlertCircleIcon class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" />
                  <p>QR code is currently unavailable. Please contact support for alternative payment methods.</p>
                </div>

                <div class="text-center mb-4">
                  <p class="text-gray-700 mb-2">Scan the QR code to pay with GCash</p>
                  <p class="text-gray-700 mb-2">Amount: <span class="font-semibold">₱{{ order.total_amount }}</span></p>

                  <div class="mt-4 flex items-center justify-center space-x-2">
                    <span class="text-sm text-gray-600">Recipient: DRM E-commerce</span>
                    <button
                      @click="copyToClipboard('DRM E-commerce')"
                      class="text-navy-600 hover:text-navy-800"
                      title="Copy recipient name"
                    >
                      <CopyIcon v-if="!copied" class="h-4 w-4" />
                      <CheckIcon v-else class="h-4 w-4 text-green-600" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Reference Form -->
            <div>
              <h2 class="text-lg font-medium text-gray-900 mb-4">Confirm Your Payment</h2>

              <div class="bg-yellow-50 p-4 rounded-lg mb-6">
                <p class="text-sm text-yellow-800">
                  After payment, please enter the last 4 digits of your GCash reference number to confirm your payment.
                </p>
              </div>

              <form @submit.prevent="handleSubmit">
                <div class="mb-6">
                  <label for="payment_ref" class="block text-sm font-medium text-gray-700 mb-1">
                    Last 4 digits of GCash Reference Number
                  </label>
                  <input
                    type="text"
                    id="payment_ref"
                    v-model="paymentRef"
                    maxlength="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-navy-500 focus:border-navy-500"
                    placeholder="Enter 4 digits"
                  />
                  <p v-if="refError" class="mt-1 text-sm text-red-600">{{ refError }}</p>
                  <p v-else class="mt-1 text-sm text-gray-500">
                    You can find this in your GCash transaction history
                  </p>
                </div>

                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="loading || !isRefValid"
                    :class="[
                      'px-6 py-3 rounded-md text-white font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500',
                      (loading || !isRefValid) ? 'bg-gray-400 cursor-not-allowed' : 'bg-navy-600 hover:bg-navy-700'
                    ]"
                  >
                    <span v-if="loading">Processing...</span>
                    <span v-else>Confirm Payment</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>

    <Footer />
  </div>
</template>

<style scoped>
.bg-navy-600 {
  background-color: #001044;
}

.hover\:bg-navy-700:hover {
  background-color: #151c63;
}

.text-navy-600 {
  color: #001044;
}

.hover\:text-navy-600:hover {
  color: #001044;
}

.hover\:text-navy-800:hover {
  color: #000833;
}

.focus\:ring-navy-500:focus {
  --tw-ring-color: #3a3f9e;
}

.focus\:border-navy-500:focus {
  border-color: #3a3f9e;
}
</style>
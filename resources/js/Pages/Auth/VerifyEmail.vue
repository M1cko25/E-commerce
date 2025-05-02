<template>
  <div
    class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8"
  >
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-xl rounded-lg sm:px-10">
        <!-- Email Icon -->
        <div class="mb-6 text-center">
          <div
            class="mx-auto h-16 w-16 bg-navy-100 rounded-full flex items-center justify-center"
          >
            <Mail class="h-8 w-8 text-navy-600" />
          </div>
        </div>

        <!-- Title and Description -->
        <div class="text-center mb-8">
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Verify your email</h2>
          <p class="text-gray-600">
            We've sent a verification link to<br />
            <span class="font-medium text-gray-900">{{ customer.email }}</span>
          </p>
        </div>

        <!-- Manual Verification Option -->
        <div v-if="verificationLinkIssue" class="mb-6 p-4 bg-yellow-50 rounded-md text-yellow-700">
          <p class="text-sm mb-2">
            <strong>Having trouble with the verification link?</strong>
          </p>
          <p class="text-sm">
            If you clicked the link in your email and saw an invalid signature error, you can try
            manually verifying by entering your ID below:
          </p>
          <form @submit.prevent="manualVerify" class="mt-3">
            <div class="flex items-center">
              <input
                v-model="manualId"
                type="number"
                placeholder="Enter your ID number"
                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-l-md focus:outline-none focus:ring-navy-500 focus:border-navy-500"
              />
              <button
                type="submit"
                :disabled="manualVerifying"
                class="px-4 py-2 text-sm text-white bg-navy-600 rounded-r-md hover:bg-navy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500 disabled:opacity-50"
              >
                <Loader2 v-if="manualVerifying" class="animate-spin h-4 w-4" />
                <span v-else>Verify</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Resend Form -->
        <form @submit.prevent="resend" class="space-y-6">
          <div
            v-if="status"
            class="p-4 rounded-md"
            :class="
              status === 'success'
                ? 'bg-green-50 text-green-700'
                : 'bg-yellow-50 text-yellow-700'
            "
          >
            <p class="text-sm text-center">
              {{
                status === "success"
                  ? "Verification link sent!"
                  : "Please wait before retrying."
              }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-navy-600 hover:bg-navy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Loader2 v-if="form.processing" class="animate-spin h-4 w-4 mr-2" />
            {{ form.processing ? "Sending..." : "Resend Verification Email" }}
          </button>
        </form>

        <!-- Help Text -->
        <div class="mt-6">
          <p class="text-center text-sm text-gray-600">
            Didn't receive the email? Check your spam folder or
            <button
              @click="openEmailClient"
              class="font-medium text-navy-600 hover:text-navy-500"
            >
              open email app
            </button>
          </p>
        </div>

        <!-- Logout Option -->
        <div class="mt-6 border-t border-gray-200 pt-6">
          <Link
            :href="route('customer.logout')"
            method="post"
            as="button"
            class="w-full text-center text-sm text-gray-600 hover:text-gray-900"
          >
            Log out
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { Mail, Loader2 } from "lucide-vue-next";
import { ref } from 'vue';

defineProps({
  status: String,
  customer: {
    type: Object,
    required: true,
  },
});

// For manual verification
const verificationLinkIssue = ref(true); // Show by default since users are having issues
const manualId = ref('');
const manualVerifying = ref(false);

const form = useForm({});

const resend = () => {
  form.post(route("verification.send"));
};

const openEmailClient = () => {
  window.location.href = "mailto:";
};

// Manual verification method
const manualVerify = () => {
  if (!manualId.value) return;

  manualVerifying.value = true;

  // Use our new route for manual verification
  window.location.href = route('verification.manual', manualId.value);
};
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>

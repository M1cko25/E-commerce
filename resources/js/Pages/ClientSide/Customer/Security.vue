<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import { Menu, X, Unlock, Eye, EyeOff } from "lucide-vue-next";
import ProfileSidebar from "@/Components/ProfileSidebar.vue";
import NavLink from "@/Components/NavLink.vue";
import Toast from "@/Components/Toast.vue";

const props = defineProps({
  customer: Object,
  addresses: Array,
  defaultAddress: Object,
});

// Form setup
const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const isMobileMenuOpen = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const toast = ref(null);
const isSubmitting = ref(false);

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const togglePasswordVisibility = (field) => {
  if (field === 'current') {
    showCurrentPassword.value = !showCurrentPassword.value;
  } else if (field === 'new') {
    showNewPassword.value = !showNewPassword.value;
  } else if (field === 'confirm') {
    showConfirmPassword.value = !showConfirmPassword.value;
  }
};

const submit = () => {
  isSubmitting.value = true;

  form.post(route('customer.security.update'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      if (toast.value) {
        toast.value.addToast('Password updated successfully', 'success');
      }
      isSubmitting.value = false;
    },
    onError: () => {
      if (toast.value) {
        toast.value.addToast('Failed to update password. Please check your inputs.', 'error');
      }
      isSubmitting.value = false;
    }
  });
};
</script>

<template>
  <Head title="Security" />
  <NavLink />
  <Toast ref="toast" />

  <div class="container mx-auto px-4 sm:px-6 py-6">
    <!-- Mobile Menu Button -->
    <div class="lg:hidden mb-4">
      <button
        @click="toggleMobileMenu"
        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors"
      >
        <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
        <X v-else class="w-6 h-6" />
      </button>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Sidebar Component -->
      <ProfileSidebar :customer="customer" :isMobileMenuOpen="isMobileMenuOpen" />

      <!-- Main Content -->
      <div class="flex-1">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
          <div class="flex items-center mb-6">
            <Unlock class="w-6 h-6 text-blue-600 mr-3" />
            <h2 class="text-xl font-semibold">Security Settings</h2>
          </div>

          <!-- Password Change Form -->
          <form @submit.prevent="submit" class="space-y-6 max-w-lg">
            <!-- Current Password -->
            <div class="space-y-2">
              <label for="current_password" class="block text-sm font-medium text-gray-700">
                Current Password
              </label>
              <div class="relative">
                <input
                  :type="showCurrentPassword ? 'text' : 'password'"
                  id="current_password"
                  v-model="form.current_password"
                  class="w-full pr-10 p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  autocomplete="current-password"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                  @click="togglePasswordVisibility('current')"
                >
                  <Eye v-if="!showCurrentPassword" class="h-5 w-5" />
                  <EyeOff v-else class="h-5 w-5" />
                </button>
              </div>
              <div v-if="form.errors.current_password" class="text-red-500 text-sm mt-1">
                {{ form.errors.current_password }}
              </div>
            </div>

            <!-- New Password -->
            <div class="space-y-2">
              <label for="password" class="block text-sm font-medium text-gray-700">
                New Password
              </label>
              <div class="relative">
                <input
                  :type="showNewPassword ? 'text' : 'password'"
                  id="password"
                  v-model="form.password"
                  class="w-full pr-10 p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                  @click="togglePasswordVisibility('new')"
                >
                  <Eye v-if="!showNewPassword" class="h-5 w-5" />
                  <EyeOff v-else class="h-5 w-5" />
                </button>
              </div>
              <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                {{ form.errors.password }}
              </div>
              <p class="text-sm text-gray-500 mt-1">
                Password must be at least 8 characters and include a combination of letters, numbers, and symbols.
              </p>
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                Confirm New Password
              </label>
              <div class="relative">
                <input
                  :type="showConfirmPassword ? 'text' : 'password'"
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  class="w-full pr-10 p-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                  @click="togglePasswordVisibility('confirm')"
                >
                  <Eye v-if="!showConfirmPassword" class="h-5 w-5" />
                  <EyeOff v-else class="h-5 w-5" />
                </button>
              </div>
              <div v-if="form.errors.password_confirmation" class="text-red-500 text-sm mt-1">
                {{ form.errors.password_confirmation }}
              </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4">
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                :disabled="form.processing || isSubmitting"
              >
                <span v-if="form.processing || isSubmitting">Updating...</span>
                <span v-else>Update Password</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

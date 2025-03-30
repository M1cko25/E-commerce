<template>
  <Head title="My Addresses" />
  <NavLink />
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
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Shipping Addresses</h2>
            <button
              @click="openAddressModal(null)"
              class="px-4 py-2 bg-navy-600 text-white rounded-md hover:bg-navy-700 transition-colors"
            >
              <Plus class="w-5 h-5 inline mr-1" />
              Add New Address
            </button>
          </div>

          <!-- Address List -->
          <div v-if="addresses.length === 0" class="text-center py-8">
            <MapPin class="w-12 h-12 mx-auto text-gray-300 mb-3" />
            <h3 class="text-lg font-medium text-gray-900">No addresses found</h3>
            <p class="text-gray-500 mt-1">Add a new shipping address to use during checkout.</p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="address in addresses"
              :key="address.id"
              class="border rounded-lg p-4 hover:shadow-md transition-shadow relative"
              :class="{ 'border-navy-600 bg-navy-50': address.default_address }"
            >
              <!-- Default badge -->
              <div v-if="address.default_address" class="absolute top-2 right-2">
                <span class="bg-navy-100 text-navy-700 text-xs px-2 py-1 rounded-full">Default</span>
              </div>

              <div class="mb-3">
                <h3 class="font-medium">{{ address.first_name }} {{ address.last_name }}</h3>
                <p class="text-sm text-gray-600">{{ address.phone_number }}</p>
              </div>

              <div class="text-sm text-gray-700 mb-4">
                <p>{{ address.complete_address }}</p>
                <p>{{ address.city }}, {{ address.province }} {{ address.zip_code }}</p>
              </div>

              <div class="flex justify-between items-center">
                <div>
                  <button
                    v-if="!address.default_address"
                    @click="setAsDefault(address.id)"
                    class="text-sm text-navy-600 hover:text-navy-800"
                  >
                    Set as default
                  </button>
                </div>
                <div class="flex gap-2">
                  <button
                    @click="openAddressModal(address)"
                    class="p-1.5 text-gray-600 hover:text-navy-600 rounded-full hover:bg-gray-100"
                  >
                    <Edit class="w-4 h-4" />
                  </button>
                  <button
                    v-if="!address.default_address"
                    @click="confirmDelete(address.id)"
                    class="p-1.5 text-gray-600 hover:text-red-600 rounded-full hover:bg-gray-100"
                  >
                    <Trash class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Address Modal (Add/Edit) -->
  <TransitionRoot appear :show="showAddressModal" as="template">
    <Dialog as="div" @close="showAddressModal = false" class="relative z-50">
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
                {{ editingAddress ? 'Edit Address' : 'Add New Address' }}
              </DialogTitle>

              <form @submit.prevent="saveAddress" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      First Name
                    </label>
                    <input
                      v-model="addressForm.first_name"
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
                      v-model="addressForm.last_name"
                      type="text"
                      required
                      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Phone Number
                  </label>
                  <input
                    v-model="addressForm.phone_number"
                    type="tel"
                    required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Province
                  </label>
                  <select
                    v-model="addressForm.province"
                    required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                  >
                    <option value="">Select Province</option>
                    <option value="Metro Manila">Metro Manila</option>
                    <option value="Cavite">Cavite</option>
                    <option value="Laguna">Laguna</option>
                    <option value="Batangas">Batangas</option>
                    <option value="Rizal">Rizal</option>
                    <option value="Bulacan">Bulacan</option>
                    <!-- Add more provinces as needed -->
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    City/Municipality
                  </label>
                  <input
                    v-model="addressForm.city"
                    type="text"
                    required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Zip Code
                  </label>
                  <input
                    v-model="addressForm.zip_code"
                    type="text"
                    required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Complete Address (Street, Building, Unit)
                  </label>
                  <textarea
                    v-model="addressForm.complete_address"
                    rows="3"
                    required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-navy-500"
                  ></textarea>
                </div>

                <div class="flex items-center">
                  <input
                    id="default_address"
                    v-model="addressForm.default_address"
                    type="checkbox"
                    class="h-4 w-4 text-navy-600 focus:ring-navy-500 border-gray-300 rounded"
                  />
                  <label for="default_address" class="ml-2 block text-sm text-gray-700">
                    Set as default address
                  </label>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                  <button
                    type="button"
                    @click="showAddressModal = false"
                    class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    class="px-4 py-2 bg-navy-600 text-white rounded-lg hover:bg-navy-700"
                  >
                    {{ editingAddress ? 'Update' : 'Save' }}
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Delete Confirmation Modal -->
  <TransitionRoot appear :show="showDeleteModal" as="template">
    <Dialog as="div" @close="showDeleteModal = false" class="relative z-50">
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
              <div class="text-center">
                <AlertCircle class="h-12 w-12 text-red-500 mx-auto mb-4" />
                <DialogTitle as="h3" class="text-lg font-medium text-gray-900 mb-2">
                  Delete Address
                </DialogTitle>
                <p class="text-sm text-gray-500">
                  Are you sure you want to delete this address? This action cannot be undone.
                </p>
              </div>

              <div class="mt-6 flex justify-center space-x-3">
                <button
                  @click="showDeleteModal = false"
                  class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  @click="deleteAddress"
                  class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                >
                  Delete
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import {
  Menu,
  X,
  Edit,
  Trash,
  Plus,
  MapPin,
  AlertCircle
} from "lucide-vue-next";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from "@headlessui/vue";
import ProfileSidebar from "@/Components/ProfileSidebar.vue";
import NavLink from "@/Components/NavLink.vue";

const props = defineProps({
  customer: Object,
  addresses: Array,
});

const isMobileMenuOpen = ref(false);
const showAddressModal = ref(false);
const showDeleteModal = ref(false);
const editingAddress = ref(null);
const addressToDeleteId = ref(null);

const addressForm = ref({
  first_name: '',
  last_name: '',
  phone_number: '',
  province: '',
  city: '',
  zip_code: '',
  complete_address: '',
  default_address: false,
});

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const openAddressModal = (address) => {
  if (address) {
    // Edit mode
    editingAddress.value = address.id;
    addressForm.value = {
      first_name: address.first_name,
      last_name: address.last_name,
      phone_number: address.phone_number,
      province: address.province,
      city: address.city,
      zip_code: address.zip_code,
      complete_address: address.complete_address,
      default_address: address.default_address,
    };
  } else {
    // Add mode
    editingAddress.value = null;
    addressForm.value = {
      first_name: props.customer.first_name || '',
      last_name: props.customer.last_name || '',
      phone_number: props.customer.phone || '',
      province: '',
      city: '',
      zip_code: '',
      complete_address: '',
      default_address: props.addresses.length === 0, // Automatically set first address as default
    };
  }
  showAddressModal.value = true;
};

const saveAddress = () => {
  if (editingAddress.value) {
    // Update existing address
    router.put(route('customer.addresses.update', editingAddress.value), addressForm.value, {
      onSuccess: () => {
        showAddressModal.value = false;
      },
    });
  } else {
    // Create new address
    router.post(route('customer.addresses.store'), addressForm.value, {
      onSuccess: () => {
        showAddressModal.value = false;
      },
    });
  }
};

const confirmDelete = (addressId) => {
  addressToDeleteId.value = addressId;
  showDeleteModal.value = true;
};

const deleteAddress = () => {
  if (addressToDeleteId.value) {
    router.delete(route('customer.addresses.destroy', addressToDeleteId.value), {
      onSuccess: () => {
        showDeleteModal.value = false;
        addressToDeleteId.value = null;
      },
    });
  }
};

const setAsDefault = (addressId) => {
  router.patch(route('customer.addresses.default', addressId), {
    onSuccess: () => {
      // Success message handled by Inertia flash message
    },
  });
};
</script>

<style scoped>
.bg-navy-50 {
  background-color: #f0f0ff;
}

.bg-navy-100 {
  background-color: #e6e6ff;
}

.bg-navy-600 {
  background-color: #1a237e;
}

.bg-navy-700 {
  background-color: #151b60;
}

.text-navy-600 {
  color: #1a237e;
}

.text-navy-700 {
  color: #151b60;
}

.text-navy-800 {
  color: #0d1147;
}

.border-navy-600 {
  border-color: #1a237e;
}

.hover\:bg-navy-700:hover {
  background-color: #151b60;
}

.focus\:ring-navy-500:focus {
  --tw-ring-color: #1e2b8f;
}
</style>

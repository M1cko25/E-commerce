<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, TrashIcon, QrCodeIcon } from 'lucide-vue-next';
import Sidebar from '../../../Components/Sidebar.vue';
import Header from '../../../Components/Header.vue';
import Toast from '../../../Components/Toast.vue';

const props = defineProps({
  qrCodes: Array,
});

const showModal = ref(false);
const qrImage = ref(null);
const previewUrl = ref(null);
const errors = ref({});
const toast = ref(null);
const isDeleting = ref(false);
const qrCodeToDelete = ref(null);

const openModal = () => {
  showModal.value = true;
  qrImage.value = null;
  previewUrl.value = null;
  errors.value = {};
};

const closeModal = () => {
  showModal.value = false;
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  qrImage.value = file;
  previewUrl.value = URL.createObjectURL(file);
};

const uploadQrCode = () => {
  if (!qrImage.value) {
    errors.value = { qr_path: 'Please select an image to upload' };
    return;
  }

  const formData = new FormData();
  formData.append('qr_path', qrImage.value);

  router.post(route('qr-codes.store'), formData, {
    onSuccess: () => {
      closeModal();
      if (toast.value) {
        toast.value.addToast('QR code uploaded successfully', 'success');
      }
    },
    onError: (err) => {
      errors.value = err;
    },
    forceFormData: true,
  });
};

const confirmDelete = (id) => {
  qrCodeToDelete.value = id;
  isDeleting.value = true;
};

const cancelDelete = () => {
  isDeleting.value = false;
  qrCodeToDelete.value = null;
};

const deleteQrCode = () => {
  router.delete(route('qr-codes.destroy', qrCodeToDelete.value), {
    onSuccess: () => {
      isDeleting.value = false;
      qrCodeToDelete.value = null;
      if (toast.value) {
        toast.value.addToast('QR code deleted successfully', 'success');
      }
    },
    onError: (err) => {
      isDeleting.value = false;
      qrCodeToDelete.value = null;
      if (toast.value) {
        toast.value.addToast('Failed to delete QR code', 'error');
      }
    },
  });
};

onMounted(() => {
  if (document.querySelector('input[type="file"]')) {
    document.querySelector('input[type="file"]').value = '';
  }
});
</script>

<template>
  <div>
    <Head title="QR Codes Management" />
    <Toast ref="toast" />

    <div class="min-h-screen bg-gray-50">
      <Sidebar />

      <main class="lg:ml-64 min-h-screen">
        <Header title="QR Codes Management" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">QR Codes</h1>

            <button
              @click="openModal"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-navy-600 hover:bg-navy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500"
            >
              <PlusIcon class="h-5 w-5 mr-2" /> Upload QR Code
            </button>
          </div>

          <!-- QR Codes Grid -->
          <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <div v-if="qrCodes && qrCodes.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="qrCode in qrCodes"
                :key="qrCode.id"
                class="relative group border border-gray-200 rounded-lg overflow-hidden bg-white"
              >
                <div class="p-4 flex flex-col items-center">
                  <div class="aspect-square w-full max-w-xs flex items-center justify-center mb-4">
                    <img :src="`/storage/${qrCode.qr_path}`" alt="QR Code" class="max-h-full max-w-full object-contain" />
                  </div>

                  <div class="flex items-center justify-between w-full">
                    <div class="text-sm text-gray-500">
                      QR Code #{{ qrCode.id }}
                    </div>

                    <button
                      @click="confirmDelete(qrCode.id)"
                      class="text-red-600 hover:text-red-800 transition-colors"
                      title="Delete QR Code"
                    >
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-12">
              <QrCodeIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">No QR Codes Found</h3>
              <p class="text-gray-500 mb-6">Upload a QR code to display it to customers during checkout.</p>
              <button
                @click="openModal"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-navy-600 hover:bg-navy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500"
              >
                <PlusIcon class="h-5 w-5 mr-2" /> Upload QR Code
              </button>
            </div>
          </div>
        </div>
      </main>

      <!-- Upload Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

          <div class="relative bg-white rounded-lg max-w-md w-full p-6 z-10">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Upload QR Code</h3>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <div class="space-y-4">
              <div class="space-y-2">
                <label for="qr_path" class="block text-sm font-medium text-gray-700">QR Code Image</label>
                <input
                  type="file"
                  id="qr_path"
                  @change="handleFileChange"
                  accept="image/*"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-navy-50 file:text-navy-700 hover:file:bg-navy-100"
                />
                <div v-if="errors.qr_path" class="mt-1 text-sm text-red-600">{{ errors.qr_path }}</div>
              </div>

              <div v-if="previewUrl" class="mt-4 flex justify-center">
                <img :src="previewUrl" alt="QR Code Preview" class="max-h-48 rounded-md" />
              </div>

              <div class="mt-6 flex justify-end space-x-3">
                <button
                  @click="closeModal"
                  class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500"
                >
                  Cancel
                </button>
                <button
                  @click="uploadQrCode"
                  class="px-4 py-2 bg-navy-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-navy-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500"
                >
                  Upload
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="isDeleting" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

          <div class="relative bg-white rounded-lg max-w-md w-full p-6 z-10">
            <div class="flex flex-col items-center">
              <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <TrashIcon class="h-6 w-6 text-red-600" />
              </div>
              <h3 class="mt-4 text-lg font-semibold text-gray-900">Delete QR Code</h3>
              <p class="mt-2 text-sm text-gray-500 text-center">
                Are you sure you want to delete this QR code? This action cannot be undone.
              </p>
              <div class="mt-6 flex gap-4">
                <button
                  @click="cancelDelete"
                  class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500"
                >
                  Cancel
                </button>
                <button
                  @click="deleteQrCode"
                  class="px-4 py-2 bg-red-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.bg-navy-50 {
  background-color: #e6e6ff;
}

.bg-navy-600 {
  background-color: #001044;
}

.bg-navy-700 {
  background-color: #151c63;
}

.hover\:bg-navy-700:hover {
  background-color: #151c63;
}

.hover\:bg-navy-100:hover {
  background-color: #f0f0ff;
}

.text-navy-700 {
  color: #151c63;
}

.focus\:ring-navy-500:focus {
  --tw-ring-color: #3a3f9e;
}
</style>

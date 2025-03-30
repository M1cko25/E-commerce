<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
  ChevronLeft,
  CheckCircle,
  CircleX,
  ArrowLeft,
  Package,
  Truck,
  User,
  Calendar,
  DollarSign,
  AlertCircle,
  ShoppingCart,
} from "lucide-vue-next";
import Sidebar from "../../../Components/Sidebar.vue";
import Header from "../../../Components/Header.vue";
import Toast from "@/Components/Toast.vue";
import { route } from "../../../../../vendor/tightenco/ziggy/src/js";

const props = defineProps({
  order: Object,
});

const toast = ref(null);
const showActionModal = ref(false);
const actionType = ref('');

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-800',
  requested: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800',
  refunded: 'bg-blue-100 text-blue-800',
  returned: 'bg-green-100 text-green-800',
  none: 'bg-gray-100 text-gray-800'
};

const openActionModal = (action) => {
  actionType.value = action;
  showActionModal.value = true;
};

const confirmAction = () => {
  let routeName = '';
  let successMessage = '';

  if (actionType.value === 'approve-return') {
    routeName = 'returns.approve-return';
    successMessage = 'Return request approved successfully';
  } else if (actionType.value === 'approve-refund') {
    routeName = 'returns.approve-refund';
    successMessage = 'Refund processed successfully';
  } else if (actionType.value === 'reject') {
    routeName = 'returns.reject';
    successMessage = 'Return request rejected';
  }

  router.put(route(routeName, props.order.id), {}, {
    onSuccess: () => {
      showActionModal.value = false;
      if (toast.value) {
        toast.value.addToast(successMessage, "success");
      }
    },
    onError: (error) => {
      showActionModal.value = false;
      if (toast.value) {
        toast.value.addToast(`Failed to process: ${error}`, "error");
      }
    }
  });
};

const getStatusText = (status) => {
  switch (status) {
    case 'requested':
      return 'Requested';
    case 'pending':
      return 'Pending';
    case 'approved':
      return 'Approved';
    case 'rejected':
      return 'Rejected';
    case 'refunded':
      return 'Refunded';
    case 'returned':
      return 'Returned';
    default:
      return 'Unknown';
  }
};

const canApprove = () => {
  return props.order.return_refund_status === 'requested' || props.order.return_refund_status === 'pending';
};

const canReject = () => {
  return props.order.return_refund_status === 'requested' || props.order.return_refund_status === 'pending';
};
</script>

<template>
  <Head :title="`Return #${order?.id || ''} | Return Management`" />
  <Toast ref="toast" />
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Sidebar Toggle -->
    <Sidebar></Sidebar>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <!-- Header -->
      <Header :title="`Return Request Details`"></Header>

      <!-- Return Details Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back button -->
        <div class="mb-6">
          <Link
            :href="route('returns.index')"
            class="inline-flex items-center text-navy-600 hover:text-navy-800"
          >
            <ArrowLeft class="h-4 w-4 mr-1" />
            Back to All Returns
          </Link>
        </div>

        <!-- Status header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">
                Order #{{ order?.reference_number }}
              </h1>
              <p class="text-gray-600 mt-1">
                Return Request ID: {{ order?.id }}
              </p>
            </div>

            <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row items-start sm:items-center gap-4">
              <!-- Return/Refund Status Badge -->
              <span
                :class="[
                  'px-3 py-1 rounded-full text-sm font-medium',
                  statusColors[order?.return_refund_status] || 'bg-gray-100'
                ]"
              >
                {{ getStatusText(order?.return_refund_status) }}
              </span>

              <!-- Order Status Badge -->
              <span
                :class="[
                  'px-3 py-1 rounded-full text-sm font-medium',
                  order?.order_status === 'returned' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                ]"
              >
                Order: {{ order?.order_status }}
              </span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Left Column - Order Details -->
          <div class="col-span-2 space-y-6">
            <!-- Items -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
              <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900 flex items-center">
                  <Package class="h-5 w-5 mr-2 text-gray-500" />
                  Returned Items
                </h2>
              </div>

              <div class="divide-y divide-gray-200">
                <div v-for="item in order?.items" :key="item.id" class="px-6 py-4">
                  <div class="flex items-start">
                    <div class="flex-1 min-w-0">
                      <h3 class="text-sm font-medium text-gray-900">{{ item.name }}</h3>
                      <div class="mt-1 flex items-center text-sm text-gray-500">
                        <div class="truncate">Quantity: {{ item.quantity }}</div>
                        <div class="mx-2">•</div>
                        <div>Unit Price: {{ formatCurrency(item.unit_amount) }}</div>
                      </div>
                      <div class="mt-1 text-sm font-medium text-green-600">
                        Subtotal: {{ formatCurrency(item.quantity * item.unit_amount) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
                <div class="flex justify-between">
                  <span class="text-base font-medium text-gray-900">Total Refund Amount</span>
                  <span class="text-base font-medium text-navy-600">{{ formatCurrency(order?.total_amount) }}</span>
                </div>
              </div>
            </div>

            <!-- Reason for Return -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
              <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900 flex items-center">
                  <AlertCircle class="h-5 w-5 mr-2 text-gray-500" />
                  Reason for Return
                </h2>
              </div>
              <div class="px-6 py-4">
                <p class="text-gray-700 whitespace-pre-line">{{ order?.notes || 'No reason provided' }}</p>
              </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-lg shadow-sm overflow-y-auto">
              <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900">Timeline</h2>
              </div>
              <div class="px-6 py-4">
                <div class="flow-root">
                  <ul class="-mb-8">
                    <li class="relative pb-8">
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                            <ShoppingCart class="h-4 w-4 text-white" />
                          </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                          <div>
                            <p class="text-sm text-gray-500">Order Placed</p>
                          </div>
                          <div class="text-right text-sm whitespace-nowrap text-gray-500">
                            {{ formatDate(order?.created_at) }}
                          </div>
                        </div>
                      </div>
                    </li>

                    <li class="relative pb-8">
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                            <Truck class="h-4 w-4 text-white" />
                          </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                          <div>
                            <p class="text-sm text-gray-500">Order Delivered</p>
                          </div>
                          <div class="text-right text-sm whitespace-nowrap text-gray-500">
                            {{ formatDate(order?.delivered_at) }}
                          </div>
                        </div>
                      </div>
                    </li>

                    <li>
                      <div class="relative flex space-x-3">
                        <div>
                          <span class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center ring-8 ring-white">
                            <AlertCircle class="h-4 w-4 text-white" />
                          </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                          <div>
                            <p class="text-sm text-gray-500">Return Requested</p>
                          </div>
                          <div class="text-right text-sm whitespace-nowrap text-gray-500">
                            {{ formatDate(order?.updated_at) }}
                          </div>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Customer Info and Actions -->
          <div class="space-y-6">
            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
              <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900 flex items-center">
                  <User class="h-5 w-5 mr-2 text-gray-500" />
                  Customer Information
                </h2>
              </div>
              <div class="px-6 py-4 space-y-3">
                <div>
                  <p class="text-sm font-medium text-gray-500">Name</p>
                  <p class="mt-1 text-sm text-gray-900">{{ order?.customer_name }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Phone</p>
                  <p class="mt-1 text-sm text-gray-900">{{ order?.customer_phone_number }}</p>
                </div>
              </div>
            </div>

            <!-- Order Information -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
              <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900 flex items-center">
                  <ShoppingCart class="h-5 w-5 mr-2 text-gray-500" />
                  Order Information
                </h2>
              </div>
              <div class="px-6 py-4 space-y-3">
                <div>
                  <p class="text-sm font-medium text-gray-500">Order Reference</p>
                  <p class="mt-1 text-sm text-gray-900">{{ order?.order_id }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Order Date</p>
                  <p class="mt-1 text-sm text-gray-900">{{ formatDate(order?.created_at) }}</p>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-500">Payment Method</p>
                  <p class="mt-1 text-sm text-gray-900">{{ order?.payment_method }}</p>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
              <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900">Actions</h2>
              </div>
              <div class="px-6 py-4 space-y-3">
                <button
                  v-if="canApprove()"
                  @click="openActionModal('approve-return')"
                  class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none"
                >
                  Approve Return & Update Inventory
                </button>

                <button
                  v-if="canApprove()"
                  @click="openActionModal('approve-refund')"
                  class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
                >
                  Process Refund Only
                </button>

                <button
                  v-if="canReject()"
                  @click="openActionModal('reject')"
                  class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none"
                >
                  Reject Request
                </button>

                <Link
                  :href="route('returns.index')"
                  class="inline-block w-full text-center mt-3 px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none"
                >
                  Back to All Returns
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Action Confirmation Modal -->
    <div v-if="showActionModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full p-6">
          <div class="flex flex-col items-center">
            <div
              class="mx-auto flex h-12 w-12 items-center justify-center rounded-full"
              :class="[
                actionType.includes('approve') ? 'bg-green-100' : 'bg-red-100'
              ]"
            >
              <component
                :is="actionType.includes('approve') ? CheckCircle : CircleX"
                class="h-6 w-6"
                :class="[
                  actionType.includes('approve') ? 'text-green-600' : 'text-red-600'
                ]"
              />
            </div>
            <h3 class="mt-4 text-lg font-semibold">
              {{ actionType === 'approve-return' ? 'Approve Return' :
                 actionType === 'approve-refund' ? 'Process Refund' : 'Reject Request' }}
            </h3>
            <p class="mt-2 text-sm text-gray-500 text-center">
              <template v-if="actionType === 'approve-return'">
                Are you sure you want to approve this return? This will update inventory and process a refund.
              </template>
              <template v-else-if="actionType === 'approve-refund'">
                Are you sure you want to process a refund without requiring the items to be returned?
              </template>
              <template v-else>
                Are you sure you want to reject this return request? This action cannot be undone.
              </template>
            </p>
            <div class="mt-6 flex gap-4">
              <button
                @click="showActionModal = false"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                @click="confirmAction"
                class="px-4 py-2 text-white rounded-md"
                :class="[
                  actionType.includes('approve') ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'
                ]"
              >
                {{ actionType.includes('approve') ? 'Approve' : 'Reject' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.bg-navy-600 {
  background-color: #001044;
}

.text-navy-600 {
  color: #001044;
}

.hover\:text-navy-800:hover {
  color: #000833;
}
</style>

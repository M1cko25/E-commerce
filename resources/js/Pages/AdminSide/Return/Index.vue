<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
  EllipsisVertical,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Trash,
  XIcon,
  CheckCircle,
  CircleX,
} from "lucide-vue-next";
import Sidebar from "../../../Components/Sidebar.vue";
import Header from "../../../Components/Header.vue";
import Toast from "@/Components/Toast.vue";
import { route } from "../../../../../vendor/tightenco/ziggy/src/js";

const props = defineProps({
  returns: Object,
});

// Mock data for development - remove this when actual data is available
const mockReturns = {
  data: [
    {
      id: 1,
      order_id: "ORD-12345",
      customer_name: "John Doe",
      items: [{ name: "Product 1", quantity: 2 }],
      reason: "Defective product",
      status: "pending",
      created_at: "2023-04-12T14:30:00",
      refund_amount: 89.99,
    },
    {
      id: 2,
      order_id: "ORD-67890",
      customer_name: "Jane Smith",
      items: [{ name: "Product 2", quantity: 1 }],
      reason: "Wrong size",
      status: "approved",
      created_at: "2023-04-10T09:15:00",
      refund_amount: 45.50,
    },
    {
      id: 3,
      order_id: "ORD-54321",
      customer_name: "Robert Johnson",
      items: [{ name: "Product 3", quantity: 3 }],
      reason: "Changed mind",
      status: "rejected",
      created_at: "2023-04-08T16:45:00",
      refund_amount: 120.75,
    },
    {
      id: 4,
      order_id: "ORD-13579",
      customer_name: "Mary Williams",
      items: [{ name: "Product 4", quantity: 1 }],
      reason: "Item not as described",
      status: "pending",
      created_at: "2023-04-11T11:20:00",
      refund_amount: 67.25,
    },
  ],
  links: [
    { label: "&laquo; Previous", url: null, active: false },
    { label: "1", url: "#", active: true },
    { label: "Next &raquo;", url: null, active: false },
  ],
  from: 1,
  to: 4,
  total: 4,
};

const headers = ["ORDER ID", "CUSTOMER", "REASON", "ITEMS", "AMOUNT", "DATE", "STATUS", "ACTIONS"];

const isFilterOpen = ref(false);
const isSortOpen = ref(false);
const showDeleteModal = ref(false);
const showActionModal = ref(false);
const returnToDelete = ref(null);
const returnToAction = ref(null);
const actionType = ref("");
const currentFilter = ref('all');
const activeActionMenu = ref(null);
const toast = ref(null);
const actionMenus = ref([]);

// Filter options
const filterOptions = [
  { label: 'All Returns', value: 'all' },
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Rejected', value: 'rejected' },
  { label: 'High Value (>$100)', value: 'high_value' },
  { label: 'Recent (Last 7 Days)', value: 'recent' },
];

// Sort options
const currentSort = ref({
  value: 'created_at',
  direction: 'desc',
  label: 'Date (Newest First)'
});

const sortOptions = [
  { value: 'created_at', label: 'Date' },
  { value: 'refund_amount', label: 'Refund Amount' },
  { value: 'customer_name', label: 'Customer Name' },
  { value: 'order_id', label: 'Order ID' },
];

// Filter and sort the returns
const filteredAndSortedReturns = computed(() => {
  // Use mockReturns for development or props.returns when available
  let result = [...(props.returns?.data || mockReturns.data)];

  // Apply filters
  if (currentFilter.value !== 'all') {
    switch (currentFilter.value) {
      case 'pending':
      case 'approved':
      case 'rejected':
        result = result.filter(item => item.status === currentFilter.value);
        break;
      case 'high_value':
        result = result.filter(item => item.refund_amount > 100);
        break;
      case 'recent':
        // Filter to last 7 days
        const lastWeek = new Date();
        lastWeek.setDate(lastWeek.getDate() - 7);
        result = result.filter(item => new Date(item.created_at) >= lastWeek);
        break;
    }
  }

  // Apply sorting
  result.sort((a, b) => {
    let comparison = 0;

    switch (currentSort.value.value) {
      case 'created_at':
        comparison = new Date(a.created_at) - new Date(b.created_at);
        break;
      case 'refund_amount':
        comparison = a.refund_amount - b.refund_amount;
        break;
      case 'customer_name':
        comparison = a.customer_name.localeCompare(b.customer_name);
        break;
      case 'order_id':
        comparison = a.order_id.localeCompare(b.order_id);
        break;
    }

    return currentSort.value.direction === 'asc' ? comparison : -comparison;
  });

  return result;
});

const applyFilter = (filter) => {
  currentFilter.value = filter;
  isFilterOpen.value = false;
};

const clearFilter = () => {
  currentFilter.value = 'all';
};

const applySort = (sort) => {
  // If clicking the same sort option, toggle direction
  if (currentSort.value.value === sort.value) {
    currentSort.value = {
      ...sort,
      direction: currentSort.value.direction === 'asc' ? 'desc' : 'asc',
      label: sort.label + (currentSort.value.direction === 'asc' ? ' (Oldest First)' : ' (Newest First)')
    };
  } else {
    // New sort option
    currentSort.value = {
      ...sort,
      direction: 'desc',
      label: sort.label + (sort.value === 'refund_amount' ? ' (Highest First)' : ' (Newest First)')
    };
  }
  isSortOpen.value = false;
};

const openActionMenu = (index, event) => {
  if (activeActionMenu.value === index) {
    closeActionMenu();
  } else {
    activeActionMenu.value = index;
  }
};

const closeActionMenu = () => {
  activeActionMenu.value = null;
};

const openDeleteModal = (item) => {
  returnToDelete.value = item;
  closeActionMenu();
  showDeleteModal.value = true;
};

const openActionModal = (item, action) => {
  returnToAction.value = item;
  actionType.value = action;
  closeActionMenu();
  showActionModal.value = true;
};

const confirmDelete = () => {
  // Implement delete logic
  router.delete(route("returns.destroy", returnToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
      closeActionMenu();
      toast.value.addToast("Return deleted successfully", "success");
    },
    onError: (error) => {
      console.error(error);
      toast.value.addToast("Failed to delete return", "error");
    }
  });
};

const confirmAction = () => {
  // Implement action logic (approve or reject)
  if (actionType.value === 'approve') {
    // Determine whether this is a return or refund
    const isReturn = returnToAction.value.status === 'requested';
    const routeName = isReturn ? 'returns.approve-return' : 'returns.approve-refund';

    router.put(route(routeName, returnToAction.value.id), {}, {
      onSuccess: () => {
        showActionModal.value = false;
        toast.value.addToast(`Return ${actionType.value}d successfully`, "success");
      },
      onError: (error) => {
        console.error(error);
        toast.value.addToast(`Failed to ${actionType.value} return`, "error");
      }
    });
  } else if (actionType.value === 'reject') {
    router.put(route("returns.reject", returnToAction.value.id), {}, {
      onSuccess: () => {
        showActionModal.value = false;
        toast.value.addToast(`Return ${actionType.value}ed successfully`, "success");
      },
      onError: (error) => {
        console.error(error);
        toast.value.addToast(`Failed to ${actionType.value} return`, "error");
      }
    });
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'approved':
      return 'bg-green-100 text-green-800';
    case 'rejected':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(amount);
};

const handleClickOutside = (event) => {
  // Check if action menu is open and clicked outside
  if (activeActionMenu.value !== null) {
    const activeMenu = actionMenus.value[activeActionMenu.value];
    if (activeMenu && !activeMenu.contains(event.target) && !event.target.closest('button.action-menu-toggle')) {
      closeActionMenu();
    }
  }

  // Also close filter and sort dropdowns when clicking outside
  if (!event.target.closest('button.has-dropdown')) {
    isFilterOpen.value = false;
    isSortOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  // Clear refs when component unmounts
  actionMenus.value = [];
});
</script>

<template>
  <Head title=" | Return Management" />
  <Toast ref="toast" />
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Sidebar Toggle -->
    <Sidebar></Sidebar>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <!-- Header -->
      <Header title="Return Management"></Header>

      <!-- Return Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
          <div class="flex space-x-4">
            <!-- Filter Dropdown -->
            <div class="relative">
              <button
                @click="isFilterOpen = !isFilterOpen"
                class="flex items-center px-4 py-2 border rounded-md bg-white hover:bg-gray-50 has-dropdown"
              >
                Filter by {{ currentFilter !== 'all' ? ': ' + currentFilter : '' }}
                <ChevronDown class="ml-2 h-5 w-5" />
              </button>
              <div
                v-if="isFilterOpen"
                class="absolute mt-2 w-48 bg-white rounded-md shadow-lg z-10 py-1"
              >
                <button
                  v-for="filter in filterOptions"
                  :key="filter.value"
                  @click="applyFilter(filter.value)"
                  class="block w-full px-4 py-2 text-sm text-left hover:bg-gray-100"
                  :class="{'bg-gray-100': currentFilter === filter.value}"
                >
                  {{ filter.label }}
                </button>
              </div>
            </div>

            <!-- Sort Dropdown -->
            <div class="relative">
              <button
                @click="isSortOpen = !isSortOpen"
                class="flex items-center px-4 py-2 border rounded-md bg-white hover:bg-gray-50 has-dropdown"
              >
                Sort by: {{ currentSort.label }}
                <ChevronDown class="ml-2 h-5 w-5" />
              </button>
              <div
                v-if="isSortOpen"
                class="absolute mt-2 w-48 bg-white rounded-md shadow-lg z-10 py-1"
              >
                <button
                  v-for="sort in sortOptions"
                  :key="sort.value"
                  @click="applySort(sort)"
                  class="block w-full px-4 py-2 text-sm text-left hover:bg-gray-100"
                  :class="{'bg-gray-100': currentSort.value === sort.value}"
                >
                  {{ sort.label }}
                  <span v-if="currentSort.value === sort.value" class="float-right">
                    {{ currentSort.direction === 'asc' ? '↑' : '↓' }}
                  </span>
                </button>
              </div>
            </div>

            <!-- Active Filter Tags -->
            <div v-if="currentFilter !== 'all'" class="flex items-center">
              <span class="px-2 py-1 bg-navy-100 text-navy-800 text-sm rounded-md flex items-center">
                {{ currentFilter }}
                <button @click="clearFilter" class="ml-1 text-navy-500 hover:text-navy-700">
                  <XIcon class="h-4 w-4" />
                </button>
              </span>
            </div>
          </div>
        </div>

        <!-- Returns Table -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  v-for="header in headers"
                  :key="header"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  {{ header }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(returnItem, index) in filteredAndSortedReturns" :key="returnItem.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ returnItem.order_id }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ returnItem.customer_name }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ returnItem.reason }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">
                    <span v-for="(item, idx) in returnItem.items" :key="idx">
                      {{ item.quantity }}x {{ item.name }}{{ idx < returnItem.items.length - 1 ? ', ' : '' }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium" :class="{
                    'text-red-600': returnItem.refund_amount > 100,
                    'text-amber-600': returnItem.refund_amount > 50 && returnItem.refund_amount <= 100,
                    'text-green-600': returnItem.refund_amount <= 50
                  }">
                    {{ formatCurrency(returnItem.refund_amount) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(returnItem.created_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'px-2 py-1 text-xs rounded-full inline-flex items-center',
                    getStatusClass(returnItem.status)
                  ]">
                    <span class="capitalize">{{ returnItem.status }}</span>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right relative">
                  <button
                    @click="openActionMenu(index, $event)"
                    class="text-gray-400 hover:text-gray-500 focus:outline-none action-menu-toggle"
                  >
                    <EllipsisVertical class="h-5 w-5" />
                  </button>
                  <!-- Action Menu -->
                  <div
                    v-if="activeActionMenu === index"
                    class="absolute z-50 bg-white border border-gray-200 shadow-lg rounded-md"
                    style="top: 0; right: 100%; margin-right: 0.5rem;"
                    :ref="el => { if (el) actionMenus[index] = el }"
                  >
                    <button
                      v-if="returnItem.status === 'pending'"
                      @click="openActionModal(returnItem, 'approve')"
                      class="block w-full px-4 py-2 text-sm text-green-500 hover:bg-green-100 text-left"
                    >
                      Approve
                    </button>
                    <button
                      v-if="returnItem.status === 'pending'"
                      @click="openActionModal(returnItem, 'reject')"
                      class="block w-full px-4 py-2 text-sm text-red-600 hover:bg-red-100 text-left"
                    >
                      Reject
                    </button>
                    <Link :href="route('returns.show', returnItem.id)">
                      <button
                        class="block w-full px-4 py-2 text-sm text-blue-500 hover:bg-blue-100 text-left"
                      >
                        View Details
                      </button>
                    </Link>
                    <button
                      @click="openDeleteModal(returnItem)"
                      class="block w-full px-4 py-2 text-sm text-red-600 hover:bg-red-100 text-left"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredAndSortedReturns.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                  No returns found matching your criteria
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Links -->
        <div class="flex items-center justify-between mt-6">
          <div class="flex items-center">
            <span class="text-sm text-gray-700">
              Showing {{ props.returns?.from || mockReturns.from }} to {{ props.returns?.to || mockReturns.to }} of
              {{ props.returns?.total || mockReturns.total }} results
            </span>
          </div>
          <div class="flex gap-2">
            <Link
              v-for="link in (props.returns?.links || mockReturns.links)"
              :key="link.label"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1 rounded-md',
                {
                  'pointer-events-none text-slate-300': !link.url,
                  'text-white bg-navy-900 font-medium hover:bg-navy-700': link.active,
                  'bg-stone-100': !link.active && link.url,
                },
              ]"
            >
              <template v-if="link.label.includes('Previous')">
                <ChevronLeft class="h-5 w-5" />
              </template>
              <template v-else-if="link.label.includes('Next')">
                <ChevronRight class="h-5 w-5" />
              </template>
              <template v-else>
                {{ link.label }}
              </template>
            </Link>
          </div>
        </div>
      </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full p-6">
          <div class="flex flex-col items-center">
            <div
              class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100"
            >
              <Trash class="h-6 w-6 text-red-600" />
            </div>
            <h3 class="mt-4 text-lg font-semibold">Delete Return Request</h3>
            <p class="mt-2 text-sm text-gray-500">Are you sure you want to delete this return request? This action cannot be undone.</p>
            <div class="mt-6 flex gap-4">
              <button
                @click="showDeleteModal = false"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                @click="confirmDelete"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Confirmation Modal -->
    <div v-if="showActionModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full p-6">
          <div class="flex flex-col items-center">
            <div
              class="mx-auto flex h-12 w-12 items-center justify-center rounded-full"
              :class="actionType === 'approve' ? 'bg-green-100' : 'bg-red-100'"
            >
              <component
                :is="actionType === 'approve' ? CheckCircle : CircleX"
                class="h-6 w-6"
                :class="actionType === 'approve' ? 'text-green-600' : 'text-red-600'"
              />
            </div>
            <h3 class="mt-4 text-lg font-semibold">
              {{ actionType === 'approve' ? 'Approve' : 'Reject' }} Return Request
            </h3>
            <p class="mt-2 text-sm text-gray-500">
              Are you sure you want to {{ actionType }} this return request for order
              <span class="font-medium">{{ returnToAction?.order_id }}</span>?
            </p>
            <div v-if="returnToAction" class="mt-4 w-full bg-gray-50 p-3 rounded text-sm">
              <div><span class="font-medium">Customer:</span> {{ returnToAction.customer_name }}</div>
              <div><span class="font-medium">Reason:</span> {{ returnToAction.reason }}</div>
              <div><span class="font-medium">Refund Amount:</span> {{ formatCurrency(returnToAction.refund_amount) }}</div>
            </div>
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
                :class="actionType === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
              >
                {{ actionType === 'approve' ? 'Approve' : 'Reject' }}
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

.bg-navy-700 {
  background-color: #151c63;
}

.bg-navy-900 {
  background-color: #0a0f2d;
}

.hover\:bg-navy-700:hover {
  background-color: #151c63;
}

.bg-navy-100 {
  background-color: #E6E6FF;
}

.text-navy-800 {
  color: #001044;
}

.text-navy-500 {
  color: #3A3F9E;
}
</style>

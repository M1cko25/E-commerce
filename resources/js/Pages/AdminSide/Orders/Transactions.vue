<template>
  <Head title=" | Transactions" />
  <Toast ref="toast" />
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Sidebar Toggle -->
    <Sidebar></Sidebar>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <!-- Header -->
      <Header
        title="Transactions"
        @search="handleHeaderSearch"
        @search-clear="clearHeaderSearch"
        :initial-search-query="headerSearchQuery"
        ref="headerRef"
      ></Header>

      <!-- Transactions Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
          <div class="flex space-x-4">
            <!-- Filter Dropdown -->
            <div class="relative">
              <button
                @click="isFilterOpen = !isFilterOpen"
                class="flex items-center px-4 py-2 border rounded-md bg-white hover:bg-gray-50"
              >
                Filter by {{ currentFilter !== 'all' ? ': ' + getFilterLabel(currentFilter) : '' }}
                <ChevronDownIcon class="ml-2 h-5 w-5" />
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
                class="flex items-center px-4 py-2 border rounded-md bg-white hover:bg-gray-50"
              >
                Sort by: {{ currentSort.label }}
                <ChevronDownIcon class="ml-2 h-5 w-5" />
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
                {{ getFilterLabel(currentFilter) }}
                <button @click="clearFilter" class="ml-1 text-navy-500 hover:text-navy-700">
                  <XIcon class="h-4 w-4" />
                </button>
              </span>
            </div>

            <!-- Active Search Tag -->
            <div v-if="headerSearchQuery" class="flex items-center">
              <span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-md flex items-center">
                Search: "{{ headerSearchQuery }}"
                <button @click="clearHeaderSearch" class="ml-1 text-blue-500 hover:text-blue-700">
                  <XIcon class="h-4 w-4" />
                </button>
              </span>
            </div>
          </div>
        </div>

        <!-- Transactions Table -->
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
              <tr v-for="order in filteredAndSortedOrders" :key="order.id">
                <td class="px-6 py-4 whitespace-nowrap uppercase">{{ order.reference_number }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ order.customer_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatDateTime(order.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ order.total_amount }}</td>
                <td class="px-6 py-4 whitespace-nowrap upSpercase">
                  <span :class="[
                    'px-2 py-1 text-xs rounded-full',
                    order.payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                  ]">
                    {{ order.payment_status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap uppercase">
                  <span :class="[
                    'px-2 py-1 text-xs rounded-full',
                    order.payment_method === 'cod' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800'
                  ]">
                    {{ order.payment_method }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="order.payment_reference_number" class="font-mono text-sm">
                    {{ order.payment_reference_number }}
                  </span>
                  <span v-else class="text-gray-400 text-sm">—</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs rounded-full',
                      order.order_status === 'pending'
                        ? 'bg-yellow-100 text-yellow-800'
                        : order.order_status === 'processing'
                        ? 'bg-blue-100 text-blue-800'
                        : order.order_status === 'cancelled'
                        ? 'bg-red-100 text-red-800'
                        : 'bg-green-100 text-green-800',
                    ]"
                  >
                    {{ order.order_status }}
                  </span>
                </td>

                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <button
                    @click="openActionMenu(order, $event)"
                    class="text-gray-400 hover:text-gray-500 focus:outline-none action-menu-trigger"
                  >
                    <MoreVerticalIcon class="h-5 w-5" />
                  </button>
                </td>
              </tr>
              <tr v-if="filteredAndSortedOrders.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                  No transactions found matching your criteria
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Links -->
        <div class="flex items-center justify-between mt-6">
          <div class="flex items-center">
            <span class="text-sm text-gray-700">
              Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} results
            </span>
          </div>
          <div class="flex gap-2">
            <Link
              v-for="link in orders.links"
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
                <ChevronLeftIcon class="h-5 w-5" />
              </template>
              <template v-else-if="link.label.includes('Next')">
                <ChevronRightIcon class="h-5 w-5" />
              </template>
              <template v-else>
                {{ link.label }}
              </template>
            </Link>
          </div>
        </div>
      </div>
    </main>

    <!-- Action Menu -->
    <div
      v-if="activeActionMenu !== null"
      class="fixed z-50 bg-white border border-gray-200 shadow-lg rounded-md"
      :style="{ top: dropdownPosition.top + 'px', left: dropdownPosition.left + 'px' }"
    >
      <button
        @click="openAcceptModal(activeActionMenu)"
        class="block w-full px-4 py-2 text-sm text-green-600 hover:bg-green-50 text-left"
      >
        Approve
      </button>
      <button
        @click="openDeleteModal(activeActionMenu)"
        class="block w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 text-left"
      >
        Decline
      </button>
    </div>

    <!-- Approve Modal -->
    <div v-if="showAcceptModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full p-6">
          <div class="flex flex-col items-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
              <CheckIcon class="h-6 w-6 text-green-600" />
            </div>
            <h3 class="mt-4 text-lg font-semibold">Approve Transaction</h3>
            <p class="mt-2 text-sm text-gray-500">
              Are you sure you want to do this?
            </p>
            <div class="mt-6 flex gap-4">
              <button
                @click="closeModal"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                @click="confirmAccept"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
              >
                Confirm
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Decline Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full p-6">
          <div class="flex flex-col items-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
              <XIcon class="h-6 w-6 text-red-600" />
            </div>
            <h3 class="mt-4 text-lg font-semibold">Decline Transaction</h3>
            <p class="mt-2 text-sm text-gray-500">
              Are you sure you want to do this?
            </p>
            <div class="mt-6 flex gap-4">
              <button
                @click="closeModal"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                @click="confirmDelete"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
              >
                Confirm
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import {
  MoreVerticalIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  TrashIcon,
  CheckIcon,
  XIcon,
} from "lucide-vue-next";
import Sidebar from "../../../Components/Sidebar.vue";
import Header from "../../../Components/Header.vue";
import Toast from "../../../Components/Toast.vue";

const props = defineProps({
  orders: Object,
});

const headers = ["Order ID", "Customer", "Date", "Total", "Payment Status", "Method", "Ref #", "Status", ""];

const formatDateTime = (date) => {
  if (!date) return "No activity yet";
  return new Date(date).toLocaleString("en-us", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "numeric",
    hour12: true, // Use the 12-hour format
  });
};

const isFilterOpen = ref(false);
const isSortOpen = ref(false);
const showDeleteModal = ref(false);
const showAcceptModal = ref(false);
const activeActionMenu = ref(null);
const dropdownPosition = ref({ top: 0, left: 0 });
const userToDelete = ref(null);
const errorMessage = ref(null);
const toast = ref(null);
const orderToAccept = ref(null);
const orderToDecline = ref(null);
const currentFilter = ref('all');
const headerSearchQuery = ref('');
const headerRef = ref(null);

// Filter options
const filterOptions = [
  { label: 'All Transactions', value: 'all' },
  { label: 'Pending Orders', value: 'pending' },
  { label: 'Processing Orders', value: 'processing' },
  { label: 'Paid Orders', value: 'paid' },
  { label: 'COD Orders', value: 'cod' },
  { label: 'GCash Orders', value: 'gcash' },
  { label: 'High Value (>₱1000)', value: 'high_value' },
  { label: 'Recent (Today)', value: 'today' },
];

// Sort options
const currentSort = ref({
  value: 'created_at',
  direction: 'desc',
  label: 'Date (Newest First)'
});

const sortOptions = [
  { value: 'created_at', label: 'Date' },
  { value: 'total_amount', label: 'Amount' },
  { value: 'customer_name', label: 'Customer Name' },
  { value: 'reference_number', label: 'Order ID' },
];

// Filter and sort the orders
const filteredAndSortedOrders = computed(() => {
  let result = [...props.orders.data];
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  // Apply search filter first
  if (headerSearchQuery.value.trim()) {
    const query = headerSearchQuery.value.toLowerCase();
    result = result.filter(order => {
      return order.reference_number.toLowerCase().includes(query) ||
             order.customer_name.toLowerCase().includes(query) ||
             order.payment_method.toLowerCase().includes(query) ||
             order.order_status.toLowerCase().includes(query);
    });
  }

  // Apply category filters
  if (currentFilter.value !== 'all') {
    switch (currentFilter.value) {
      case 'pending':
        result = result.filter(order => order.order_status === 'pending');
        break;
      case 'processing':
        result = result.filter(order => order.order_status === 'processing');
        break;
      case 'paid':
        result = result.filter(order => order.payment_status === 'paid');
        break;
      case 'cod':
        result = result.filter(order => order.payment_method === 'cod');
        break;
      case 'gcash':
        result = result.filter(order => order.payment_method === 'gcash');
        break;
      case 'high_value':
        result = result.filter(order => parseFloat(order.total_amount) > 1000);
        break;
      case 'today':
        result = result.filter(order => {
          const orderDate = new Date(order.created_at);
          return orderDate >= today;
        });
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
      case 'total_amount':
        comparison = parseFloat(a.total_amount) - parseFloat(b.total_amount);
        break;
      case 'customer_name':
        comparison = a.customer_name.localeCompare(b.customer_name);
        break;
      case 'reference_number':
        comparison = a.reference_number.localeCompare(b.reference_number);
        break;
    }

    return currentSort.value.direction === 'asc' ? comparison : -comparison;
  });

  return result;
});

const handleHeaderSearch = (query) => {
  headerSearchQuery.value = query;
};

const clearHeaderSearch = () => {
  headerSearchQuery.value = '';
  if (headerRef.value) {
    headerRef.value.clearSearch();
  }
};

const applyFilter = (filter) => {
  currentFilter.value = filter;
  isFilterOpen.value = false;
};

const clearFilter = () => {
  currentFilter.value = 'all';
};

const getFilterLabel = (filterValue) => {
  const option = filterOptions.find(option => option.value === filterValue);
  return option ? option.label : filterValue;
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
      label: sort.label + (sort.value === 'total_amount' ? ' (Highest First)' : ' (Newest First)')
    };
  }
  isSortOpen.value = false;
};

onMounted(() => {
  const page = usePage().props;

  if (page.flash) {
    if (page.flash.message && toast.value) {
      toast.value.addToast(page.flash.message, page.flash.type || "success");
    }

    if (page.errors && Object.keys(page.errors).length > 0) {
      const errorMessage = Object.values(page.errors)[0];
      if (toast.value) {
        toast.value.addToast(errorMessage, "error");
      }
    }
  }

  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});

const openActionMenu = (order, event) => {
  if (activeActionMenu.value === order) {
    closeActionMenu();
  } else {
    activeActionMenu.value = order;
    dropdownPosition.value = {
      top: event.target.getBoundingClientRect().bottom + window.scrollY - 70,
      left: event.target.getBoundingClientRect().right - 100 + window.scrollX,
    };
  }
};

const openAcceptModal = (order) => {
  orderToAccept.value = order;
  activeActionMenu.value = null;
  showAcceptModal.value = true;
};

const openDeleteModal = (order) => {
  orderToDecline.value = order;
  activeActionMenu.value = null;
  showDeleteModal.value = true;
};

const closeActionMenu = () => {
  activeActionMenu.value = null;
};

const confirmAccept = () => {
  if (!orderToAccept.value) return;
  console.log(orderToAccept.value);
  router.put(`/orders/${orderToAccept.value.id}/approve`, { 'status': "processing" }, {
    preserveScroll: true,
    onSuccess: () => {
      showAcceptModal.value = false;
      orderToAccept.value = null;
      activeActionMenu.value = null;
      console.log('success');
      if (toast.value) {
        toast.value.addToast('Order approved successfully', 'success');
      }

    },
    onError: (errors) => {
      showAcceptModal.value = false;
      console.log(errors);
      if (errors.error && toast.value) {
        toast.value.addToast(errors.error, 'error');
      }
    },
  });
};

const confirmDelete = () => {
  if (!orderToDecline.value) return;

  router.post(`/orders/${orderToDecline.value.id}/decline`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false;
      orderToDecline.value = null;
      activeActionMenu.value = null;
      if (toast.value) {
        toast.value.addToast('Order declined successfully', 'success');
      }
    },
    onError: (errors) => {
      showDeleteModal.value = false;
      if (errors.error && toast.value) {
        toast.value.addToast(errors.error, 'error');
      }
    },
  });
};

const closeModal = () => {
  showDeleteModal.value = false;
  showAcceptModal.value = false;
  orderToDecline.value = null;
  orderToAccept.value = null;
  activeActionMenu.value = null;
};

const handleClickOutside = (event) => {
  if (!event.target.closest('button') || !event.target.closest('button').contains(ChevronDownIcon)) {
    isFilterOpen.value = false;
    isSortOpen.value = false;
  }

  // Close the action menu if it's open and the user clicks outside
  if (activeActionMenu.value && !event.target.closest('.action-menu-trigger')) {
    closeActionMenu();
  }
};

const makeLabel = (label) => {
  if (label.includes("Previous")) return "<";
  if (label.includes("Next")) return ">";
  return label;
};

const clearError = () => {
  errorMessage.value = null;
};
</script>

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

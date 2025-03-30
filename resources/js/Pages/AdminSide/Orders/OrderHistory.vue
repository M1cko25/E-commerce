<template>
  <Head title=" | Order History" />
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar></Sidebar>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <!-- Header -->
      <Header
        title="Order History"
        @search="handleHeaderSearch"
        @search-clear="clearHeaderSearch"
        :initial-search-query="searchQuery"
        ref="headerRef"
      ></Header>

      <!-- Main Content Area -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Order History Header -->
        <div class="flex justify-between items-center mb-6">
          <div class="flex space-x-4">
            <!-- Filter Dropdown -->
          <div class="relative">
            <button
              @click="isFilterOpen = !isFilterOpen"
              class="flex items-center px-4 py-2 border rounded-md bg-white hover:bg-gray-50"
            >
              <FilterIcon class="h-5 w-5 mr-2" />
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
            <div v-if="searchQuery" class="flex items-center">
              <span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-md flex items-center">
                Search: "{{ searchQuery }}"
                <button @click="clearHeaderSearch" class="ml-1 text-blue-500 hover:text-blue-700">
                  <XIcon class="h-4 w-4" />
                </button>
              </span>
            </div>
          </div>
        </div>

        <!-- Orders Grid -->
        <div v-if="!showOrderDetails">
          <!-- No Orders Message -->
          <div v-if="!filteredAndSortedOrders.length" class="text-center py-12">
            <div class="flex flex-col items-center justify-center">
              <ShoppingBag class="h-12 w-12 text-gray-400 mb-4" />
              <h3 class="text-lg font-medium text-gray-900">No orders found</h3>
              <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or search criteria.</p>
            </div>
          </div>

          <!-- Orders Grid -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="order in filteredAndSortedOrders"
              :key="order.id"
              class="bg-white rounded-lg p-6 border border-gray-200"
            >
              <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold uppercase">{{ order.reference_number }}</h3>
                <span
                  :class="[
                    'px-3 py-1 text-sm font-medium rounded-full',
                    order.order_status === 'delivered' ? 'bg-green-500 text-white' :
                    order.order_status === 'shipped' ? 'bg-blue-500 text-white' :
                    order.order_status === 'cancelled' ? 'bg-red-500 text-white' :
                    'bg-yellow-500 text-white'
                  ]"
                >
                  {{ order.order_status.charAt(0).toUpperCase() + order.order_status.slice(1) }}
                </span>
              </div>
              <p class="text-gray-600">{{ order.customer_name}}</p>
              <p class="text-gray-600">Date: {{ new Date(order.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) }}</p>
              <div class="flex justify-between items-center mt-2">
                <p class="font-semibold text-lg">₱{{ order.total_amount }}</p>
                <span :class="[
                  'px-2 py-1 text-xs rounded-full',
                  order.payment_method === 'cod' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800'
                ]">
                  {{ order.payment_method }}
                </span>
              </div>
              <button
                @click="viewOrderDetails(order)"
                class="w-full mt-4 bg-navy-900 text-white py-2 rounded-lg hover:bg-navy-700"
              >
                View Details
              </button>
            </div>
          </div>
        </div>

        <!-- Order Details View -->
        <div v-else class="bg-white rounded-lg p-6 border border-gray-200">
          <!-- Back Button and Title -->
          <div class="flex items-center gap-2 mb-6">
            <button
              @click="showOrderDetails = false"
              class="flex items-center text-gray-600 hover:text-gray-900"
            >
              <ChevronLeftIcon class="h-5 w-5" />
              <span>Back to Order History</span>
            </button>
          </div>


          <!-- Order Details Header -->
          <div class="mb-8 ">
            <div class="flex justify-between items-start mb-4">
                <h1 class="text-lg font-semibold uppercase">{{ selectedOrder.reference_number }}</h1>
                <span
                  :class="[
                    'px-3 py-1 text-sm font-medium rounded-full',
                    selectedOrder.order_status === 'delivered' ? 'bg-green-500 text-white' :
                    selectedOrder.order_status === 'shipped' ? 'bg-blue-500 text-white' :
                    selectedOrder.order_status === 'cancelled' ? 'bg-red-500 text-white' :
                    'bg-yellow-500 text-white'
                  ]"
                >
                  {{ selectedOrder.order_status.charAt(0).toUpperCase() + selectedOrder.order_status.slice(1) }}
                </span>
              </div>
            <div class="flex justify-between">
              <div>
                <p class="text-gray-600">Customer Name:</p>
                <p class="font-medium">{{ selectedOrder.customer_name }}</p>
                <p class="text-gray-600 mt-4">Order Date:</p>
                <p class="font-medium">{{ selectedOrder.created_at }}</p>
              </div>
              <div class="text-right">
                <p class="text-gray-600">Total:</p>
                <p class="text-2xl font-semibold">₱{{ selectedOrder.total_amount }}</p>
                <p class="text-gray-600 mt-2">Payment Method:</p>
                <span :class="[
                  'px-2 py-1 text-xs rounded-full inline-block mt-1',
                  selectedOrder.payment_method === 'cod' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800'
                ]">
                  {{ selectedOrder.payment_method }}
                </span>
              </div>
            </div>
          </div>

          <!-- Order Items Table -->
          <div class="mb-8 border border-gray-200 rounded-lg">
            <table class="w-full">
              <thead class="">
                <tr class="border-b">
                  <th class="text-left py-3 pl-10 text-gray-600">PRODUCT</th>
                  <th class="text-left py-3 text-gray-600">SKU</th>
                  <th class="text-left py-3 text-gray-600">QUANTITY</th>
                  <th class="text-left py-3 text-gray-600">PRICE</th>
                  <th class="text-left py-3 text-gray-600">TOTAL</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in selectedOrder.items" :key="item.sku" class="border-b">
                  <td class="py-4 pl-10">{{ item.product_name }}</td>
                  <td class="py-4">{{ item.sku }}</td>
                  <td class="py-4">{{ item.quantity }}</td>
                  <td class="py-4">₱{{ item.unit_amount }}</td>
                  <td class="py-4">₱{{ (item.quantity * item.unit_amount).toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Shipping and Status Containers -->
          <div class="grid grid-cols-2 gap-6">
            <!-- Shipping Information -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
              <h3 class="font-semibold mb-4 flex items-center gap-2">Shipping Information <ShoppingBag class="h-5 w-5" /></h3>
              <div class="space-y-4">
                <div>
                  <p class="text-gray-600 mb-1">Address:</p>
                  <p class="font-medium">{{ selectedOrder.shipping_address }}</p>
                </div>
                <div>
                  <p class="text-gray-600 mb-1">Phone Number:</p>
                  <p class="font-medium">{{ selectedOrder.customer_phone_number }}</p>
                </div>
                <div>
                  <p class="text-gray-600 mb-1">Estimated Delivery:</p>
                  <p class="font-medium">{{ selectedOrder.estimatedDelivery || 'To be determined' }}</p>
                </div>
              </div>
            </div>

            <!-- Order Status -->
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
              <h3 class="font-semibold mb-4 flex items-center gap-2">Order Status</h3>
              <p class="font-medium flex items-center gap-2">
                <CircleCheckBig v-if="selectedOrder.order_status === 'delivered'" class="h-5 w-5 text-green-500" />
                <Truck v-else-if="selectedOrder.order_status === 'shipped'" class="h-5 w-5 text-blue-500" />
                <X v-else-if="selectedOrder.order_status === 'cancelled'" class="h-5 w-5 text-red-500" />
                <Loader2 v-else-if="selectedOrder.order_status === 'processing'" class="h-5 w-5 text-yellow-500 animate-spin" />
                {{ selectedOrder.order_status === 'completed' ? 'Delivered' : selectedOrder.order_status.charAt(0).toUpperCase() + selectedOrder.order_status.slice(1) }}
              </p>
              <p class="text-gray-600 mt-2">
                Your order has been {{ selectedOrder.order_status === 'completed' ? 'delivered' : selectedOrder.order_status }}
              </p>
            </div>
          </div>
        </div>
        <!-- Pagination Links -->
        <div v-if="filteredAndSortedOrders.length && !showOrderDetails" class="flex items-center justify-between mt-6">
          <div class="flex items-center">
            <span class="text-sm text-gray-700">
              Showing {{ filteredAndSortedOrders.length }} result(s)
            </span>
          </div>
          <div class="flex gap-2">
            <Link
              v-for="link in props.order.links"
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, usePage, Link } from '@inertiajs/vue3'
import Sidebar from '../../../Components/Sidebar.vue'
import Header from '../../../Components/Header.vue'
import {
  SearchIcon,
  FilterIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ShoppingBag,
  CircleCheckBig,
  Truck,
  X,
  Loader2,
  XIcon
} from 'lucide-vue-next'

const props = defineProps({
  order: Object,
})

const page = usePage()

// State
const searchQuery = ref('')
const isFilterOpen = ref(false)
const isSortOpen = ref(false)
const currentFilter = ref('all')
const showOrderDetails = ref(false)
const selectedOrder = ref(null)
const headerRef = ref(null)

// Filter options
const filterOptions = [
  { label: 'All Orders', value: 'all' },
  { label: 'Processing', value: 'processing' },
  { label: 'Shipped', value: 'shipped' },
  { label: 'Delivered', value: 'delivered' },
  { label: 'Cancelled', value: 'cancelled' },
  { label: 'COD Orders', value: 'cod' },
  { label: 'GCash Orders', value: 'gcash' },
  { label: 'High Value (>₱1000)', value: 'high_value' },
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
  { value: 'total_amount', label: 'Amount' },
  { value: 'customer_name', label: 'Customer Name' },
  { value: 'reference_number', label: 'Order ID' },
];

// Filter and sort the orders
const filteredAndSortedOrders = computed(() => {
  let result = [...props.order.data];

  // First apply text search
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(order => {
      return order.reference_number.toLowerCase().includes(query) ||
             order.customer_name.toLowerCase().includes(query) ||
             (order.payment_method && order.payment_method.toLowerCase().includes(query)) ||
             order.order_status.toLowerCase().includes(query);
    });
  }

  // Then apply filter
  if (currentFilter.value !== 'all') {
    const oneWeekAgo = new Date();
    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);

    switch (currentFilter.value) {
      case 'processing':
        result = result.filter(order => order.order_status === 'processing');
        break;
      case 'shipped':
        result = result.filter(order => order.order_status === 'shipped');
        break;
      case 'delivered':
        result = result.filter(order => order.order_status === 'delivered');
        break;
      case 'cancelled':
        result = result.filter(order => order.order_status === 'cancelled');
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
      case 'recent':
        result = result.filter(order => {
          const orderDate = new Date(order.created_at);
          return orderDate >= oneWeekAgo;
        });
        break;
    }
  }

  // Finally, apply sorting
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
})

// Methods
const viewOrderDetails = (order) => {
  selectedOrder.value = order
  showOrderDetails.value = true
}

const handleHeaderSearch = (query) => {
  searchQuery.value = query;
};

const clearHeaderSearch = () => {
  searchQuery.value = '';
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

const getFilterLabel = (filterValue) => {
  const option = filterOptions.find(option => option.value === filterValue);
  return option ? option.label : filterValue;
};

const handleClickOutside = (event) => {
  if (!event.target.closest('button') || !event.target.closest('button').contains(ChevronDownIcon)) {
    isFilterOpen.value = false;
    isSortOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style>
/* Add any additional styles here if needed */
.bg-navy-900 {
  background-color: #0A1E4C;
}
.bg-navy-700 {
  background-color: #151c63;
}
.bg-navy-100 {
  background-color: #E6E6FF;
}
.text-navy-800 {
  color: #001044;
}
.text-navy-700 {
  color: #151c63;
}
.text-navy-500 {
  color: #3A3F9E;
}
</style>

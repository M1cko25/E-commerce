<template>
  <Head title=" | Product" />
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Sidebar Toggle -->
    <Sidebar></Sidebar>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <!-- Header -->
      <Header
        title="Products"
        @search="handleHeaderSearch"
        @search-clear="clearHeaderSearch"
        :initial-search-query="searchQuery"
        ref="headerRef"
      ></Header>

      <!-- Product Content -->
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
            <div v-if="searchQuery" class="flex items-center">
              <span class="px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-md flex items-center">
                Search: "{{ searchQuery }}"
                <button @click="clearHeaderSearch" class="ml-1 text-blue-500 hover:text-blue-700">
                  <XIcon class="h-4 w-4" />
                </button>
              </span>
            </div>
          </div>

          <button class="px-4 py-2 bg-navy-600 text-white rounded-md hover:bg-navy-700">
            <Link :href="route('products.create')"> New Product </Link>
          </button>
        </div>

        <!-- Product Table -->
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
              <tr v-for="(product, index) in filteredAndSortedProducts" :key="product.id">
                <td class="px-6 py-4 whitespace-nowrap">{{ product.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ product.sku }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ product.category ? product.category.name : "N/A" }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ product.brand ? product.brand.name : "N/A" }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ product.price }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ product.stock }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div
                    class="h-3 w-3 rounded-full hover:cursor-help"
                    :class="product.stock > 10 ? 'bg-green-500' : 'bg-red-500'"
                    :title="product.stock > 10 ? 'Available' : 'Low Stock / Out of Stock'"
                  ></div>
                </td>
                <td class="px-6 py-4 h-20 whitespace-nowrap text-right relative">
                  <button
                    @click="openActionMenu(index, $event)"
                    class="text-gray-400 hover:text-gray-500 focus:outline-none"
                  >
                    <MoreVerticalIcon class="h-5 w-5" />
                  </button>
                  <!-- Action Menu -->
                  <div
                    v-if="activeActionMenu === index"
                    class="absolute z-50 bg-white border border-gray-200 shadow-lg rounded-md"
                    style="top: 0; right: 50%; margin-right: 0.5rem;"
                    ref="actionMenu"
                  >
                    <Link :href="route('products.edit', filteredAndSortedProducts[index].id)">
                      <button
                        class="block w-full px-4 py-2 text-sm text-green-500 hover:bg-green-100 text-left"
                      >
                        Edit
                      </button>
                    </Link>
                    <button
                      @click="openDeleteModal(filteredAndSortedProducts[index])"
                      class="block w-full px-4 py-2 text-sm text-red-600 hover:bg-red-100 text-left"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredAndSortedProducts.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                  No products found matching your criteria
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Links-->
        <div class="flex items-center justify-between mt-6">
          <div class="flex items-center">
            <span class="text-sm text-gray-700"
              >Showing {{ products.from }} to {{ products.to }} of
              {{ products.total }} results</span
            >
          </div>
          <div class="flex gap-2">
            <Link
              v-for="link in products.links"
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

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="relative bg-white rounded-lg max-w-md w-full p-6">
          <div class="flex flex-col items-center">
            <div
              class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100"
            >
              <TrashIcon class="h-6 w-6 text-red-600" />
            </div>
            <h3 class="mt-4 text-lg font-semibold">Delete Item</h3>
            <p class="mt-2 text-sm text-gray-500">Are you sure you want to do this?</p>
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
import { Link, router } from "@inertiajs/vue3";
import {
  MoreVerticalIcon,
  ChevronDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  TrashIcon,
  XIcon,
} from "lucide-vue-next";
import Sidebar from "../../../Components/Sidebar.vue";
import Header from "../../../Components/Header.vue";
import { route } from "../../../../../vendor/tightenco/ziggy/src/js";

const props = defineProps({
  products: Object,
});

const isFilterOpen = ref(false);
const isSortOpen = ref(false);
const showDeleteModal = ref(false);
const productToDelete = ref(null);
const currentFilter = ref('all');
const searchQuery = ref('');
const headerRef = ref(null);

const headers = ["NAME", "SKU", "CATEGORY", "BRAND", "PRICE", "STOCKS",  "", ""];

const activeActionMenu = ref(null);
const dropdownPosition = ref({ top: 0, left: 0 });
const actionMenu = ref(null);

// Filter options
const filterOptions = [
  { label: 'All Products', value: 'all' },
  { label: 'Low Stock (≤10)', value: 'low_stock' },
  { label: 'In Stock (>10)', value: 'in_stock' },
  { label: 'Out of Stock', value: 'out_of_stock' },
  { label: 'Recently Added', value: 'recent' },
  { label: 'High Price (>₱100)', value: 'high_price' },
  { label: 'Budget (<₱50)', value: 'budget' },
];

// Sort options
const currentSort = ref({
  value: 'name',
  direction: 'asc',
  label: 'Name (A-Z)'
});

const sortOptions = [
  { value: 'name', label: 'Name' },
  { value: 'price', label: 'Price' },
  { value: 'stock', label: 'Stock Level' },
  { value: 'sku', label: 'SKU' },
  { value: 'created_at', label: 'Date Added' },
];

// Filter and sort the products
const filteredAndSortedProducts = computed(() => {
  let result = [...props.products.data];

  // Apply search filter first
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(product => {
      return product.name.toLowerCase().includes(query) ||
             product.sku.toLowerCase().includes(query) ||
             (product.description && product.description.toLowerCase().includes(query)) ||
             (product.category && product.category.name.toLowerCase().includes(query)) ||
             (product.brand && product.brand.name.toLowerCase().includes(query));
    });
  }

  // Apply filters
  if (currentFilter.value !== 'all') {
    switch (currentFilter.value) {
      case 'low_stock':
        result = result.filter(product => product.stock > 0 && product.stock <= 10);
        break;
      case 'in_stock':
        result = result.filter(product => product.stock > 10);
        break;
      case 'out_of_stock':
        result = result.filter(product => product.stock === 0);
        break;
      case 'recent':
        // Filter to last 7 days
        const lastWeek = new Date();
        lastWeek.setDate(lastWeek.getDate() - 7);
        result = result.filter(product => new Date(product.created_at) >= lastWeek);
        break;
      case 'high_price':
        result = result.filter(product => parseFloat(product.price) > 100);
        break;
      case 'budget':
        result = result.filter(product => parseFloat(product.price) < 50);
        break;
    }
  }

  // Apply sorting
  result.sort((a, b) => {
    let comparison = 0;

    switch (currentSort.value.value) {
      case 'name':
        comparison = a.name.localeCompare(b.name);
        break;
      case 'price':
        comparison = parseFloat(a.price) - parseFloat(b.price);
        break;
      case 'stock':
        comparison = a.stock - b.stock;
        break;
      case 'sku':
        comparison = a.sku.localeCompare(b.sku);
        break;
      case 'created_at':
        comparison = new Date(a.created_at) - new Date(b.created_at);
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
      label: sort.label + (currentSort.value.direction === 'asc' ? ' (High-Low)' : ' (Low-High)')
    };
  } else {
    // New sort option
    currentSort.value = {
      ...sort,
      direction: 'asc',
      label: sort.label
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
  productToDelete.value = item;
  activeActionMenu.value = null;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  router.delete(route("products.destroy", productToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
      activeActionMenu.value = null;
    },
    onError: (errors) => {
      console.error(errors);
    },
  });
};

const logout = () => {
  // Implement logout logic
};

const getDate = (date) =>
  new Date(date).toLocaleDateString("en-us", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });

const makeLabel = (label) => {
  if (label.includes("Previous")) return "<";
  if (label.includes("Next")) return ">";
  return label;
};

const handleClickOutside = (event) => {
  if (actionMenu.value && !actionMenu.value.contains(event.target) && !event.target.closest('button')) {
    closeActionMenu();
  }

  // Also close filter and sort dropdowns when clicking outside
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

// Methods for search functionality
const handleHeaderSearch = (query) => {
  searchQuery.value = query;
};

const clearHeaderSearch = () => {
  searchQuery.value = '';
  if (headerRef.value) {
    headerRef.value.clearSearch();
  }
};

const getFilterLabel = (filterValue) => {
  const option = filterOptions.find(option => option.value === filterValue);
  return option ? option.label : filterValue;
};
</script>
<style scoped>
.bg-navy-600 {
  background-color: #001044;
}

.bg-navy-700 {
  background-color: #151c63;
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

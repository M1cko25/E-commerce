<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
  MoreVerticalIcon,
  ChevronDownIcon,
  TrashIcon,
  XIcon,
} from "lucide-vue-next";
import Sidebar from "../../../Components/Sidebar.vue";
import Header from "../../../Components/Header.vue";
import Toast from "@/Components/Toast.vue";

const props = defineProps({
  promotions: Array,
});

const headers = ["NAME", "CODE", "TYPE", "DISCOUNT", "DESCRIPTION", ""];
const isFilterOpen = ref(false);
const isSortOpen = ref(false);
const showDeleteModal = ref(false);
const promotionToDelete = ref(null);
const activeActionMenu = ref(null);
const currentFilter = ref('all');

// Filter options
const filterOptions = [
  { label: 'All Promotions', value: 'all' },
  { label: 'Item Discounts', value: 'item' },
  { label: 'Shipping Discounts', value: 'shipping' },
  { label: 'High Discount (>30%)', value: 'high_discount' },
  { label: 'Medium Discount (10-30%)', value: 'medium_discount' },
  { label: 'Low Discount (<10%)', value: 'low_discount' },
];

// Sort options
const currentSort = ref({
  value: 'name',
  direction: 'asc',
  label: 'Name (A-Z)'
});

const sortOptions = [
  { value: 'name', label: 'Name' },
  { value: 'discount', label: 'Discount %' },
  { value: 'code', label: 'Promo Code' },
];

const filteredAndSortedPromotions = computed(() => {
  let result = [...props.promotions];

  // Apply filters
  if (currentFilter.value !== 'all') {
    switch (currentFilter.value) {
      case 'item':
      case 'shipping':
        result = result.filter(promo => promo.type === currentFilter.value);
        break;
      case 'high_discount':
        result = result.filter(promo => promo.discount > 30);
        break;
      case 'medium_discount':
        result = result.filter(promo => promo.discount >= 10 && promo.discount <= 30);
        break;
      case 'low_discount':
        result = result.filter(promo => promo.discount < 10);
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
      case 'discount':
        comparison = a.discount - b.discount;
        break;
      case 'code':
        comparison = a.code.localeCompare(b.code);
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

const openActionMenu = (index) => {
  activeActionMenu.value = activeActionMenu.value === index ? null : index;
};

const openDeleteModal = (promotion) => {
  promotionToDelete.value = promotion;
  showDeleteModal.value = true;
  activeActionMenu.value = null;
};

const confirmDelete = () => {
  router.delete(route("promotions.destroy", promotionToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
      promotionToDelete.value = null;
    },
  });
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

<template>
  <Head title=" | Promotions" />
  <Toast ref="toast" />
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Sidebar Toggle -->
    <Sidebar></Sidebar>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <!-- Header -->
      <Header title="Promotions"></Header>

      <!-- Promotions Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-6">
          <div class="flex space-x-4">
            <!-- Filter Dropdown -->
            <div class="relative">
              <button
                @click="isFilterOpen = !isFilterOpen"
                class="flex items-center px-4 py-2 border rounded-md bg-white hover:bg-gray-50"
              >
                Filter by {{ currentFilter !== 'all' ? ': ' + currentFilter : '' }}
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
                {{ currentFilter }}
                <button @click="clearFilter" class="ml-1 text-navy-500 hover:text-navy-700">
                  <XIcon class="h-4 w-4" />
                </button>
              </span>
            </div>
          </div>

          <button class="px-4 py-2 bg-navy-600 text-white rounded-md hover:bg-navy-700">
            <Link :href="route('promotions.create')"> New Promotion </Link>
          </button>
        </div>

        <!-- Promotions Table -->
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
              <tr v-for="(promotion, index) in filteredAndSortedPromotions" :key="promotion.id">
                <td class="px-6 py-4 whitespace-nowrap">{{ promotion.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ promotion.code }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'px-2 py-1 text-xs rounded-full',
                    promotion.type === 'item'
                      ? 'bg-blue-100 text-blue-800'
                      : 'bg-green-100 text-green-800'
                  ]">
                    {{ promotion.type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'font-medium',
                    promotion.discount > 30
                      ? 'text-red-600'
                      : promotion.discount > 10
                        ? 'text-orange-500'
                        : 'text-green-600'
                  ]">
                    {{ promotion.discount }}%
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ promotion.description }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-right relative">
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
                    <Link :href="route('promotions.edit', promotion.id)">
                      <button
                        class="block w-full px-4 py-2 text-sm text-green-500 hover:bg-green-100 text-left"
                      >
                        Edit
                      </button>
                    </Link>
                    <button
                      @click="openDeleteModal(promotion)"
                      class="block w-full px-4 py-2 text-sm text-red-600 hover:bg-red-100 text-left"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredAndSortedPromotions.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                  No promotions found matching your criteria
                </td>
              </tr>
            </tbody>
          </table>
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
            <h3 class="mt-4 text-lg font-semibold">Delete Promotion</h3>
            <p class="mt-2 text-sm text-gray-500">Are you sure you want to delete this promotion?</p>
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
  </div>
</template>

<style scoped>
.bg-navy-600 {
  background-color: #1a237e;
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

<template>
  <Head title=" | Brand" />
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Sidebar Toggle -->
    <Sidebar></Sidebar>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <!-- Header -->
      <Header title="Brand"></Header>

      <!-- Brand Content -->
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
            <Link :href="route('brands.create')"> New Brand </Link>
          </button>
        </div>

        <!-- Brand Table -->
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
              <tr v-for="(brand, index) in filteredAndSortedBrands" :key="brand.id">
                <td class="px-6 py-4 whitespace-nowrap">{{ brand.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <img
                    :src="brand.image ? '/storage/' + brand.image : 'storage/default.jpg'"
                    class="h-10 w-24 object-cover rounded-md shadow-sm"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">{{ brand.slug }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ getDate(brand.created_at) }}
                </td>
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
                    style="top: 0; right: 100%; margin-right: 0.5rem;"
                    ref="actionMenu"
                  >
                    <Link :href="route('brands.edit', filteredAndSortedBrands[index].id)">
                      <button
                        class="block w-full px-4 py-2 text-sm text-green-500 hover:bg-green-100 text-left"
                      >
                        Edit
                      </button>
                    </Link>
                    <button
                      @click="openDeleteModal(filteredAndSortedBrands[index])"
                      class="block w-full px-4 py-2 text-sm text-red-600 hover:bg-red-100 text-left"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredAndSortedBrands.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                  No brands found matching your criteria
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Links -->
        <div class="flex items-center justify-between mt-6">
          <div class="flex items-center">
            <span class="text-sm text-gray-700"
              >Showing {{ brands.from }} to {{ brands.to }} of
              {{ brands.total }} results</span
            >
          </div>
          <div class="flex gap-2">
            <Link
              v-for="link in brands.links"
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
  brands: Object,
});

const headers = ["NAME", "IMG", "SLUG", "CREATED_AT", ""];

const isFilterOpen = ref(false);
const isSortOpen = ref(false);
const showDeleteModal = ref(false);
const brandToDelete = ref(null);
const currentFilter = ref('all');

const activeActionMenu = ref(null);
const dropdownPosition = ref({ top: 0, left: 0 });
const actionMenu = ref(null);

// Filter options
const filterOptions = [
  { label: 'All Brands', value: 'all' },
  { label: 'Recently Added', value: 'recent' },
  { label: 'Has Image', value: 'has_image' },
  { label: 'No Image', value: 'no_image' },
  { label: 'Alphabetical (A-E)', value: 'a_to_e' },
  { label: 'Alphabetical (F-J)', value: 'f_to_j' },
  { label: 'Alphabetical (K-O)', value: 'k_to_o' },
  { label: 'Alphabetical (P-Z)', value: 'p_to_z' },
];

// Sort options
const currentSort = ref({
  value: 'name',
  direction: 'asc',
  label: 'Name (A-Z)'
});

const sortOptions = [
  { value: 'name', label: 'Name' },
  { value: 'created_at', label: 'Date Added' },
];

// Filter and sort the brands
const filteredAndSortedBrands = computed(() => {
  let result = [...props.brands.data];

  // Apply filters
  if (currentFilter.value !== 'all') {
    switch (currentFilter.value) {
      case 'recent':
        // Filter to last 7 days
        const lastWeek = new Date();
        lastWeek.setDate(lastWeek.getDate() - 7);
        result = result.filter(brand => new Date(brand.created_at) >= lastWeek);
        break;
      case 'has_image':
        result = result.filter(brand => brand.image);
        break;
      case 'no_image':
        result = result.filter(brand => !brand.image);
        break;
      case 'a_to_e':
        result = result.filter(brand => {
          const firstChar = brand.name.charAt(0).toLowerCase();
          return firstChar >= 'a' && firstChar <= 'e';
        });
        break;
      case 'f_to_j':
        result = result.filter(brand => {
          const firstChar = brand.name.charAt(0).toLowerCase();
          return firstChar >= 'f' && firstChar <= 'j';
        });
        break;
      case 'k_to_o':
        result = result.filter(brand => {
          const firstChar = brand.name.charAt(0).toLowerCase();
          return firstChar >= 'k' && firstChar <= 'o';
        });
        break;
      case 'p_to_z':
        result = result.filter(brand => {
          const firstChar = brand.name.charAt(0).toLowerCase();
          return firstChar >= 'p' && firstChar <= 'z';
        });
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
      label: sort.label + (currentSort.value.direction === 'asc' ? ' (Z-A)' : ' (A-Z)')
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

const openDeleteModal = (brand) => {
  brandToDelete.value = brand;
  closeActionMenu();
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  router.delete(route("brands.destroy", brandToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
      closeActionMenu();
    },
  });
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

const makeLabel = (label) => {
  if (label.includes("Previous")) return "<";
  if (label.includes("Next")) return ">";
  return label;
};

const getDate = (date) =>
  new Date(date).toLocaleDateString("en-us", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
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

<template>
  <header class="bg-white shadow-sm">
    <div
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center"
    >
      <!-- Dynamic Title -->
      <h1 class="text-2xl font-semibold text-gray-900">{{ title }}</h1>

      <div class="flex items-center gap-4">
        <!-- Search Input (Optional) -->
        <div v-if="showSearch" class="relative">
          <input
            type="text"
            placeholder="Search"
            class="w-full sm:w-64 pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            v-model="searchQuery"
            @input="handleSearch"
            @keyup.enter="handleSearchEnter"
          />
          <SearchIcon class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
          <button
            v-if="searchQuery"
            @click="clearSearch"
            class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 hover:text-gray-600"
          >
            <XIcon class="h-4 w-4" />
          </button>
        </div>

        <!-- Additional Actions Slot -->
        <slot name="actions"></slot>

        <!-- Profile Section -->
        <div v-if="user" class="relative">
          <div
            class="flex items-center justify-center w-10 h-10 rounded-full bg-navy-900 text-white cursor-pointer"
          >
            <button
              @click="toggleProfile"
              class="text-sm font-medium focus:outline-none w-full h-full rounded-full flex items-center justify-center"
            >
              {{ user.charAt(0).toUpperCase() }}
              <!-- Display the first letter of the user's name -->
            </button>
          </div>

          <div
            v-if="isProfileOpen"
            class="absolute right-0 mt-2 w-45 bg-white border rounded-lg shadow-lg z-50"
          >
            <slot name="profile-menu">
              <Link
                method="post"
                as="button"
                :href="route('admin.logout')"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                Logout
              </Link>
            </slot>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { SearchIcon, XIcon } from "lucide-vue-next";
import { usePage, Link } from "@inertiajs/vue3";

// Fetch page props using Inertia.js
const page = usePage();

// Safely access user's first name or default to "Guest"
const user = computed(() => {
  return page.props.auth?.user?.first_name || "Guest";
});

// Props for dynamic content
const props = defineProps({
  title: {
    type: String,
    required: true,
    default: "Dashboard",
  },
  showSearch: {
    type: Boolean,
    default: true,
  },
  initialSearchQuery: {
    type: String,
    default: "",
  }
});

// Emit events that parent components can listen to
const emit = defineEmits(['search', 'search-enter', 'search-clear']);

// State for search query and profile menu
const searchQuery = ref(props.initialSearchQuery || "");
const isProfileOpen = ref(false);

// Watch for changes to initialSearchQuery prop
watch(() => props.initialSearchQuery, (newValue) => {
  searchQuery.value = newValue;
});

// Methods
const toggleProfile = () => {
  isProfileOpen.value = !isProfileOpen.value;
};

const handleSearch = () => {
  emit('search', searchQuery.value);
};

const handleSearchEnter = () => {
  emit('search-enter', searchQuery.value);
};

const clearSearch = () => {
  searchQuery.value = '';
  emit('search-clear');
};

// Expose search query to parent components
defineExpose({
  searchQuery,
  clearSearch
});
</script>

<style scoped>
/* Additional scoped styles can be added here */
</style>

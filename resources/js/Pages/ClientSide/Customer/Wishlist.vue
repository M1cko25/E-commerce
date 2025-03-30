<template>
  <Head title="My Wishlist" />
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
            <h2 class="text-xl font-semibold">My Wishlist</h2>
            <Link
              :href="route('product.list')"
              class="text-navy-600 hover:text-navy-800 text-sm"
            >
              <span class="flex items-center gap-1">
                <ShoppingBag class="w-4 h-4" />
                Continue Shopping
              </span>
            </Link>
          </div>

          <!-- Empty state -->
          <div v-if="!wishlistItems.length" class="text-center py-12">
            <Heart class="w-16 h-16 mx-auto text-gray-300 mb-4" />
            <h3 class="text-lg font-medium text-gray-900">Your wishlist is empty</h3>
            <p class="text-gray-500 mt-1 max-w-md mx-auto">
              Add items to your wishlist by clicking the heart icon on products you're interested in.
            </p>
            <Link
              :href="route('product.list')"
              class="mt-4 inline-block px-4 py-2 bg-navy-600 text-white rounded-lg hover:bg-navy-700"
            >
              Browse Products
            </Link>
          </div>

          <!-- Product Grid -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="item in wishlistItems"
              :key="item.id"
              class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow"
            >
              <!-- Product Image -->
              <Link :href="route('product.view', item.product.slug)">
                <div class="h-48 bg-gray-100 overflow-hidden relative">
                  <img
                    v-if="item.product.image"
                    :src="getImageUrl(item.product.image)"
                    :alt="item.product.name"
                    class="w-full h-full object-contain"
                  />
                  <div v-else class="flex items-center justify-center h-full">
                    <ImageIcon class="w-12 h-12 text-gray-300" />
                  </div>

                  <!-- Stock Badge -->
                  <div
                    v-if="item.product.stock <= 0"
                    class="absolute top-2 right-2 bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded"
                  >
                    Out of Stock
                  </div>
                  <div
                    v-else-if="item.product.stock <= 5"
                    class="absolute top-2 right-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded"
                  >
                    Low Stock
                  </div>
                </div>
              </Link>

              <!-- Product Info -->
              <div class="p-4">
                <div class="flex justify-between">
                  <span class="text-xs text-gray-500">{{ item.product.category }}</span>
                  <span class="text-xs text-gray-500">{{ item.product.brand }}</span>
                </div>

                <Link :href="route('product.view', item.product.slug)" class="block mt-1">
                  <h3 class="font-medium text-gray-900 hover:text-navy-600 line-clamp-2">
                    {{ item.product.name }}
                  </h3>
                </Link>

                <div class="mt-2 flex justify-between items-center">
                  <div class="font-semibold text-gray-900">
                    ₱{{ formatPrice(item.product.price) }}
                  </div>

                  <div class="flex gap-2">
                    <button
                      @click="removeFromWishlist(item.id)"
                      class="p-1.5 text-gray-500 hover:text-red-500 rounded-full hover:bg-gray-100"
                      title="Remove from wishlist"
                    >
                      <Trash class="w-4 h-4" />
                    </button>

                    <button
                      v-if="item.product.stock > 0"
                      @click="addToCart(item.product.id)"
                      class="p-1.5 text-gray-500 hover:text-navy-600 rounded-full hover:bg-gray-100"
                      title="Add to cart"
                    >
                      <ShoppingCart class="w-4 h-4" />
                    </button>
                  </div>
                </div>

                <div class="mt-3 text-xs text-gray-500">
                  Added on {{ item.added_at }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { Menu, X, Heart, ShoppingBag, ShoppingCart, Trash, Image as ImageIcon } from "lucide-vue-next";
import ProfileSidebar from "@/Components/ProfileSidebar.vue";
import NavLink from "@/Components/NavLink.vue";

const props = defineProps({
  customer: Object,
  wishlistItems: Array,
});

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const getImageUrl = (path) => {
  if (!path) return null;
  // Check if the path already has a domain
  if (path.startsWith('http')) {
    return path;
  }
  return `/storage/${path}`;
};

const formatPrice = (price) => {
  return Number(price).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

const removeFromWishlist = (id) => {
  if (confirm('Are you sure you want to remove this item from your wishlist?')) {
    router.delete(route('customer.wishlist.destroy', id));
  }
};

const addToCart = (productId) => {
  // Implement your cart logic here
  router.post(route('cart.add'), {
    product_id: productId,
    quantity: 1
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Show a success message or update cart count
    }
  });
};
</script>

<style scoped>
.bg-navy-600 {
  background-color: #1a237e;
}

.bg-navy-700 {
  background-color: #151b60;
}

.text-navy-600 {
  color: #1a237e;
}

.text-navy-800 {
  color: #0d1147;
}

.hover\:bg-navy-700:hover {
  background-color: #151b60;
}

.hover\:text-navy-600:hover {
  color: #1a237e;
}
</style>

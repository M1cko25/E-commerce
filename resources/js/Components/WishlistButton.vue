<template>
  <button
    @click.prevent.stop="toggleWishlist"
    :class="[
      'p-2 rounded-full transition-colors',
      isInWishlist
        ? 'text-red-500 hover:bg-red-50'
        : 'text-gray-400 hover:text-red-500 hover:bg-gray-100',
      size === 'small' ? 'text-sm' : '',
      size === 'large' ? 'text-lg' : '',
      buttonClass
    ]"
    :aria-label="isInWishlist ? 'Remove from wishlist' : 'Add to wishlist'"
    :title="isInWishlist ? 'Remove from wishlist' : 'Add to wishlist'"
  >
    <span class="sr-only">{{ isInWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}</span>
    <HeartIcon v-if="isInWishlist" :class="iconClass" :stroke-width="1.5" fill="currentColor" />
    <HeartIcon v-else :class="iconClass" :stroke-width="1.5" />
  </button>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { Heart as HeartIcon } from "lucide-vue-next";
import { usePage, router } from "@inertiajs/vue3";

const props = defineProps({
  productId: {
    type: Number,
    required: true
  },
  initialWishlistState: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'medium', // small, medium, large
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  buttonClass: {
    type: String,
    default: ''
  },
  iconClass: {
    type: String,
    default: 'w-5 h-5'
  }
});

const isInWishlist = ref(props.initialWishlistState);
const auth = usePage().props.auth;

const toggleWishlist = () => {
  // Guard against guest users
  if (!auth.customer) {
    window.location.href = route('customer.login');
    return;
  }

  // Toggle the UI state immediately for better UX
  isInWishlist.value = !isInWishlist.value;

  // Send the request to the server
  router.post(route('customer.wishlist.toggle'), {
    product_id: props.productId
  }, {
    preserveScroll: true,
    onSuccess: (response) => {
      // The state is already updated in the UI
    },
    onError: (errors) => {
      // Revert the UI state if there's an error
      isInWishlist.value = !isInWishlist.value;
      console.error('Failed to update wishlist', errors);
    }
  });
};

// Check if the product is in the wishlist on component mount
onMounted(() => {
  isInWishlist.value = props.initialWishlistState;
});
</script>

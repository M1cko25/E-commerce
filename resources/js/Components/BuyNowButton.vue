<template>
  <button
    @click.prevent="buyNow"
    title="Buy Now"
    :class="[
      'flex items-center justify-center gap-2 py-2 px-4 rounded-lg transition-colors',
      size === 'sm' ? 'text-sm' : '',
      size === 'lg' ? 'text-lg py-3 px-5' : '',
      fullWidth ? 'w-full' : '',
      buttonClass || 'bg-red-600 hover:bg-red-700 text-white font-semibold'
    ]"
  >
    <ShoppingBag v-if="showIcon" :class="iconClass || 'w-5 h-5'" />
  </button>
</template>

<script setup>
import { ShoppingBag } from "lucide-vue-next";
import { router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
  productId: {
    type: Number,
    required: true
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
    validator: (val) => ['sm', 'md', 'lg'].includes(val)
  },
  fullWidth: {
    type: Boolean,
    default: false
  },
  productPrice: {
    type: Number,
    required: true
  },
  buttonClass: String,
  iconClass: String,
  label: String,
  showIcon: {
    type: Boolean,
    default: true
  }
});

const auth = usePage().props.auth;

const buyNow = () => {
  // Redirect to login if not authenticated
  if (!auth.customer) {
    window.location.href = route('customer.login');
    return;
  }

  // Add item to cart and redirect to checkout
  router.post(route('cart.add'), {
    product_id: props.productId,
    price: props.productPrice,
    quantity: 1,
    buy_now: true // Special flag to indicate direct checkout
  }, {
    onSuccess: () => {
      // Redirect to checkout
      window.location.href = route('customer.checkout');
    }
  });
};
</script>

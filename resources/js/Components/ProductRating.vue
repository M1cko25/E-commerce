<template>
  <div class="rating-component">
    <!-- Read-only rating display -->
    <div v-if="readOnly" class="flex items-center">
      <div class="flex">
        <span
          v-for="star in 5"
          :key="`display-${star}`"
          class="mr-0.5"
        >
          <Star
            :class="[
              star <= modelValue
                ? 'text-yellow-400 fill-current'
                : 'text-gray-300',
              size === 'sm' ? 'h-3 w-3' : size === 'md' ? 'h-4 w-4' : 'h-5 w-5'
            ]"
            :stroke-width="1.5"
          />
        </span>
      </div>
      <span v-if="showCount" class="text-gray-500 text-xs ml-1">
        ({{ count }})
      </span>
    </div>

    <!-- Interactive rating input -->
    <div v-else class="flex items-center">
      <div class="flex">
        <button
          v-for="star in 5"
          :key="`input-${star}`"
          type="button"
          @click="rate(star)"
          @mouseover="hoverRating = star"
          @mouseleave="hoverRating = 0"
          class="mr-0.5 focus:outline-none transition-colors duration-200"
          :aria-label="`Rate ${star} stars`"
        >
          <Star
            :class="[
              (hoverRating >= star || modelValue >= star)
                ? 'text-yellow-400 fill-current'
                : 'text-gray-300',
              size === 'sm' ? 'h-3 w-3' : size === 'md' ? 'h-4 w-4' : 'h-5 w-5'
            ]"
            :stroke-width="1.5"
          />
        </button>
      </div>
      <span v-if="showCount" class="text-gray-500 text-xs ml-1">
        ({{ count }})
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, computed } from 'vue';
import { Star } from 'lucide-vue-next';

const props = defineProps({
  modelValue: {
    type: Number,
    default: 0
  },
  readOnly: {
    type: Boolean,
    default: false
  },
  count: {
    type: Number,
    default: 0
  },
  showCount: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'md', // 'sm', 'md', 'lg'
    validator: (val) => ['sm', 'md', 'lg'].includes(val)
  }
});

const emit = defineEmits(['update:modelValue', 'rate']);

const hoverRating = ref(0);

const rate = (value) => {
  emit('update:modelValue', value);
  emit('rate', value);
};
</script>

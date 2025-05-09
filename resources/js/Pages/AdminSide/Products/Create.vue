<template>
    <Head title=" | Create Product"></Head>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Sidebar Toggle -->
    <Sidebar></Sidebar>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <!-- Header -->
      <Header title="Create Product" :showSearch="false"></Header>

      <!-- Create Product Form -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form @submit.prevent="submitForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Form Content -->
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-medium mb-4">Product Information</h2>

              <!-- Product Name -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Product Name</label
                >
                <input
                  v-model="form.name"
                  type="text"
                  @focus="clearRelatedErrors"
                  :class="[
                    'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                    { 'border-red-500': form.errors.name }
                  ]"
                />
                <small v-show="form.errors.name" class="text-red-700">{{ form.errors.name }}</small>
              </div>

              <!-- Slug and SKU -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                  <input
                    v-model="form.slug"
                    type="text"
                    disabled
                    :class="[
                      'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                      { 'border-red-500': form.errors.slug }
                    ]"
                  />
                  <small v-show="form.errors.slug" class="text-red-700">{{ form.errors.slug }}</small>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                  <input
                    v-model="form.sku"
                    disabled
                    type="text"
                    :class="[
                      'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                      { 'border-red-500': form.errors.sku }
                    ]"
                  />
                  <small v-show="form.errors.sku" class="text-red-700">{{ form.errors.sku }}</small>
                </div>
              </div>

              <!-- Description -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Description</label
                >
                <textarea
                  v-model="form.description"
                  rows="4"
                  @focus="form.clearErrors('description')"
                  :class="[
                    'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                    { 'border-red-500': form.errors.description }
                  ]"
                ></textarea>
                <small v-show="form.errors.description" class="text-red-700">{{ form.errors.description }}</small>
              </div>
              <!-- Image Upload -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Images</label>
                <div
                  class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md"
                  :class="[
                    'border-gray-300',
                    { 'border-red-500': form.errors.image }
                  ]"
                  @dragover.prevent
                  @drop.prevent="handleDrop"
                  @click="form.clearErrors('image')"
                >
                  <div class="space-y-1 text-center">
                    <!-- Image Previews -->
                    <div v-if="imagePreviews.length" class="mb-4 flex flex-wrap gap-4 justify-center">
                      <div v-for="(preview, index) in imagePreviews" :key="index" class="relative group">
                        <img :src="preview" alt="Preview" class="h-32 w-auto rounded-md shadow-md" />
                        <button
                          @click="removeImage(index)"
                          type="button"
                          class="absolute top-1 right-1 text-red-600 w-6 h-6 rounded-full flex items-center justify-center shadow-lg"
                        >
                          ✕
                        </button>
                      </div>
                    </div>

                    <div v-else class="flex text-sm text-gray-600">
                      <label
                        for="file-upload"
                        class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500"
                      >
                        <span>Upload files</span>
                        <input
                          id="file-upload"
                          type="file"
                          multiple
                          accept="image/*"
                          class="sr-only"
                          @change="handleFileUpload"
                        />

                      </label>
                      <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each</p>
                  </div>
                </div>
                <small v-show="form.errors.image" class="text-red-700">{{ form.errors.image }}</small>
              </div>
            </div>

            <!-- Specifications -->
            <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium mb-4">Specifications</h2>
            <div class="space-y-4">
                <div v-for="(spec, index) in currentSpecifications" :key="spec.id">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ spec.name }}
                </label>
                <input
                    v-model="form.specifications[index].value"
                    type="text"
                    :placeholder="`Enter ${spec.name}`"
                    @focus="form.clearErrors(`specifications.${index}.value`)"
                    :class="[
                    'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                    { 'border-red-500': form.errors[`specifications.${index}.value`] }
                    ]"
                />
                <input
                    type="hidden"
                    value="spec.id"
                    v-model="form.specifications[index].id"
                />
                <small v-show="form.errors[`specifications.${index}.value`]" class="text-red-700">
                    {{ form.errors[`specifications.${index}.value`] }}
                </small>
                </div>
            </div>
            </div>
          </div>

          <!-- Side Panel -->
          <div class="space-y-6">
            <!-- Price Section -->
            <div v-if="!hasVariants" class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-medium mb-4">Price</h2>
              <!-- Regular Price (shown only when no variants) -->
              <div v-if="!hasVariants">
                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                <input
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>
            </div>

            <!-- Stock Section -->
            <div class="bg-white rounded-xl shadow-sm p-6">
              <h2 class="text-lg font-medium mb-4">Stock</h2>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                <input
                  v-model="form.stock"
                  type="number"
                  placeholder="Enter Stock"
                  @focus="form.clearErrors('stock')"
                  :class="[
                    'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                    { 'border-red-500': form.errors.stock }
                  ]"
                />
                <small v-show="form.errors.stock" class="text-red-700">{{ form.errors.stock }}</small>
              </div>
            </div>

            <!-- Sizes and Kinds Section -->
            <div  class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-medium mb-4">Sizes and Kinds</h2>

              <!-- Sizes -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Sizes</label>
                <div class="flex gap-2 mb-2">
                  <input
                    v-model="newSize"
                    type="text"
                    placeholder="Enter size"
                    class="flex-1 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <button
                    type="button"
                    @click="addSize"
                    class="px-4 py-2 bg-navy-600 text-white rounded-md hover:bg-navy-700"
                  >
                    Add
                  </button>
                </div>
                <!-- Variants List -->
                <div class="space-y-2 mt-4">
                  <div v-for="(variant, index) in form.variants" :key="index" class="border rounded-md p-3">
                    <div class="flex items-center justify-between cursor-pointer" @click="toggleVariant(index)">
                      <div class="flex items-center gap-2">
                        <span class="font-medium">{{ variant.size }}</span>
                        <button
                          type="button"
                          @click.stop="removeSize(index)"
                          class="text-red-600 hover:text-red-700"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                          </svg>
                        </button>
                      </div>
                      <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        class="h-5 w-5 transform transition-transform" 
                        :class="{ 'rotate-180': isVariantExpanded(index) }"
                        viewBox="0 0 20 20" 
                        fill="currentColor"
                      >
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </div>
                    <!-- Expandable Content -->
                    <div v-show="isVariantExpanded(index)" class="mt-3 space-y-3">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kind (optional)</label>
                        <input
                          v-model="variant.kind"
                          type="text"
                          placeholder="Enter kind"
                          class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <input
                          v-model="variant.price"
                          type="number"
                          step="0.01"
                          min="0"
                          placeholder="Enter price"
                          :class="[
                            'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                            { 'border-red-500': form.errors[`variants.${index}.price`] }
                          ]"
                        />
                        <small v-if="form.errors[`variants.${index}.price`]" class="text-red-700">
                          {{ form.errors[`variants.${index}.price`] }}
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Kinds -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kinds/Variants</label>
                <div class="flex gap-2 mb-2">
                  <input
                    v-model="newKind"
                    type="text"
                    placeholder="Enter kind"
                    class="flex-1 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @keyup.enter="addKind"
                  />
                  <button
                    type="button"
                    @click="addKind"
                    class="px-4 py-2 bg-navy-600 text-white rounded-md hover:bg-navy-700"
                  >
                    Add
                  </button>
                </div>
                <!-- Kind Tags -->
                <div class="flex flex-wrap gap-2">
                  <div
                    v-for="(kind, index) in form.kinds"
                    :key="index"
                    class="bg-gray-100 px-3 py-1 rounded-full flex items-center gap-2"
                  >
                    <span>{{ kind }}</span>
                    <button
                      type="button"
                      @click="removeKind(index)"
                      class="text-gray-500 hover:text-red-500"
                    >
                      ×
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Sizes and Kinds Section -->

            <!-- Associations -->
            <div class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-medium mb-4">Associations</h2>
              <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select
                        v-model="form.category_id"
                        @change="handleCategoryChange"
                        @focus="form.clearErrors('category_id')"
                        :class="[
                          'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                          { 'border-red-500': form.errors.category_id }
                        ]"
                    >
                        <option value="">Select Category</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                        </option>
                    </select>
                    <small v-show="form.errors.category_id" class="text-red-700">{{ form.errors.category_id }}</small>
                    </div>

                    <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                    <select
                        v-model="form.brand_id"
                        @focus="form.clearErrors('brand_id')"
                        :class="[
                          'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                          { 'border-red-500': form.errors.brand_id }
                        ]"
                    >
                        <option value="">Select Brand</option>
                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                        {{ brand.name }}
                        </option>
                    </select>
                    <small v-show="form.errors.brand_id" class="text-red-700">{{ form.errors.brand_id }}</small>
                    </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4">
              <Link
                :href="route('products.index')"
                class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 bg-navy-600 text-white rounded-md hover:bg-navy-700 disabled:opacity-50"
                >
                <span v-if="form.processing">Creating...</span>
                <span v-else>Create</span>
                </button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link, useForm, } from "@inertiajs/vue3";
import Sidebar from "../../../Components/Sidebar.vue";
import Header from "../../../Components/Header.vue";
// State

const props = defineProps({
  categories: Array,
  brands: Array,
});

const form = useForm({
  name: "",
  slug: "",
  sku: "",
  price: "",
  description: "",
  images: [],
  stock: "",
  category_id: "",
  brand_id: "",
  specifications: [],
  variants: [], // This will store size/kind combinations with prices
});

const currentSpecifications = ref([]);
const variantPrices = ref({}); // To store prices for each variant

// Watch for changes to the selected category
watch(
  () => form.category_id, // Watch for changes to the selected category
  () => {
    handleCategoryChange(); // Populate specifications dynamically
  }
);

watch(
  () => form.name,
  (newName) => {
    form.slug = newName.toLowerCase().trim().replace(/[\s\/]+/g, "-");
  }
);

const handleCategoryChange = () => {
  const selectedCategory = props.categories.find(
    (category) => category.id === form.category_id
  );
  currentSpecifications.value = selectedCategory
    ? selectedCategory.specifications
    : [];
  form.specifications = currentSpecifications.value.map((spec) => ({
    id: spec.id,
    value: "", // Initialize the value as empty
  }));
};

// Methods
const imagePreviews = ref([]); // Array to store image previews

// Handle file upload
// Handle file upload
const handleFileUpload = (event) => {
  const files = Array.from(event.target.files); // Convert FileList to Array
  addFiles(files);
};

// Handle drag-and-drop
const handleDrop = (event) => {
  event.preventDefault(); // Prevent default behavior
  const files = Array.from(event.dataTransfer.files);
  addFiles(files);
};

// Add files to the form
const addFiles = (files) => {
  files.forEach((file) => {
    if (!file.type.startsWith("image/")) {
      alert("Only image files are allowed.");
      return;
    }

    if (file.size > 10 * 1024 * 1024) {
      alert("File size must not exceed 10MB.");
      return;
    }

    form.images.push(file); // Add the file to form.images
    createImagePreview(file); // Create a preview for the image
  });
};

// Create image previews
const createImagePreview = (file) => {
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreviews.value.push(e.target.result); // Store the base64 preview
  };
  reader.readAsDataURL(file);
};

// Remove an image
const removeImage = (index) => {
  form.images.splice(index, 1); // Remove file from form data
  imagePreviews.value.splice(index, 1); // Remove preview
};

const submitForm = () => {
  // Ensure sizes and kinds are properly formatted before submission
  form.post(route("products.store"), {
    onSuccess: () => {
      form.reset();
      currentSpecifications.value = [];
      newSize.value = "";
      newKind.value = "";
    },
    onError: (errors) => {
      console.error(errors);
    },
  });
};

// Add this function in the script setup section
const clearRelatedErrors = () => {
  form.clearErrors('name');
  form.clearErrors('slug');
  form.clearErrors('sku');
};

// Add after the existing refs
const newSize = ref("");
const newKind = ref("");
const expandedVariants = ref([]);

const toggleVariant = (index) => {
  if (expandedVariants.value.includes(index)) {
    expandedVariants.value = expandedVariants.value.filter(i => i !== index);
  } else {
    expandedVariants.value.push(index);
  }
};

const isVariantExpanded = (index) => {
  return expandedVariants.value.includes(index);
};

// Update these methods for proper handling
const addSize = () => {
  if (newSize.value.trim()) {
    // Make sure we're not adding duplicates
    if (!form.variants.some(v => v.size === newSize.value.trim())) {
      form.variants.push({
        size: newSize.value.trim(),
        kind: newKind.value || null,
        price: ''
      });
    }
    newSize.value = "";
  }
};

const removeSize = (index) => {
  form.variants.splice(index, 1);
};

const addKind = () => {
  if (newKind.value.trim()) {
    // Make sure we're not adding duplicates
    if (!form.variants.some(v => v.kind === newKind.value.trim())) {
      form.variants.push({
        size: null,
        kind: newKind.value.trim(),
        price: ''
      });
    }
    newKind.value = "";
  }
};

const removeKind = (index) => {
  form.variants.splice(index, 1);
};

const updateVariantPrices = () => {
  const newPrices = {};
  form.variants.forEach(variant => {
    const key = `${variant.size}${variant.kind ? `-${variant.kind}` : ''}`;
    newPrices[key] = variant.price;
  });
  variantPrices.value = newPrices;
};

const addVariant = () => {
  if (newSize.value) {
    const variant = {
      size: newSize.value,
      kind: newKind.value || null,
      price: ''
    };
    
    // Check if variant already exists
    const exists = form.variants.some(v => 
      v.size === variant.size && v.kind === variant.kind
    );
    
    if (!exists) {
      form.variants.push(variant);
      newSize.value = '';
      newKind.value = '';
    }
  }
};

const removeVariant = (index) => {
  form.variants.splice(index, 1);
};

const hasVariants = computed(() => {
  return form.variants && form.variants.length > 0;
});

// Watch for changes in variants to handle price field
watch(() => form.variants, (newVariants) => {
  if (newVariants && newVariants.length > 0) {
    form.price = '0'; // Clear price when variants are added
  }
}, { deep: true });
</script>

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
</style>

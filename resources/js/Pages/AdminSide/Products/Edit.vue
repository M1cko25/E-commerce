<template>
  <Head title=" | Edit Product"></Head>
  <div class="min-h-screen bg-gray-50">
    <!-- Mobile Sidebar Toggle -->
    <Sidebar></Sidebar>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
      <!-- Header -->
      <Header title="Edit Product" :showSearch="false"></Header>

      <!-- Edit Product Form -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form @submit.prevent="updateForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
                    { 'border-red-500': form.errors.name },
                  ]"
                />
                <small v-show="form.errors.name" class="text-red-700">{{
                  form.errors.name
                }}</small>
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
                      { 'border-red-500': form.errors.slug },
                    ]"
                  />
                  <small v-show="form.errors.slug" class="text-red-700">{{
                    form.errors.slug
                  }}</small>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                  <input
                    v-model="form.sku"
                    disabled
                    type="text"
                    :class="[
                      'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                      { 'border-red-500': form.errors.sku },
                    ]"
                  />
                  <small v-show="form.errors.sku" class="text-red-700">{{
                    form.errors.sku
                  }}</small>
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
                    { 'border-red-500': form.errors.description },
                  ]"
                ></textarea>
                <small v-show="form.errors.description" class="text-red-700">{{
                  form.errors.description
                }}</small>
              </div>

              <!-- Image Upload -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Images</label>
                <div
                  class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md"
                  :class="['border-gray-300', { 'border-red-500': form.errors.image }]"
                  @dragover.prevent
                  @drop.prevent="handleDrop"
                  @click="form.clearErrors('image')"
                >
                  <div class="space-y-1 text-center">
                    <!-- Image Previews -->
                    <div
                      v-if="imagePreviews.length"
                      class="mb-4 flex flex-wrap gap-4 justify-center"
                    >
                      <div
                        v-for="(preview, index) in imagePreviews"
                        :key="index"
                        class="relative group"
                      >
                        <img
                          :src="preview"
                          alt="Preview"
                          class="h-32 w-auto rounded-md shadow-md"
                        />
                        <button
                          @click.stop="removeImage(index)"
                          type="button"
                          class="absolute top-1 right-1 bg-white text-red-600 w-6 h-6 rounded-full flex items-center justify-center shadow-lg"
                        >
                          ✕
                        </button>
                      </div>
                    </div>

                    <!-- Upload controls - always visible -->
                    <div class="flex text-sm text-gray-600 justify-center">
                      <label
                        for="file-upload"
                        class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500"
                      >
                        <span>{{ imagePreviews.length ? 'Add more files' : 'Upload files' }}</span>
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
                <small v-show="form.errors.image" class="text-red-700">{{
                  form.errors.image
                }}</small>
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
                      { 'border-red-500': form.errors[`specifications.${index}.value`] },
                    ]"
                  />
                  <input
                    type="hidden"
                    value="spec.id"
                    v-model="form.specifications[index].id"
                  />
                  <small
                    v-show="form.errors[`specifications.${index}.value`]"
                    class="text-red-700"
                  >
                    {{ form.errors[`specifications.${index}.value`] }}
                  </small>
                </div>
              </div>
            </div>
          </div>

          <!-- Side Panel -->
          <div class="space-y-6">
            <!-- Price Section (show only when no variants) -->
            <div v-if="!hasVariants" class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-medium mb-4">Price</h2>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                <input
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  :class="{ 'border-red-500': form.errors.price }"
                />
                <small v-if="form.errors.price" class="text-red-700">{{ form.errors.price }}</small>
              </div>
            </div>

            <!-- Stock Section -->
            <div class="bg-white rounded-lg shadow p-6">
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
                    { 'border-red-500': form.errors.stock },
                  ]"
                />
                <small v-show="form.errors.stock" class="text-red-700">{{
                  form.errors.stock
                }}</small>
              </div>
            </div>

            <!-- Associations -->
            <div class="bg-white rounded-lg shadow p-6">
              <h2 class="text-lg font-medium mb-4">Associations</h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Category</label
                  >
                  <select
                    v-model="form.category_id"
                    @change="handleCategoryChange"
                    @focus="form.clearErrors('category_id')"
                    :class="[
                      'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                      { 'border-red-500': form.errors.category_id },
                    ]"
                  >
                    <option value="">Select Category</option>
                    <option
                      v-for="category in categories"
                      :key="category.id"
                      :value="category.id"
                    >
                      {{ category.name }}
                    </option>
                  </select>
                  <small v-show="form.errors.category_id" class="text-red-700">{{
                    form.errors.category_id
                  }}</small>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Brand</label
                  >
                  <select
                    v-model="form.brand_id"
                    @focus="form.clearErrors('brand_id')"
                    :class="[
                      'w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                      { 'border-red-500': form.errors.brand_id },
                    ]"
                  >
                    <option value="">Select Brand</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                      {{ brand.name }}
                    </option>
                  </select>
                  <small v-show="form.errors.brand_id" class="text-red-700">{{
                    form.errors.brand_id
                  }}</small>
                </div>
              </div>
            </div>

            <!-- Sizes and Kinds Section -->
            <div class="bg-white rounded-lg shadow p-6">
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
                <span v-if="form.processing">Updating...</span>
                <span v-else>Update</span>
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
import { Link, useForm, router } from "@inertiajs/vue3";
import Sidebar from "../../../Components/Sidebar.vue";
import Header from "../../../Components/Header.vue";
import axios from "axios";

const props = defineProps({
  product: {
    type: Object,
    required: true,
  },
  categories: Array,
  brands: Array,
});

// Initialize form with existing product data
const form = useForm({
  name: props.product.name,
  slug: props.product.slug,
  sku: props.product.sku,
  description: props.product.description,
  price: props.product.variants.length > 0 ? Number(props.product.price) : 0,
  stock: props.product.stock,
  category_id: props.product.category_id,
  brand_id: props.product.brand_id,
  specifications: props.product.specifications,
  variants: props.product.variants ? props.product.variants.map(v => ({
    size: v.sizes,
    kind: v.kinds,
    price: Number(v.price)
  })) : [],
  remove_images: [],
  product_images: [...(props.product.product_images || [])],
  images: []
});

const expandedVariants = ref([]);
const hasVariants = computed(() => {
  return form.variants && form.variants.length > 0;
});

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

// Watch for changes in variants to handle price field
watch(() => form.variants, (newVariants) => {
  if (newVariants && newVariants.length > 0) {
    form.price = 0;
  }
}, { deep: true });

const currentSpecifications = ref([]);
const imagePreviews = ref([]);
const newSize = ref('');
const newKind = ref('');

// Load existing images into previews
if (props.product.product_images && props.product.product_images.length > 0) {
  imagePreviews.value = props.product.product_images.map((image) => {
    return `/storage/${image}`;
  });
}

// Watch for changes to the selected category
watch(
  () => form.category_id,
  () => {
    handleCategoryChange();
  }
);

watch(
  () => form.name,
  (newName) => {
    form.slug = newName.toLowerCase().trim().replace(/\s+/g, "-");
  }
);

const handleCategoryChange = () => {
  const selectedCategory = props.categories.find(
    (category) => category.id === form.category_id
  );
  currentSpecifications.value = selectedCategory ? selectedCategory.specifications : [];

  form.specifications = currentSpecifications.value.map((spec) => {
    const existingSpec = props.product.specifications.find((s) => s.id === spec.id);
    return {
      id: spec.id,
      value: existingSpec ? existingSpec.value : "",
    };
  });
};

// Initialize specifications based on initial category
handleCategoryChange();

// Methods
const handleFileUpload = (event) => {
  const files = Array.from(event.target.files);
  addFiles(files);
};

const handleDrop = (event) => {
  event.preventDefault();
  const files = Array.from(event.dataTransfer.files);
  addFiles(files);
};

const addFiles = (files) => {
  // Make sure form.images is initialized
  if (!form.images) {
    form.images = [];
  }

  files.forEach((file) => {
    if (!file.type.startsWith("image/")) {
      alert("Only image files are allowed.");
      return;
    }

    if (file.size > 10 * 1024 * 1024) {
      alert("File size must not exceed 10MB.");
      return;
    }

    form.images.push(file);
    createImagePreview(file);
  });
};

const createImagePreview = (file) => {
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreviews.value.push(e.target.result);
  };
  reader.readAsDataURL(file);
};

const removeImage = (index) => {
  // Check if index is valid
  if (index < 0 || index >= imagePreviews.value.length) {
    console.error('Invalid image index:', index);
    return;
  }

  // Get the actual image path from the form's product_images array
  if (index < form.product_images.length) {
    // It's an existing image - get the path and add to remove_images
    const imagePath = form.product_images[index];

    if (imagePath) {
      if (!form.remove_images) {
        form.remove_images = [];
      }
      form.remove_images.push(imagePath);

      // Remove from form's product_images array
      form.product_images.splice(index, 1);

      // Also remove from props.product.product_images to keep in sync
      if (index < props.product.product_images.length) {
        props.product.product_images.splice(index, 1);
      }
    }
  } else {
    // It's a newly added image - just remove from form.images
    const newIndex = index - form.product_images.length;
    if (form.images && form.images[newIndex]) {
      form.images.splice(newIndex, 1);
    }
  }

  // Always remove from previews
  imagePreviews.value.splice(index, 1);
};

const clearRelatedErrors = () => {
  form.clearErrors("name");
  form.clearErrors("slug");
  form.clearErrors("sku");
};

const updateForm = () => {
  // Set processing state to true
  form.processing = true;

  // If there are new images, we need to use FormData
  if (form.images && form.images.length > 0) {
    // Create a new FormData object
    const formData = new FormData();

    // Add regular form fields
    formData.append('name', form.name);
    formData.append('slug', form.slug);
    formData.append('sku', form.sku);
    formData.append('description', form.description);
    formData.append('price', form.price);
    formData.append('stock', form.stock);
    formData.append('category_id', form.category_id);
    formData.append('brand_id', form.brand_id);
    formData.append('_method', 'PUT'); // Need this for method spoofing

    // Add arrays as JSON strings
    formData.append('specifications', JSON.stringify(form.specifications));
    formData.append('variants', JSON.stringify(form.variants));
    formData.append('product_images', JSON.stringify(form.product_images));
    formData.append('remove_images', JSON.stringify(form.remove_images));

    // Add image files
    form.images.forEach((file, index) => {
      formData.append(`images[${index}]`, file);
    });

    // Use Inertia's post method but make sure it knows to treat this as a PUT request
    axios.post(route("products.update", props.product.id), formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }).then(response => {
      // Reset processing state
      form.processing = false;
      // Reset the images array after successful upload
      form.images = [];
      // Redirect to the products index page with success message
      router.visit(route('products.index') + '?success=Product updated successfully');
    }).catch(error => {
      // Reset processing state
      form.processing = false;
      console.error('Form submission errors:', error);
      // Re-populate any validation errors
      if (error.response && error.response.data && error.response.data.errors) {
        form.setErrors(error.response.data.errors);
      }
    });
  } else {
    // No new images, use regular form submission
    form.put(route("products.update", props.product.id), {
      preserveScroll: true,
      onSuccess: () => {
        form.images = [];
        // If there were any removed images, refresh the page to show the updated images
        if (form.remove_images && form.remove_images.length > 0) {
          router.visit(route('products.edit', props.product.id));
        }
      },
    });
  }
};

// Methods for managing sizes and kinds
const addSize = () => {
  if (newSize.value.trim()) {
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

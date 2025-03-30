<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { Link, router } from "@inertiajs/vue3";
import {
  ShoppingCartIcon,
  StarIcon,
  HeartIcon,
  PackageIcon,
  TruckIcon,
  HeadphonesIcon,
  ShieldIcon,
  ChevronLeft,
  ChevronRight,
  MessageCircle,
} from "lucide-vue-next";
import NavLink from "../../Components/NavLink.vue";
import { route } from "../../../../vendor/tightenco/ziggy/src/js";
import Footer from "../../Components/Footer.vue";
import AddToCartModal from "../../Components/AddToCartModal.vue";
import PowerDrill from "@/images/powerDrill.png";
import RoofSheets from "@/images/roofSheets.png";
import HydraulicHinges from "@/images/hydraulicHinges.png";
import { usePage } from "@inertiajs/vue3";
import { useAI } from "@/Api/ai";
import WishlistButton from "../../Components/WishlistButton.vue";
import ProductRating from "../../Components/ProductRating.vue";
import BuyNowButton from "../../Components/BuyNowButton.vue";
const page = usePage();
const landingContents = page.props.landingContents;

const props = defineProps({
  exploreProducts: Object,
  latestProducts: Object,
  products: Object,
  categories: Object,
});

const currentSlide = ref(0);
const visibleCards = ref(5);

// State for Categories Section
const currentCategorySlide = ref(0);
const visibleCategoryCards = ref(5);

// Update visible cards based on screen size
const updateVisibleCards = () => {
  if (window.innerWidth < 640) {
    visibleCards.value = 1;
    visibleCategoryCards.value = 1;
  } else if (window.innerWidth < 768) {
    visibleCards.value = 2;
    visibleCategoryCards.value = 2;
  } else if (window.innerWidth < 1024) {
    visibleCards.value = 3;
    visibleCategoryCards.value = 3;
  } else if (window.innerWidth < 1280) {
    visibleCards.value = 4;
    visibleCategoryCards.value = 4;
  } else {
    visibleCards.value = 5;
    visibleCategoryCards.value = 5;
  }
};

// Scroll functionality for Latest Products Section
const prevSlide = () => {
  currentSlide.value = Math.max(currentSlide.value - 1, 0);
};
const nextSlide = () => {
  currentSlide.value = Math.min(
    currentSlide.value + 1,
    props.latestProducts.length - visibleCards.value
  );
};

// Scroll functionality for Categories Section
const prevCategorySlide = () => {
  currentCategorySlide.value = Math.max(currentCategorySlide.value - 1, 0);
};
const nextCategorySlide = () => {
  currentCategorySlide.value = Math.min(
    currentCategorySlide.value + 1,
    props.categories.length - visibleCategoryCards.value
  );
};

// Add event listener for window resize
onMounted(() => {
  updateVisibleCards();
  window.addEventListener("resize", updateVisibleCards);
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", updateVisibleCards);
});
// Sample categories (replace with your dynamic data)

// Reviews Data
const reviews = [
  {
    id: 1,
    name: "Martin Goutry",
    title: "Back-end developer at MyOnline",
    content:
      "Dico is finally addressing a long-time problem we had when building UIs. It's ease of use and workflow seems really intuitive. Promising!",
    date: "Dico user, 2023.03.02",
    avatar: "",
  },
  {
    id: 2,
    name: "Theo Champion",
    title: "Back-end developer at MyOnline",
    content:
      "Dico is finally addressing a long-time problem we had when building UIs. It's ease of use and workflow seems really intuitive. Promising!",
    date: "Dico user, 2023.03.02",
    avatar: "",
  },
  {
    id: 3,
    name: "Agnes Remi",
    title: "Back-end developer at MyOnline",
    content:
      "Dico is finally addressing a long-time problem we had when building UIs. It's ease of use and workflow seems really intuitive. Promising!",
    date: "Dico user, 2023.03.02",
    avatar: "",
  },
];

const showSuccessModal = ref(false);
const addedProduct = ref(null);

const addToCart = (product) => {
  router.post(
    route("cart.add"),
    {
      product_id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1,
      image: product.image ? "/storage/" + product.image : "/storage/default.jpg",
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        addedProduct.value = {
          name: product.name,
          price: product.price,
          quantity: 1,
          image: product.image ? "/storage/" + product.image : "/storage/default.jpg",
        };
        showSuccessModal.value = true;
      },
    }
  );
};

const showChatWindow = ref(false);
const chatMessages = ref([]);
const userInput = ref('');
const { sendPrompt, isLoading, error, clearConversation, fetchChatHistory, loadConversation, deleteConversation, chatHistory, loadingHistory, conversationHistory, currentChatId } = useAI();
const chatWindowSize = ref({ width: 320, height: 450 });
const isDragging = ref(false);
const startPosition = ref({ x: 0, y: 0 });
const chatContainerRef = ref(null);
const showHistory = ref(false);

const toggleChatWindow = () => {
  showChatWindow.value = !showChatWindow.value;

  if (showChatWindow.value && chatMessages.value.length === 0) {
    // Add initial greeting only if it's a fresh conversation
    chatMessages.value.push({
      type: 'bot',
      content: 'Hello! I am Chatter, your AI assistant for DRM Roofing, Glass, and Iron Works. I can help you with:<br><br>• Finding products in our catalog<br>• Providing product information and prices<br>• Directing you to product pages<br>• Answering questions about our services<br><br>Try asking me something like "Show me roofing products" or "Find glass doors"!'
    });
  }
};

const openNewChat = () => {
  showHistory.value = false;
  chatMessages.value = [{
    type: 'bot',
    content: 'Hello! I am Chatter, your AI assistant for DRM Roofing, Glass, and Iron Works. I can help you with:<br><br>• Finding products in our catalog<br>• Providing product information and prices<br>• Directing you to product pages<br>• Answering questions about our services<br><br>Try asking me something like "Show me roofing products" or "Find glass doors"!'
  }];
  clearConversation();
};

const toggleHistory = async () => {
  showHistory.value = !showHistory.value;
  if (showHistory.value) {
    await fetchChatHistory();
  }
};

const loadHistoryConversation = async (promptId) => {
  const success = await loadConversation(promptId);
  if (success) {
    showHistory.value = false;
    // Convert the conversation history to chat messages format
    chatMessages.value = conversationHistory.value.map(msg => ({
      type: msg.role === 'user' ? 'user' : 'bot',
      content: msg.content
    }));
  }
};

const sendMessage = async () => {
  if (!userInput.value.trim()) return;

  const userMessage = userInput.value;
  chatMessages.value.push({
    type: 'user',
    content: userMessage
  });

  userInput.value = '';

  try {
    const response = await sendPrompt(userMessage, currentChatId.value);
    if (response) {
      // The response is already formatted with HTML by the server
      // Just ensure any line breaks that aren't in HTML tags are converted properly
      chatMessages.value.push({
        type: 'bot',
        content: response
      });

      // Scroll to the bottom of the chat
      setTimeout(() => {
        const chatContainer = document.querySelector('.overflow-y-auto');
        if (chatContainer) {
          chatContainer.scrollTop = chatContainer.scrollHeight;
        }
      }, 100);
    }
  } catch (error) {
    chatMessages.value.push({
      type: 'bot',
      content: 'Sorry, I encountered an error. Please try again.'
    });
  }
};

const startResize = (e) => {
  e.preventDefault();
  isDragging.value = true;
  startPosition.value = {
    x: e.clientX,
    y: e.clientY,
    width: chatWindowSize.value.width,
    height: chatWindowSize.value.height
  };
  document.addEventListener('mousemove', handleResize);
  document.addEventListener('mouseup', stopResize);
};

const handleResize = (e) => {
  if (!isDragging.value) return;

  const deltaX = e.clientX - startPosition.value.x;
  const deltaY = e.clientY - startPosition.value.y;

  chatWindowSize.value = {
    width: Math.max(300, startPosition.value.width - deltaX),
    height: Math.max(400, startPosition.value.height - deltaY)
  };
};

const stopResize = () => {
  isDragging.value = false;
  document.removeEventListener('mousemove', handleResize);
  document.removeEventListener('mouseup', stopResize);
};

// Add this to prevent text selection while resizing
onMounted(() => {
  updateVisibleCards();
  window.addEventListener("resize", updateVisibleCards);

  // Add event listener for preventing text selection during resize
  if (chatContainerRef.value) {
    chatContainerRef.value.addEventListener('selectstart', (e) => {
      if (isDragging.value) e.preventDefault();
    });
  }
});

onBeforeUnmount(() => {
  window.removeEventListener("resize", updateVisibleCards);
  document.removeEventListener('mousemove', handleResize);
  document.removeEventListener('mouseup', stopResize);
});

// Add this new function to handle conversation deletion
const deleteHistoryConversation = async (event, promptId) => {
  // Stop the click event from propagating to the parent (which would load the conversation)
  event.stopPropagation();

  if (await deleteConversation(promptId)) {
    // If the current history is empty after deletion, close the history panel
    if (Object.keys(chatHistory.value).length === 0) {
      showHistory.value = false;
    }
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-200">
    <!-- Header/Navigation -->
    <NavLink />
    <!-- Hero Section -->
    <section class="py-16 bg-gray-50">
        <img
              v-if="landingContents.length > 0"
              :src="landingContents[0].image ? '/storage/' + landingContents[0].image : ''"
              class="absolute top-0 z-1 left-0 w-full h-full object-cover"
            >
        <div class="container rounded-lg z-10 bg-gray-50 mt-1 mx-auto px-4 py-8">
            <div class="grid md:grid-cols-2 gap-8 items-center">
          <!-- Text Section -->
          <div class="z-10">
            <h1 class="text-4xl md:text-5xl z-10 font-bold text-navy-900 mb-4 leading-tight">
              {{ landingContents[0]?.hero || 'Reliable Roofing, Elegant Glass, and Durable Ironworks – Crafted for You!' }}
            </h1>
            <p class="text-gray-600 mb-6 leading-relaxed">
              {{ landingContents[0]?.description || 'High-quality materials, expert craftsmanship, and hassle-free ordering. Get a free quote today!' }}
            </p>
          </div>

          <!-- Images Section -->
          <div class="grid z-10 md:grid-cols-2 gap-3">
            <div
              class="bg-white rounded-lg p-4 shadow-lg flex justify-center items-center"
            >
              <img
                :src="PowerDrill"
                alt="Power Drill"
                class="w-full max-w-[250px] object-contain"
              />
            </div>

            <!-- Right Content Section -->
            <div class="space-y-2">
              <!-- Summer Sales -->
              <div class="bg-white rounded-lg p-4 shadow-lg relative">
                <div
                  class="absolute z-10 top-2 left-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded"
                >
                  SUMMER SALES
                </div>
                <div class="w-full z-10 h-36 overflow-hidden">
                  <img
                  :src="RoofSheets"
                  alt="Roofing Sheets"
                  class="relative -top-10 "
                />
                </div>
                <h3 class="font-semibold mt-2">Metal Barrel Roof Tiles</h3>
                <div class="text-lg font-bold text-green-600">29% OFF</div>
              </div>

              <div class="bg-white z-10 rounded-lg p-4 shadow-lg">
                <img
                  :src="HydraulicHinges"
                  alt="Hydraulic Hinges"
                  class="w-full max-w-[150px] mx-auto mb-2"
                />
                <h3 class="font-semibold mb-2 text-center">Hydraulic Hinges</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-12 bg-white">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div class="flex items-center space-x-4">
            <div class="p-3 bg-gray-100 rounded-lg">
              <PackageIcon class="h-6 w-6 text-navy-600" />
            </div>
            <div>
              <h3 class="font-semibold">Discount</h3>
              <p class="text-sm text-gray-600">Every month new sales</p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <div class="p-3 main rounded-lg">
              <TruckIcon class="h-6 w-6 text-navy-600" />
            </div>
            <div>
              <h3 class="font-semibold">Free Delivery</h3>
              <p class="text-sm text-gray-600">100% Free for all orders</p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <div class="p-3 main rounded-lg">
              <HeadphonesIcon class="h-6 w-6 text-navy-600" />
            </div>
            <div>
              <h3 class="font-semibold">Great Support 24/7</h3>
              <p class="text-sm text-gray-600">We care your experiences</p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <div class="p-3 main rounded-lg">
              <ShieldIcon class="h-6 w-6 text-navy-600" />
            </div>
            <div>
              <h3 class="font-semibold">Secure Payment</h3>
              <p class="text-sm text-gray-600">100% Secure Payment Method</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Latest Products Section -->
    <section class="py-8 sm:py-12 bg-gray-50">
      <div class="container mx-auto px-4">
        <!-- Section Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 sm:mb-8">
          <div>
            <div class="flex items-center space-x-2 mb-2">
              <div class="w-4 h-8 bg-navy-600 rounded"></div>
              <span class="text-sm font-medium">This Month</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold">Latest Products</h2>
          </div>

          <Link
            :href="route('product.list')"
            class="mt-4 sm:mt-0 px-3 py-1.5 sm:px-4 sm:py-2 text-sm bg-navy-900 text-white rounded-lg hover:bg-navy-800"
          >
            View All Products
          </Link>
        </div>

        <div class="relative overflow-hidden">
          <!-- Carousel Wrapper -->
          <div
            class="flex transition-transform duration-300"
            :style="{ transform: `translateX(-${currentSlide * (100 / visibleCards)}%)` }"
            ref="productCarousel"
          >
            <Link
              v-for="latestProduct in latestProducts"
              :key="latestProduct.id"
              :href="route('product.view', { slug: latestProduct.slug })"
              class="flex-none px-1"
              :class="{
                'w-full': visibleCards === 1,
                'w-1/2': visibleCards === 2,
                'w-1/3': visibleCards === 3,
                'w-1/4': visibleCards === 4,
                'w-1/5': visibleCards === 5,
              }"
            >
              <div
                class="bg-white rounded-lg p-2 sm:p-3 hover:shadow-lg transition-all duration-300 border border-gray-200 hover:border-blue-500 h-full flex flex-col"
              >
                <!-- Brand Name -->
                <span
                  class="text-end text-xs sm:text-sm font-semibold text-black uppercase mb-1 sm:mb-2 block"
                >
                  {{ latestProduct.brand }}
                </span>

                <!-- Product Image -->
                <div
                  class="flex justify-center items-center mb-2 sm:mb-3 aspect-square"
                >
                  <img
                    :src="
                      latestProduct.image
                        ? '/storage/' + latestProduct.image
                        : 'storage/default.jpg'
                    "
                    :alt="latestProduct.name"
                    class="w-full h-24 xs:h-28 sm:h-32 md:h-36 lg:h-40 object-contain rounded-lg"
                  />
                </div>

                <!-- Product Name -->
                <h3
                  class="primary-text text-sm sm:text-base font-medium mb-1 sm:mb-2 truncate whitespace-nowrap overflow-hidden"
                >
                  {{ latestProduct.name }}
                </h3>

                <div class="flex items-center justify-between mt-auto">
                  <!-- Product Details -->
                  <div>
                    <div class="flex items-center mb-1">
                      <ProductRating
                        :model-value="latestProduct.rating || 0"
                        :count="latestProduct.stock"
                        read-only
                        size="sm"
                      />
                    </div>
                    <span class="primary-text font-bold text-sm sm:text-base lg:text-lg">
                      ₱{{ latestProduct.price }}
                    </span>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex space-x-1 sm:space-x-2 mt-1 sm:mt-2">
                    <WishlistButton
                      :product-id="latestProduct.id"
                      :initial-wishlist-state="latestProduct.in_wishlist"
                      size="small"
                      icon-class="h-4 w-4 sm:h-5 sm:w-5"
                      button-class="p-1 sm:p-1.5 primary-text main rounded-lg"
                    />
                    <button
                      @click.prevent="addToCart(latestProduct)"
                      class="p-1 sm:p-1.5 text-white bg-navy-900 rounded-lg"
                    >
                      <ShoppingCartIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                    </button>
                    <BuyNowButton
                      :product-id="latestProduct.id"
                      size="sm"
                      :product-price="latestProduct.price"
                      button-class="p-1 sm:p-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg"
                      icon-class="h-4 w-4 sm:h-5 sm:w-5"
                      :show-icon="true"
                      label=""
                    />
                  </div>
                </div>
              </div>
            </Link>
          </div>

          <!-- Navigation Buttons -->
          <button
            @click="prevSlide"
            class="absolute left-0 top-1/2 -translate-y-1/2 bg-navy-800 p-1 sm:p-2 rounded-full hover:bg-navy-700 transition shadow-md"
            :disabled="currentSlide === 0"
            :class="{ 'opacity-50 cursor-not-allowed': currentSlide === 0 }"
          >
            <ChevronLeft class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
          </button>
          <button
            @click="nextSlide"
            class="absolute right-0 top-1/2 -translate-y-1/2 bg-navy-800 p-1 sm:p-2 rounded-full hover:bg-navy-700 transition shadow-md"
            :disabled="currentSlide >= props.latestProducts.length - visibleCards"
            :class="{ 'opacity-50 cursor-not-allowed': currentSlide >= props.latestProducts.length - visibleCards }"
          >
            <ChevronRight class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
          </button>

          <!-- Pagination Indicators -->
          <div class="flex justify-center mt-6 space-x-2">
            <button
              v-for="(_, index) in Array.from({ length: Math.ceil(props.latestProducts.length / visibleCards) })"
              :key="index"
              @click="currentSlide = index"
              class="w-2.5 h-2.5 rounded-full transition-colors"
              :class="currentSlide === index ? 'bg-navy-600' : 'bg-gray-300 hover:bg-gray-400'"
              :aria-label="`Go to slide ${index + 1}`"
            ></button>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section class="py-8 sm:py-12 bg-navy-900 text-white">
      <div class="container mx-auto px-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 sm:mb-8">
          <h2 class="text-xl sm:text-2xl font-bold">Categories</h2>

          <Link
            :href="route('product.list')"
            class="mt-4 sm:mt-0 px-3 py-1.5 sm:px-4 sm:py-2 text-sm bg-navy-600 text-white rounded-lg hover:bg-navy-700"
          >
            All Categories
          </Link>
        </div>

        <div class="relative overflow-hidden">
          <!-- Carousel Wrapper -->
          <div
            class="flex transition-transform duration-300"
            :style="{
              transform: `translateX(-${
                currentCategorySlide * (100 / visibleCategoryCards)
              }%)`,
            }"
            ref="categoryCarousel"
          >
            <Link
              :href="route('category.products', { categorySlug: category.slug })"
              v-for="category in categories"
              :key="category.id"
              class="flex-none px-1"
              :class="{
                'w-full': visibleCategoryCards === 1,
                'w-1/2': visibleCategoryCards === 2,
                'w-1/3': visibleCategoryCards === 3,
                'w-1/4': visibleCategoryCards === 4,
                'w-1/5': visibleCategoryCards === 5,
              }"
            >
              <div class="bg-navy-800 rounded-lg overflow-hidden h-full shadow-md hover:shadow-lg transition-all hover:bg-navy-700">
                <div class="relative aspect-video">
                  <img
                    :src="
                      category.image ? '/storage/' + category.image : 'storage/default.jpg'
                    "
                    :alt="category.name"
                    class="w-full h-full object-contain p-2"
                  />
                </div>
                <div class="p-3 sm:p-4">
                  <h3 class="font-medium truncate whitespace-nowrap overflow-hidden">
                    {{ category.name }}
                  </h3>
                </div>
              </div>
            </Link>
          </div>

          <!-- Navigation Buttons -->
          <button
            @click="prevCategorySlide"
            class="absolute left-0 top-1/2 -translate-y-1/2 bg-navy-700 p-1 sm:p-2 rounded-full hover:bg-navy-600 transition shadow-md"
            :disabled="currentCategorySlide === 0"
            :class="{ 'opacity-50 cursor-not-allowed': currentCategorySlide === 0 }"
          >
            <ChevronLeft class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
          </button>
          <button
            @click="nextCategorySlide"
            class="absolute right-0 top-1/2 -translate-y-1/2 bg-navy-700 p-1 sm:p-2 rounded-full hover:bg-navy-600 transition shadow-md"
            :disabled="currentCategorySlide >= props.categories.length - visibleCategoryCards"
            :class="{ 'opacity-50 cursor-not-allowed': currentCategorySlide >= props.categories.length - visibleCategoryCards }"
          >
            <ChevronRight class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
          </button>

          <!-- Pagination Indicators -->
          <div class="flex justify-center mt-6 space-x-2">
            <button
              v-for="(_, index) in Array.from({ length: Math.ceil(props.categories.length / visibleCategoryCards) })"
              :key="index"
              @click="currentCategorySlide = index"
              class="w-2.5 h-2.5 rounded-full transition-colors"
              :class="currentCategorySlide === index ? 'bg-white' : 'bg-navy-700 hover:bg-navy-600'"
              :aria-label="`Go to category slide ${index + 1}`"
            ></button>
          </div>
        </div>
      </div>
    </section>

    <!-- Explore Products Section -->
    <section class="py-8 sm:py-12 bg-gray-50">
      <div class="container mx-auto px-4">
        <!-- Section Header -->
        <div class="flex items-center space-x-2 mb-2">
          <div class="w-4 h-8 bg-navy-600 rounded"></div>
          <span class="text-sm font-medium">Our Products</span>
        </div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 sm:mb-8">
          <h2 class="text-xl sm:text-2xl font-bold">Explore Our Products</h2>
          <Link
            :href="route('product.list')"
            class="mt-4 sm:mt-0 px-3 py-1.5 sm:px-4 sm:py-2 text-sm bg-navy-900 text-white rounded-lg hover:bg-navy-800"
          >
            Browse All Products
          </Link>
        </div>

        <!-- Responsive Products Grid -->
        <div
          class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6"
        >
          <Link
            :href="route('product.view', { slug: product.slug })"
            v-for="product in exploreProducts"
            :key="product.id"
            class="relative bg-white rounded-lg p-2 sm:p-3 hover:shadow-lg transition-all duration-300 border border-gray-200 block hover:border-blue-500 h-full flex flex-col"
          >
            <!-- Brand Name -->
            <span
              class="text-end text-xs sm:text-sm font-semibold text-black uppercase mb-1 sm:mb-2 block"
            >
              {{ product.brand.name }}
            </span>

            <!-- Product Image -->
            <div
              class="flex justify-center items-center mb-2 sm:mb-3 aspect-square"
            >
              <img
                :src="product.image ? '/storage/' + product.image : 'storage/default.jpg'"
                :alt="product.name"
                class="w-full h-24 xs:h-28 sm:h-32 md:h-36 lg:h-40 object-contain rounded-lg"
              />
            </div>

            <!-- Product Name -->
            <h3
              class="primary-text text-sm sm:text-base font-medium mb-1 sm:mb-2 truncate whitespace-nowrap overflow-hidden"
            >
              {{ product.name }}
            </h3>

            <div class="flex items-center justify-between mt-auto">
              <!-- Product Details -->
              <div>
                <div class="flex items-center mb-1">
                  <ProductRating
                    :model-value="product.rating || 0"
                    :count="product.stock"
                    read-only
                    size="sm"
                  />
                </div>
                <span class="primary-text font-bold text-sm sm:text-base lg:text-lg"
                  >₱{{ product.price }}</span
                >
              </div>

              <!-- Action Buttons -->
              <div class="flex space-x-1 sm:space-x-2 mt-1 sm:mt-2">
                <WishlistButton
                  :product-id="product.id"
                  :initial-wishlist-state="product.in_wishlist"
                  size="small"
                  icon-class="h-4 w-4 sm:h-5 sm:w-5"
                  button-class="p-1 sm:p-1.5 primary-text main rounded-lg"
                />
                <button
                  @click.prevent="addToCart(product)"
                  class="p-1 sm:p-1.5 button-primary rounded-lg"
                >
                  <ShoppingCartIcon class="h-4 w-4 sm:h-5 sm:w-5" />
                </button>
                <BuyNowButton
                  :product-id="product.id"
                  size="sm"
                  :product-price="product.price"
                  button-class="p-1 sm:p-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg"
                  icon-class="h-4 w-4 sm:h-5 sm:w-5"
                  :show-icon="true"
                  label=""
                />
              </div>
            </div>
          </Link>
        </div>
      </div>
    </section>

    <!-- Reviews Section -->
    <section class="py-12 bg-navy-900 text-white">
      <div class="container mx-auto px-4">
        <div class="flex items-center space-x-2 mb-2">
          <div class="w-4 h-8 bg-white rounded"></div>
          <span class="text-sm font-medium">Reviews</span>
        </div>
        <h2 class="text-2xl font-bold mb-8">What our customers say about</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="review in reviews"
            :key="review.id"
            class="bg-navy-800 rounded-lg p-6"
          >
            <div class="flex items-center mb-4">
              <img
                :src="review.avatar"
                :alt="review.name"
                class="w-12 h-12 rounded-full"
              />
              <div class="ml-4">
                <h3 class="font-medium">{{ review.name }}</h3>
                <p class="text-sm text-gray-400">{{ review.title }}</p>
              </div>
            </div>
            <p class="text-gray-300">{{ review.content }}</p>
            <p class="mt-4 text-sm text-gray-400">{{ review.date }}</p>
          </div>
        </div>
      </div>
    </section>
    <Footer />
    <AddToCartModal
      :is-open="showSuccessModal"
      :product="addedProduct"
      @close="showSuccessModal = false"
    />

    <!-- Replace the Floating Messenger Icon section with this -->
    <div v-if="page.props.auth && page.props.auth.customer" class="fixed bottom-6 right-6 z-50">
      <!-- Chat Window -->
      <div v-if="showChatWindow"
           ref="chatContainerRef"
           class="fixed bottom-20 right-6 bg-white rounded-lg shadow-xl overflow-hidden"
           :style="{
             width: chatWindowSize.width + 'px',
             height: chatWindowSize.height + 'px',
             minWidth: '300px',
             minHeight: '400px'
           }">
        <!-- Chat Header -->
        <div class="bg-navy-600 text-white p-3 flex justify-between items-center">
          <div class="flex items-center space-x-2">
            <MessageCircle class="h-5 w-5" />
            <span class="font-medium">Chatter AI</span>
          </div>
          <div class="flex items-center space-x-2">
            <!-- New Chat button -->
            <button @click="openNewChat" class="text-sm bg-navy-700 hover:bg-navy-800 rounded-md px-2 py-1" title="New Chat">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
            </button>

            <!-- History button -->
            <button @click="toggleHistory" class="text-sm bg-navy-700 hover:bg-navy-800 rounded-md px-2 py-1" title="Chat History">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
              </svg>
            </button>

            <!-- Close button -->
            <button @click="toggleChatWindow" class="hover:text-gray-300 flex items-center justify-center p-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Resize Handle -->
        <div class="absolute top-0 left-0 w-4 h-4 cursor-se-resize z-50"
             @mousedown.prevent="startResize">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-gray-400">
            <path d="M16 2H8v2h8V2zm0 7h8v2h-8V9zm0 7h8v2h-8v-2zm0 7h8v2h-8v-2z" />
          </svg>
        </div>

        <!-- Chat History Panel -->
        <div v-if="showHistory" class="absolute inset-0 bg-white z-40 flex flex-col" style="top: 48px;">
          <div class="p-3 flex justify-between items-center border-b">
            <h3 class="font-medium text-navy-800">Chat History</h3>
            <button @click="toggleHistory" class="p-1 hover:bg-gray-100 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <div class="flex-1 overflow-y-auto p-3">
            <div v-if="loadingHistory" class="flex justify-center py-4">
              <svg class="animate-spin h-5 w-5 text-navy-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
            <div v-else-if="Object.keys(chatHistory).length === 0" class="text-center text-gray-500 py-8">
              No chat history found.
              <div class="mt-2 text-sm">Start a new conversation!</div>
              <button @click="openNewChat" class="mt-4 px-4 py-2 bg-navy-600 text-white rounded-md hover:bg-navy-700">
                New Conversation
              </button>
            </div>
            <div v-else>
              <div class="mb-6 flex justify-between items-center">
                <h4 class="font-medium text-navy-700">Your Conversations</h4>
                <button @click="openNewChat" class="px-3 py-1 bg-navy-600 text-white text-sm rounded-md hover:bg-navy-700 flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                  </svg>
                  New Chat
                </button>
              </div>

              <div v-for="(chats, date) in chatHistory" :key="date" class="mb-4">
                <div class="text-xs text-gray-500 mb-2">{{ date }}</div>
                <div v-for="chat in chats" :key="chat.id"
                     class="p-3 border rounded-md mb-2 hover:bg-gray-50 cursor-pointer"
                     @click="loadHistoryConversation(chat.id)">
                  <div class="flex justify-between items-start">
                    <div class="text-sm font-medium truncate max-w-[85%]">{{ chat.title }}</div>
                    <button
                      @click="(event) => deleteHistoryConversation(event, chat.id)"
                      class="text-red-500 hover:text-red-700 p-1 rounded-full hover:bg-red-50"
                      title="Delete conversation"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                  <div class="text-xs text-gray-500 flex justify-between mt-1">
                    <span>{{ new Date(chat.created_at).toLocaleTimeString() }}</span>
                    <span class="text-navy-600">Click to view conversation</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 overflow-y-auto p-4" :style="{ height: `${chatWindowSize.height - 140}px` }">
          <div v-for="(message, index) in chatMessages" :key="index"
               class="mb-4"
               :class="{ 'flex justify-end': message.type === 'user' }">
            <div :class="{
              'bg-navy-600 text-white rounded-lg p-3 max-w-[80%] break-words': message.type === 'user',
              'bg-gray-100 text-gray-800 rounded-lg p-3 max-w-[80%] break-words': message.type === 'bot'
            }"
            v-html="message.content">
            </div>
          </div>
          <div v-if="isLoading" class="flex items-center space-x-2 text-gray-500">
            <div class="animate-bounce">●</div>
            <div class="animate-bounce" style="animation-delay: 0.2s">●</div>
            <div class="animate-bounce" style="animation-delay: 0.4s">●</div>
          </div>
        </div>

        <!-- Chat Input -->
        <div class="border-t p-4 bg-white">
          <div class="flex space-x-2">
            <input
              v-model="userInput"
              type="text"
              placeholder="Type your message..."
              class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:border-navy-600"
              @keyup.enter="sendMessage"
            />
            <button
              @click="sendMessage"
              :disabled="isLoading || !userInput.trim()"
              class="bg-navy-600 text-white px-4 py-2 rounded-lg hover:bg-navy-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="!isLoading" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
              </svg>
              <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Messenger Button -->
      <button
        @click="toggleChatWindow"
        class="bg-navy-600 hover:bg-navy-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 hover:scale-110"
      >
        <MessageCircle class="h-6 w-6" />
      </button>
    </div>
  </div>
</template>

<style scoped>
/* Add these styles if you want a pulse animation */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(26, 35, 126, 0.7);
  }
  70% {
    box-shadow: 0 0 0 1px rgba(26, 35, 126, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(26, 35, 126, 0);
  }
}

.group:hover button {
  animation: pulse 2s infinite;
}

.animate-bounce {
  animation: bounce 1s infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}

/* Add smooth transitions for chat window resizing */
[style*="width"][style*="height"] {
  transition: none;
}

/* Add these new styles */
.break-words {
  word-wrap: break-word;
  word-break: break-word;
}

/* Add a visible resize handle */
[class*="cursor-se-resize"] {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 15px;
  height: 15px;
  background: linear-gradient(135deg, transparent 50%, #cbd5e0 50%);
}

/* Message styling improvements */
:deep(.bg-gray-100) {
  line-height: 1.5;
}

:deep(.bg-gray-100 a) {
  color: #1a237e;
  text-decoration: underline;
  font-weight: 500;
}

:deep(.bg-gray-100 strong) {
  font-weight: 600;
}

:deep(code) {
  font-family: monospace;
  padding: 1px 4px;
  border-radius: 3px;
  font-size: 0.9em;
}

/* Add natural spacing for bullet points */
:deep(.bg-gray-100) {
  white-space: pre-line;
}
</style>

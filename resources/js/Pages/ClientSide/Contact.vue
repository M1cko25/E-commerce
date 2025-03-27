<script setup>
import { ref } from 'vue';
import { Link } from "@inertiajs/vue3";
import NavLink from "../../Components/NavLink.vue";
import Footer from "../../Components/Footer.vue";
import { MapPin, Phone, Mail, Clock } from 'lucide-vue-next';

const form = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
});

const handleSubmit = () => {
  // TODO: Implement form submission logic
  console.log('Form submitted:', form.value);
};
</script>

<template>
  <div class="min-h-screen bg-gray-200">
    <NavLink />

    <!-- Hero Section -->
    <section class="py-16 bg-navy-900 text-white">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Contact Us</h1>
        <p class="text-lg md:text-xl max-w-2xl mx-auto">
          We're here to help! Send us a message and we'll get back to you as soon as possible.
        </p>
      </div>
    </section>

    <!-- Contact Information -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
          <!-- Contact Form -->
          <div class="bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-6">Send us a Message</h2>
            <form @submit.prevent="handleSubmit" class="space-y-6">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input
                  type="text"
                  id="name"
                  v-model="form.name"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-navy-600 focus:border-transparent"
                  required
                />
              </div>
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                  type="email"
                  id="email"
                  v-model="form.email"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-navy-600 focus:border-transparent"
                  required
                />
              </div>
              <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input
                  type="text"
                  id="subject"
                  v-model="form.subject"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-navy-600 focus:border-transparent"
                  required
                />
              </div>
              <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea
                  id="message"
                  v-model="form.message"
                  rows="4"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-navy-600 focus:border-transparent"
                  required
                ></textarea>
              </div>
              <button
                type="submit"
                class="w-full bg-navy-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-navy-700 transition duration-300"
              >
                Send Message
              </button>
            </form>
          </div>

          <!-- Contact Information -->
          <div class="space-y-8">
            <div>
              <h2 class="text-2xl font-bold mb-6">Contact Information</h2>
              <div class="space-y-4">
                <div class="flex items-start space-x-4">
                  <MapPin class="w-6 h-6 text-navy-600 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold">Address</h3>
                    <p class="text-gray-600">123 Hardware Street, Construction City, 12345</p>
                  </div>
                </div>
                <div class="flex items-start space-x-4">
                  <Phone class="w-6 h-6 text-navy-600 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold">Phone</h3>
                    <p class="text-gray-600">+1 (123) 456-7890</p>
                  </div>
                </div>
                <div class="flex items-start space-x-4">
                  <Mail class="w-6 h-6 text-navy-600 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold">Email</h3>
                    <p class="text-gray-600">info@constructionhardware.com</p>
                  </div>
                </div>
                <div class="flex items-start space-x-4">
                  <Clock class="w-6 h-6 text-navy-600 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold">Business Hours</h3>
                    <p class="text-gray-600">Monday - Friday: 9:00 AM - 6:00 PM</p>
                    <p class="text-gray-600">Saturday: 9:00 AM - 4:00 PM</p>
                    <p class="text-gray-600">Sunday: Closed</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Map Placeholder -->
            <div class="bg-gray-200 rounded-lg h-64 flex items-center justify-center">
              <p class="text-gray-600">Map will be integrated here</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>

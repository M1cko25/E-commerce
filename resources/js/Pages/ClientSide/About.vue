<script setup>
import { Link } from "@inertiajs/vue3";
import NavLink from "../../Components/NavLink.vue";
import Footer from "../../Components/Footer.vue";
import adriano from '@/images/adriano.jpg'
import glenn from '@/images/Glenn.jpg'
import jonel from '@/images/jonel.jpg'
import julius from '@/images/Juluis.jpg'
import nash from '@/images/nash.jpg'
import neo from '@/images/neo.jpg'
import norman from '@/images/norman.jpg'
const teamMembers = [
  {
    name: "Adriano",
    role: "Backend",
    image: adriano,
  },
  {
    name: "Glenn",
    role: "Quality Assurance",
    image: glenn,
  },
  {
    name: "Jonel",
    role: "UI/UX Designer",
    image: jonel,
  },{
    name: "Julius",
    role: "Frontend",
    image: julius,
  }, {
    name: "Nash",
    role: "Fullstack",
    image: nash,
  }, {
    name: "Neo",
    role: "Project Manager",
    image: neo,
  }, {
    name: "Norman",
    role: "Frontend",
    image: norman,
  }
];
</script>

<template>
  <div class="min-h-screen bg-gray-200">
    <!-- Header/Navigation -->
    <NavLink />

    <!-- Hero Section -->
    <section class="py-16 bg-navy-900 text-white">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">About Us</h1>
        <p class="text-lg md:text-xl max-w-2xl mx-auto">
          Your trusted partner in hardware and construction supplies. We're committed to providing quality products and exceptional service.
        </p>
      </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
          <h2 class="text-3xl font-bold mb-8 text-center">Our Story</h2>
          <p class="text-gray-600 mb-6">
            Founded in 2020, we started with a simple mission: to provide high-quality hardware and construction materials to both professionals and DIY enthusiasts. Our journey began with a small shop and a big dream.
          </p>
          <p class="text-gray-600 mb-6">
            Today, we've grown into a trusted name in the industry, serving thousands of customers with the same dedication and commitment to quality that we started with.
          </p>
        </div>
      </div>
    </section>

    <!-- Our Values Section -->
    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-12 text-center">Our Values</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center p-6">
            <div class="w-16 h-16 bg-navy-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Quality</h3>
            <p class="text-gray-600">We never compromise on the quality of our products</p>
          </div>
          <div class="text-center p-6">
            <div class="w-16 h-16 bg-navy-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Innovation</h3>
            <p class="text-gray-600">Constantly evolving to meet modern needs</p>
          </div>
          <div class="text-center p-6">
            <div class="w-16 h-16 bg-navy-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold mb-2">Customer Service</h3>
            <p class="text-gray-600">Dedicated support for all your needs</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Team Section -->
    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-12 text-center">Our Team</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
          <div v-for="member in teamMembers" :key="member.name" class="text-center">
            <img :src="member.image" :alt="member.name" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover">
            <h3 class="text-xl font-semibold mb-1">{{ member.name }}</h3>
            <p class="text-gray-600">{{ member.role }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact CTA Section -->
    <section class="py-16 bg-navy-900 text-white">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Get in Touch</h2>
        <p class="text-lg mb-8">Have questions? We'd love to hear from you.</p>
        <Link
          href="/contact"
          class="inline-block bg-white text-navy-900 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300"
        >
          Contact Us
        </Link>
      </div>
    </section>

    <Footer />
  </div>
</template>

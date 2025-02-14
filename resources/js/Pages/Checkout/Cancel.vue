<template>
  <AuthenticatedLayout title="Payment Cancelled">
    <Head title="Payment Cancelled" />
    
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-3xl font-bold text-red-600 mb-4">Pago cancelado</h1>
      <div class="space-y-4">
        <p class="text-lg">El proceso de pago fue cancelado.</p>
        <p class="text-gray-600">
          Puedes intentar nuevamente o contactar a soporte si necesitas ayuda.
        </p>
        <div class="mt-6 space-x-4">
          <Link 
            :href="route('cart')"
            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700"
          >
            Volver al carrito
          </Link>
          <Link 
            :href="route('home')"
            class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700"
          >
            Ir al inicio
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
</script> 
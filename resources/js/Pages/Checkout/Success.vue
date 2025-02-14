<template>
  <AuthenticatedLayout title="Order Confirmation">
    <Head title="Order Confirmation" />
    
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-3xl font-bold text-green-600 mb-4">¡Pago exitoso!</h1>
      <div class="space-y-4">
        <p class="text-lg">Número de orden: #{{ order.uuid }}</p>
        <p class="text-lg">Total pagado: ${{ order.total_cost }}</p>
        <p class="text-gray-600">
          Hemos enviado un correo de confirmación a tu dirección de email.
        </p>
        <div class="mt-6">
          <Link 
            :href="route('profile.orders')"
            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700"
          >
            Ver mis órdenes
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
  order: Object
})
</script> 
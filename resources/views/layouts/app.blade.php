<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-commerce System')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-800">E-commerce System</h1>
                    </div>
                    <div class="hidden md:block ml-10">
                        <div class="flex items-baseline space-x-4">
                            <a href="{{ route('customers.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('customers.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                Customers
                            </a>
                            <a href="{{ route('customer-types.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('customer-types.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                Customer Types
                            </a>
                            <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('products.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                Products
                            </a>
                            <a href="{{ route('payment-methods.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('payment-methods.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                Payment Methods
                            </a>
                            <a href="{{ route('orders.index') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('orders.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <div class="text-center text-gray-500 text-sm">
                © 2024 E-commerce System. Built with Laravel & Tailwind CSS.
            </div>
        </div>
    </footer>
</body>
</html>

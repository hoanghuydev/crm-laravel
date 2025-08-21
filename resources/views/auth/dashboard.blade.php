@extends('layouts.app')

@section('title', 'Dashboard - E-commerce System')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600">Welcome back, {{ $user->name }}!</p>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- User Profile Card -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg text-white p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold">{{ $user->name }}</h3>
                        <p class="text-blue-100">{{ ucfirst($user->role) }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-blue-100">{{ $user->email }}</p>
                    <p class="text-xs text-blue-200 mt-1">
                        Member since {{ $user->created_at->format('M Y') }}
                    </p>
                </div>
            </div>

            <!-- Account Status Card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 {{ $user->is_active ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                            @if($user->is_active)
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Account Status</h3>
                        <p class="{{ $user->is_active ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-600">
                        Last login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </p>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                    </div>
                </div>
                <div class="space-y-2">
                    @if($user->isAdmin() || $user->isStaff())
                        <a href="{{ route('customers.index') }}" class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                            View Customers
                        </a>
                        <a href="{{ route('products.index') }}" class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                            Manage Products
                        </a>
                        <a href="{{ route('orders.index') }}" class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                            View Orders
                        </a>
                    @endif
                    <a href="#" class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                        Update Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ \App\Models\User::count() }}</div>
                    <div class="text-sm text-gray-600">Total Users</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ \App\Models\Customer::count() }}</div>
                    <div class="text-sm text-gray-600">Customers</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600">{{ \App\Models\Product::count() }}</div>
                    <div class="text-sm text-gray-600">Products</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-2xl font-bold text-orange-600">{{ \App\Models\Order::count() }}</div>
                    <div class="text-sm text-gray-600">Orders</div>
                </div>
            </div>
        </div>

        <!-- Logout Section -->
        <div class="mt-8 flex justify-end">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition duration-150 ease-in-out">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

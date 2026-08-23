@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    Users
                </h1>

                <p class="text-sm text-gray-500">
                    View and manage your users.
                </p>
            </div>

        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow border overflow-hidden">

            @if ($users->count())

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50 border-b">
                            <tr>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    User
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Email
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Phone
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Email Status
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Account
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">
                                    Joined
                                </th>

                                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">
                                    Actions
                                </th>

                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($users as $user)
                                <tr class="border-b hover:bg-gray-50">

                                    <!-- User -->
                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-4">

                                            @if ($user->avatar)
                                                <img src="{{ asset('storage/' . $user->avatar) }}"
                                                    class="w-14 h-14 rounded-full object-cover">
                                            @else
                                                <div
                                                    class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center text-xl">
                                                    👤
                                                </div>
                                            @endif

                                            <div>

                                                <h3 class="font-semibold text-gray-800">
                                                    {{ $user->name }}
                                                </h3>

                                                <p class="text-sm text-gray-500">
                                                    ID: #{{ $user->id }}
                                                </p>

                                            </div>

                                        </div>

                                    </td>

                                    <!-- Email -->
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $user->email }}
                                    </td>

                                    <!-- Phone -->
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $user->phone ?? '-' }}
                                    </td>

                                    <!-- Email Status -->
                                    <td class="px-6 py-4">

                                        @if ($user->email_verified_at)
                                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                                                Verified
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">
                                                Unverified
                                            </span>
                                        @endif

                                    </td>

                                    <!-- Account Status -->
                                    <td class="px-6 py-4">

                                        @if ($user->is_active)
                                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">
                                                Disabled
                                            </span>
                                        @endif

                                    </td>

                                    <!-- Joined -->
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $user->created_at->format('d M Y') }}
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4">

                                        <div class="flex justify-end gap-2">

                                            <a href="{{ route('users.show', $user->id) }}"
                                                class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                                                View
                                            </a>

                                            <form action="{{ route('users.toggleStatus', $user->id) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                    class="px-4 py-2 rounded-lg {{ $user->is_active ? 'bg-amber-500 text-white hover:bg-amber-600' : 'bg-green-600 text-white hover:bg-green-700' }}">
                                                    {{ $user->is_active ? 'Disable' : 'Activate' }}
                                                </button>
                                            </form>

                                        </div>

                                    </td>

                                </tr>
                            @endforeach

                        </tbody>

                    </table>

                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t bg-gray-50">
                    {{ $users->links() }}
                </div>
            @else
                <div class="py-20 text-center">

                    <div class="text-6xl mb-4">
                        👤
                    </div>

                    <h2 class="text-xl font-bold text-gray-700">
                        No Users Found
                    </h2>

                    <p class="mt-2 text-gray-500">
                        There are no registered users yet.
                    </p>

                </div>

            @endif

        </div>

    </div>

@endsection

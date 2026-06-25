@extends('layouts.app')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow border p-8">

        <div class="flex items-center gap-6">

            <!-- Avatar -->
            @if ($user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}"
                    class="w-32 h-32 rounded-full object-cover border">
            @else
                <div class="w-32 h-32 rounded-full bg-gray-100 flex items-center justify-center text-5xl">
                    👤
                </div>
            @endif

            <div>

                <h1 class="text-3xl font-bold text-gray-800">
                    {{ $user->name }}
                </h1>

                <p class="text-gray-500 mt-1">
                    User ID: #{{ $user->id }}
                </p>

                @if ($user->email_verified_at)
                    <span class="inline-block mt-4 px-4 py-1 bg-green-100 text-green-700 rounded-full">
                        Verified
                    </span>
                @else
                    <span class="inline-block mt-4 px-4 py-1 bg-red-100 text-red-700 rounded-full">
                        Unverified
                    </span>
                @endif

            </div>

        </div>

    </div>

    <!-- Information -->
    <div class="bg-white rounded-2xl shadow border p-8">

        <h2 class="text-xl font-bold mb-6">
            User Information
        </h2>

        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <p class="text-sm text-gray-500">
                    Full Name
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $user->name }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">
                    Email
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $user->email }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">
                    Phone Number
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $user->phone ?? 'N/A' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">
                    Email Verified At
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $user->email_verified_at ?? 'Not Verified' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">
                    Joined Date
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $user->created_at->format('d M Y, h:i A') }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">
                    Last Updated
                </p>

                <p class="font-semibold text-gray-800">
                    {{ $user->updated_at->format('d M Y, h:i A') }}
                </p>
            </div>

        </div>

    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3">

        <a href="{{ route('users.index') }}"
            class="px-5 py-3 border rounded-xl hover:bg-gray-100">
            Back
        </a>

    </div>

</div>

@endsection
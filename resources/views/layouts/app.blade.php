<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HRPP Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<body class="bg-gray-100 min-h-screen text-gray-800">

    <header class="bg-white shadow p-4 mb-6">
        <div class="container mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <a href="/" class="text-xl font-bold">HR Posting Partner</a>

            @auth
                <div class="flex items-center justify-between md:justify-end gap-4">
                    <nav class="flex items-center gap-4 text-sm">
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                        <a href="{{ route('admin.jobs.index') }}" class="text-gray-700 hover:text-blue-600">Jobs</a>
                        <a href="{{ route('admin.blogs.index') }}" class="text-gray-700 hover:text-blue-600">Blogs</a>
                    </nav>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-red-500">Logout</button>
                    </form>
                </div>
            @endauth
        </div>
    </header>

    <main class="container mx-auto px-4">
        @yield('content')
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Blade script stack -->
    @stack('scripts')

</body>
</html>

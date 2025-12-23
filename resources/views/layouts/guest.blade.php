<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Partner Account') }} - @yield('title', 'Login')</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                <span class="inline-flex items-center justify-center">
                    <svg class="h-8 w-8 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 2C7.029 2 3 6.029 3 11v3c0 4.971 4.029 9 9 9s9-4.029 9-9v-3c0-4.971-4.029-9-9-9Z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M7 11h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M9 15h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Partner Account
                </span>
            </h1>
            <p class="text-gray-600 mt-2">Account Tracker</p>
        </div>
        
        <!-- Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 sm:p-8">
            @yield('content')
        </div>
    </div>

    <!-- Toast notification container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50"></div>

    <!-- Base JavaScript -->
    <script>
        // Show toast notification
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg mb-2 transform transition-all duration-300`;
            toast.textContent = message;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    </script>

    @yield('scripts')
</body>
</html>

@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Login</h2>

<form id="loginForm" class="space-y-4">
    <!-- Email field -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" id="email" name="email" required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        <p class="text-red-500 text-sm mt-1 hidden" id="email-error"></p>
    </div>

    <!-- Password field -->
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" id="password" name="password" required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        <p class="text-red-500 text-sm mt-1 hidden" id="password-error"></p>
    </div>

    <!-- Remember me -->
    <div class="flex items-center">
        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 rounded border-gray-300">
        <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
    </div>

    <!-- Error message container -->
    <div id="form-error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm hidden"></div>

    <!-- Submit button -->
    <button type="submit" id="submitBtn"
        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
        Login
    </button>
</form>

<!-- Register link -->
<p class="text-center text-sm text-gray-600 mt-6">
    Don't have an account? 
    <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-medium">Register</a>
</p>
@endsection

@section('scripts')
<script>
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const formError = document.getElementById('form-error');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Logging in...';
        formError.classList.add('hidden');

        // Clear previous errors
        document.querySelectorAll('[id$="-error"]').forEach(el => el.classList.add('hidden'));

        try {
            const response = await fetch('{{ route("login") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                    remember: document.getElementById('remember').checked
                })
            });

            const data = await response.json();

            if (data.success) {
                showToast('Login successful!');
                window.location.href = data.redirect;
            } else {
                // Show error message
                formError.textContent = data.message || 'Login failed';
                formError.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Login';
            }
        } catch (error) {
            // Handle validation errors
            if (error.response && error.response.status === 422) {
                const errors = error.response.data.errors;
                Object.keys(errors).forEach(field => {
                    const errorEl = document.getElementById(`${field}-error`);
                    if (errorEl) {
                        errorEl.textContent = errors[field][0];
                        errorEl.classList.remove('hidden');
                    }
                });
            } else {
                formError.textContent = 'An error occurred. Please try again.';
                formError.classList.remove('hidden');
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Login';
        }
    });
</script>
@endsection

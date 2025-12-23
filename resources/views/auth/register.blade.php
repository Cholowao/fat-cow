@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Create Account</h2>

<form id="registerForm" class="space-y-4">
    <!-- Name field -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" id="name" name="name" required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        <p class="text-red-500 text-sm mt-1 hidden" id="name-error"></p>
    </div>

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
        <input type="password" id="password" name="password" required minlength="6"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
        <p class="text-red-500 text-sm mt-1 hidden" id="password-error"></p>
    </div>

    <!-- Confirm Password field -->
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
    </div>

    <!-- Error message container -->
    <div id="form-error" class="bg-red-50 text-red-600 p-3 rounded-lg text-sm hidden"></div>

    <!-- Submit button -->
    <button type="submit" id="submitBtn"
        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
        Register
    </button>
</form>

<!-- Login link -->
<p class="text-center text-sm text-gray-600 mt-6">
    Already have an account? 
    <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Login</a>
</p>
@endsection

@section('scripts')
<script>
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const formError = document.getElementById('form-error');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating account...';
        formError.classList.add('hidden');

        // Clear previous errors
        document.querySelectorAll('[id$="-error"]').forEach(el => el.classList.add('hidden'));

        try {
            const response = await fetch('{{ route("register") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                    password_confirmation: document.getElementById('password_confirmation').value
                })
            });

            const data = await response.json();

            if (data.success) {
                showToast('Account created successfully!');
                window.location.href = data.redirect;
            } else if (data.errors) {
                // Show validation errors
                Object.keys(data.errors).forEach(field => {
                    const errorEl = document.getElementById(`${field}-error`);
                    if (errorEl) {
                        errorEl.textContent = data.errors[field][0];
                        errorEl.classList.remove('hidden');
                    }
                });
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register';
            } else {
                formError.textContent = data.message || 'Registration failed';
                formError.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register';
            }
        } catch (error) {
            formError.textContent = 'An error occurred. Please try again.';
            formError.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Register';
        }
    });
</script>
@endsection

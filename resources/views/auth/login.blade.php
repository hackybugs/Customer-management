@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center">
    <div class="card shadow-lg p-4" style="width: 500px;">
        <div class="card-body">
            <h2 class="text-center">Login</h2>
            <p id="message" class="text-center text-danger"></p>

            <form id="loginForm">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter Password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <p class="text-center mt-3">
                Don't have an account? <a href="{{ url('/register-form') }}">Register</a>
            </p>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function(){
        let token = localStorage.getItem("access_token");

        if (token) {
            window.location.href = "{{ url('/dashboard') }}";
        }
        $("#loginForm").submit(function(e){
            e.preventDefault();
            $.ajax({
                url: "{{ url('/api/login') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    localStorage.setItem("temp_token", response.temp_token);
                    $("#message").text(response.message).css("color", "green");

                    // Redirect user to OTP verification page after success
                    setTimeout(function(){
                        window.location.href = "{{ url('/verify-otp') }}";
                    }, 1500);
                },
                error: function(response) {
                    $("#message").text(response.responseJSON.message);
                }
            });
        });
    });
</script>

@endpush
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center">
    <div class="card shadow-lg p-4" style="width: 500px;">
        <div class="card-body">
            <h2 class="text-center">Register</h2>
            <p id="message" class="text-center text-danger"></p>

            <form id="registerForm">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" id="name" placeholder="Enter Your Name" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter Password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>

            <p class="text-center mt-3">
                Already have an account? <a href="{{ url('/login-form') }}">Login</a>
            </p>
        </div>
    </div>
</div>
@endsection
@push('scripts')

<script>
    $(document).ready(function(){
        $("#registerForm").submit(function(e){
            e.preventDefault();
            $.ajax({
                url: "{{ url('/api/register') }}", 
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
                    $("#message").text(response.responseJSON.message).css("color", "red");
                }
            });
        });
    });
</script>
@endpush
@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center">
    <div class="card shadow-lg p-4" style="width: 350px;">
        <div class="card-body">
            <h2 class="text-center">Verify OTP</h2>
            <p class="text-center">Enter the OTP sent to your email.</p>
            <p id="message" class="text-center text-danger"></p>

            <form id="verifyOtpForm">
                @csrf
                <input type="hidden" name="temp_token" id="temp_token" required>

                <div class="d-flex justify-content-between mb-3 otp-input-group">
                    <input type="text" class="form-control text-center otp-field" maxlength="1">
                    <input type="text" class="form-control text-center otp-field" maxlength="1">
                    <input type="text" class="form-control text-center otp-field" maxlength="1">
                    <input type="text" class="form-control text-center otp-field" maxlength="1">
                    <input type="text" class="form-control text-center otp-field" maxlength="1">
                    <input type="text" class="form-control text-center otp-field" maxlength="1">
                </div>

                <input type="hidden" name="email_otp" id="email_otp">
                <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function(){
        let token = localStorage.getItem("temp_token");
        
        if (!token) {
            $("#message").text("Session expired. Please register again.").css("color", "red");
            return; // Stop further execution
        }

        $("#temp_token").val(token);
        $(".otp-field").on("input", function() {
            let currentInput = $(this);
            let nextInput = currentInput.next(".otp-field");
            let prevInput = currentInput.prev(".otp-field");

            if (currentInput.val().length === 1) {
                nextInput.focus();
            } else if (event.inputType === "deleteContentBackward") {
                prevInput.focus();
            }

            let otpValue = "";
            $(".otp-field").each(function() {
                otpValue += $(this).val();
            });
            $("#email_otp").val(otpValue);
        });

        $("#verifyOtpForm").submit(function(e){
            e.preventDefault();
            $.ajax({
                url: "{{ url('/api/verify-otp') }}", 
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    $("#message").text(response.message).css("color", "green");

                    // Remove temp_token after successful verification
                    localStorage.removeItem("temp_token");
                    localStorage.setItem("access_token",response.access_token);


                    // Redirect user to dashboard after success
                    setTimeout(function(){
                        window.location.href = "{{ url('/dashboard') }}";
                    }, 1500);
                },
                error: function(response) {
                    $("#message").text(response.responseJSON.message).css("color", "red");
                    window.location.href = "{{ url('/login-form') }}";

                }
            });
        });
    });
</script>
@endpush

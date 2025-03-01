@extends('layouts.app')

@section('content')
<div class="">
    <div class="row justify-content-center align-items-center ">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h2 id="formTitle" class="text-center">Create Customer</h2>
                    <p id="message" class="text-center text-success"></p>

                    <form id="customerForm">
                        @csrf
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="first_name" placeholder="Enter First Name" required>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Enter Last Name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email" required>
                            <div class="form-text">We'll never share your email with anyone else.</div>
                        </div>

                        <div class="mb-3">
                            <label for="dob" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="dob" id="dob" required>
                        </div>

                        <div class="mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="number" name="age" id="age" class="form-control" placeholder="Enter Age" required>
                        </div>

                        <input type="hidden" name="customer_id" id="customer_id">
                        <button type="submit" id="submitBtn" class="btn btn-primary w-100">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
    $(document).ready(function(){
        let token = localStorage.getItem("access_token");

        if (!token) {
            $("#message").text("Unauthorized access! Please log in.").css("color", "red");
            return;
        }

        // Check if Editing a Customer
        let editCustomer = localStorage.getItem("edit_customer");

        if (editCustomer) {
            let customer = JSON.parse(editCustomer);
            $("#formTitle").text("Edit Customer");
            $("#submitBtn").text("Update");

            // Fill form with customer data
            $("#customer_id").val(customer.id);
            $("#first_name").val(customer.first_name);
            $("#last_name").val(customer.last_name);
            $("#email").val(customer.email);
            $("#dob").val(customer.dob);
            $("#age").val(customer.age);
        }

        // Submit Form (Create or Update)
        $("#customerForm").submit(function(e){
            e.preventDefault();

            let customerId = $("#customer_id").val();
            let url = customerId ? `{{ url('/api/customers') }}/${customerId}` : "{{ url('/api/customers') }}";
            let method = customerId ? "PUT" : "POST";

            $.ajax({
                url: url,
                type: method,
                headers: { "Authorization": "Bearer " + token },
                data: $(this).serialize(),
                success: function(response) {
                    $("#message").text(response.message).css("color", "green");
                    localStorage.removeItem("edit_customer");

                    // Redirect after success
                    setTimeout(function(){
                        window.location.href = "{{ url('/dashboard') }}";
                    }, 1500);
                },
                error: function(response) {
                    if (xhr.status === 401) {
                        localStorage.removeItem("access_token");
                        alert("Session expired. Please log in again.");
                        window.location.href = "{{ url('/login-form') }}";
                    }
                    $("#message").text(response.responseJSON.message).css("color", "red");
                }
            });
        });
    });
</script>
@endpush

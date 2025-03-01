@extends('layouts.app')

@section('content')
<!-- <div class="container"> -->
    <h2>Customer List</h2>
    <p id="message"></p>

    <button id="addCustomer" style="margin-bottom: 10px;">Add Customer</button>

    <table id="customerTable" class="display">
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Customers will be loaded dynamically via DataTables -->
        </tbody>
    </table>
<!-- </div> -->
@endsection

@push('scripts')
<!-- jQuery & DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function(){
        let token = localStorage.getItem("access_token");

        if (!token) {
            $("#message").text("Unauthorized access! Please log in.").css("color", "red");
            return;
        }

        // Redirect to Add Customer Page
        $("#addCustomer").click(function(){
            localStorage.removeItem("edit_customer"); // Ensure no edit data is stored
            window.location.href = "{{ url('/customer-form') }}";
        });

        // Initialize DataTables
        $('#customerTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('/api/customers') }}",
                type: "GET",
                headers: { "Authorization": "Bearer " + token },
                dataSrc: 'data'
            },
            columns: [
                { data: "id" },
                { data: "first_name" },
                { data: "last_name" },
                { data: "email" },
                { 
                    data: "id",
                    render: function(data, type, row) {
                        let buttons='';
                        buttons+= `<span> <button class="editCustomer" data-customer-id="${data}">Edit</button>`;
                        buttons+=`<button class="deleteCustomer" data-customer-id="${data}">Delete</button></span>`;
                        return buttons;
                    }
                }
            ]
        });
        $(document).on("click",".deleteCustomer",function(){
            let id = $(this).data("customer-id");
            $.ajax({
                url:`{{url('/api/customers')}}/${id}`,
                type:"delete",
                headers:{"Authorization":"Bearer "+token},
                success:function(response){
                    localStorage.removeItem("edit_customer");
                    $("#message").text(response.message).css("color", "green");
                    window.location.href = "{{ url('/dashboard') }}";
                },
                error: function(response) {
                    if (xhr.status === 401) {
                        localStorage.removeItem("access_token");
                        alert("Session expired. Please log in again.");
                        window.location.href = "{{ url('/login-form') }}";
                    }
                    $("#message").text("Failed to delete customer details.").css("color", "red");
                }
            });
        });
        // Handle Edit Button Click
        $(document).on("click", ".editCustomer", function(){
            let id = $(this).data("customer-id");

            $.ajax({
                url: `{{ url('/api/customers')}}/${id}`,
                type: "GET",
                headers: { "Authorization": "Bearer " + token },
                success: function(response) {
                    localStorage.setItem("edit_customer", JSON.stringify(response.data));
                    window.location.href = "{{ url('/customer-form') }}";
                },
                error: function(response) {
                    if (xhr.status === 401) {
                        localStorage.removeItem("access_token");
                        alert("Session expired. Please log in again.");
                        window.location.href = "{{ url('/login-form') }}";
                    }
                    $("#message").text("Failed to fetch customer details.").css("color", "red");
                }
            });
        });
    });
</script>
@endpush

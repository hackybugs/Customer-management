<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel App') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        /* .container {
            max-width: 500px;
            margin-top: 50px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        } */
        .app{
            width: 100%;
            height: auto;
            background:#f8f9fa;
            padding: 20px;
            margin-top: 50px;

        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="#">My App</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto" id="navLinks">
                        <!-- Links will be dynamically updated by jQuery -->
                    </ul>
                </div>
            </div>
        </nav>
    <div class="app">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let token = localStorage.getItem("access_token");
            let navLinks = $("#navLinks");

            if (token) {
                // User is logged in
                navLinks.html(`
                    <li class="nav-item"><a class="nav-link" href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" id="logoutBtn">Logout</a></li>
                `);
            } else {
                // User is NOT logged in
                navLinks.html(`
                    <li class="nav-item"><a class="nav-link" href="{{ url('/login-form') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/register-form') }}">Register</a></li>
                `);
            }

            // Logout button click event
            $(document).on("click", "#logoutBtn", function(e) {
                e.preventDefault();               
                $.ajax({
                url: "{{ url('/api/logout') }}", 
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    localStorage.removeItem("access_token");
                    alert("Logged out successfully!");
                    // Redirect user to OTP verification page after success
                    window.location.href = "{{ url('/login-form') }}";

                }
            });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>

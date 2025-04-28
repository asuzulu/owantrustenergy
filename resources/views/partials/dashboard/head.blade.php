<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Owan Trust Energy')</title>

<!-- Favicon -->
<link rel="icon" href="{{ asset('images/fav-icon.png') }}" type="image/x-icon">

<!-- Stylesheets -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600">
<link rel="stylesheet" href="{{ asset('dashboard/css/fontawesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('dashboard/css/fullcalendar.min.css') }}">
<link rel="stylesheet" href="{{ asset('dashboard/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('dashboard/css/dashboard.css') }}">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Only once -->

<!-- Bootstrap JS and Popper.js -->
@if (!isset($useBootstrap5) || !$useBootstrap5)
    {{-- Use Bootstrap 4 --}}
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
@else
    {{-- Use Bootstrap 5 --}}
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
@endif

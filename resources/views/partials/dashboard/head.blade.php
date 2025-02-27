{{-- resources/views/partials/dashboard/head.blade.php --}}
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

<!-- Add Croppie CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" />

<!-- Add Croppie JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


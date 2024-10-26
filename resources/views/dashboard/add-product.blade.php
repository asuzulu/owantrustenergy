@extends('layouts.dashboard')

@section('head')
    @include('partials.dashboard.head', [
        'title' => 'Add Product - Dashboard Admin Template',
        'additionalStyles' => [
            'https://fonts.googleapis.com/css?family=Open+Sans:300,400,600',
            asset('css/fontawesome.min.css'),
            asset('jquery-ui-datepicker/jquery-ui.min.css'),
            asset('css/bootstrap.min.css'),
            asset('css/dashboard.css'),
        ]
    ])
@endsection

@section('navbar')
    @include('partials.dashboard.navbar')
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="row tm-mt-big">
                    <div class="col-xl-8 col-lg-10 col-md-12 col-sm-12">
                        <div class="bg-white tm-block">
                            <div class="row">
                                <div class="col-12">
                                    <h2 class="tm-block-title d-inline-block">Add Product</h2>
                                </div>
                            </div>
                            <div class="row mt-4 tm-edit-product-row">
                                <div class="col-xl-7 col-lg-7 col-md-12">
                                    <form action="{{ route('products.store') }}" method="POST" class="tm-edit-product-form">
                                        @csrf
                                        <div class="input-group mb-3">
                                            <label for="name" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 col-form-label">Product Name</label>
                                            <input id="name" name="name" type="text" class="form-control validate col-xl-9 col-lg-8 col-md-8 col-sm-7" required>
                                        </div>
                                        <div class="input-group mb-3">
                                            <label for="description" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 mb-2">Description</label>
                                            <textarea id="description" name="description" class="form-control validate col-xl-9 col-lg-8 col-md-8 col-sm-7" rows="3" required></textarea>
                                        </div>
                                        <div class="input-group mb-3">
                                            <label for="category" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 col-form-label">Category</label>
                                            <select class="custom-select col-xl-9 col-lg-8 col-md-8 col-sm-7" id="category" name="category" required>
                                                <option selected>Select one</option>
                                                <option value="1">Cras efficitur lacus</option>
                                                <option value="2">Pellentesque molestie</option>
                                                <option value="3">Sed feugiat nulla</option>
                                            </select>
                                        </div>
                                        <div class="input-group mb-3">
                                            <label for="expire_date" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 col-form-label">Expire Date</label>
                                            <input id="expire_date" name="expire_date" type="text" class="form-control validate col-xl-9 col-lg-8 col-md-8 col-sm-7" data-large-mode="true" required>
                                        </div>
                                        <div class="input-group mb-3">
                                            <label for="stock" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 col-form-label">Units In Stock</label>
                                            <input id="stock" name="stock" type="number" class="form-control validate col-xl-9 col-lg-8 col-md-7 col-sm-7" required>
                                        </div>
                                        <div class="input-group mb-3">
                                            <div class="ml-auto col-xl-8 col-lg-8 col-md-8 col-sm-7 pl-0">
                                                <button type="submit" class="btn btn-primary">Add</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-12 mx-auto mb-4">
                                    <div class="tm-product-img-dummy mx-auto">
                                        <i class="fas fa-5x fa-cloud-upload-alt" onclick="document.getElementById('fileInput').click();"></i>
                                    </div>
                                    <div class="custom-file mt-3 mb-3">
                                        <input id="fileInput" type="file" style="display:none;" name="product_image" />
                                        <input type="button" class="btn btn-primary d-block mx-auto" value="Upload ..." onclick="document.getElementById('fileInput').click();"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('partials.dashboard.footer')
@endsection

@section('scripts')
    @include('partials.dashboard.scripts')
    <script src="{{ asset('jquery-ui-datepicker/jquery-ui.min.js') }}"></script>
    <script>
        $(function () {
            $('#expire_date').datepicker();
        });
    </script>
@endsection

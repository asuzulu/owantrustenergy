@extends('layouts.dashboard')

@section('head')
    @include('partials.dashboard.head', ['title' => 'Edit Product - Dashboard Template'])
@endsection

@section('navbar')
    @include('partials.dashboard.navbar')
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Add your Navbar here if you want to include it -->
            </div>
        </div>
        <div class="row tm-mt-big">
            <div class="col-xl-8 col-lg-10 col-md-12 col-sm-12">
                <div class="bg-white tm-block">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="tm-block-title d-inline-block">Edit Product</h2>
                        </div>
                    </div>
                    <div class="row mt-4 tm-edit-product-row">
                        <div class="col-xl-7 col-lg-7 col-md-12">
                            <form action="{{ route('products.update', $product->id) }}" method="POST" class="tm-edit-product-form">
                                @csrf
                                @method('PUT')
                                <div class="input-group mb-3">
                                    <label for="name" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 col-form-label">Product Name</label>
                                    <input placeholder="Product name" value="{{ old('name', $product->name) }}" id="name" name="name" type="text" class="form-control validate col-xl-9 col-lg-8 col-md-8 col-sm-7">
                                </div>
                                <div class="input-group mb-3">
                                    <label for="description" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 mb-2">Description</label>
                                    <textarea class="form-control validate col-xl-9 col-lg-8 col-md-8 col-sm-7" rows="3" placeholder="Product Description" required>{{ old('description', $product->description) }}</textarea>
                                </div>
                                <div class="input-group mb-3">
                                    <label for="category" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 col-form-label">Category</label>
                                    <select class="custom-select col-xl-9 col-lg-8 col-md-8 col-sm-7" id="category" name="category">
                                        <option value="1" {{ $product->category_id == 1 ? 'selected' : '' }}>Cras efficitur lacus</option>
                                        <option value="2" {{ $product->category_id == 2 ? 'selected' : '' }}>Pellentesque molestie</option>
                                        <option value="3" {{ $product->category_id == 3 ? 'selected' : '' }}>Sed feugiat nulla</option>
                                    </select>
                                </div>
                                <div class="input-group mb-3">
                                    <label for="expire_date" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 col-form-label">Expire Date</label>
                                    <input placeholder="Expire Date" value="{{ old('expire_date', $product->expire_date) }}" id="expire_date" name="expire_date" type="text" class="form-control validate col-xl-9 col-lg-8 col-md-8 col-sm-7" data-large-mode="true">
                                </div>
                                <div class="input-group mb-3">
                                    <label for="stock" class="col-xl-4 col-lg-4 col-md-4 col-sm-5 col-form-label">Units In Stock</label>
                                    <input placeholder="Stock" value="{{ old('stock', $product->stock) }}" id="stock" name="stock" type="text" class="form-control validate col-xl-9 col-lg-8 col-md-7 col-sm-7">
                                </div>
                                <div class="input-group mb-3">
                                    <div class="ml-auto col-xl-8 col-lg-8 col-md-8 col-sm-7 pl-0">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-12 mx-auto mb-4">
                            <img src="{{ asset('img/product-image.jpg') }}" alt="Product Image" class="img-fluid mx-auto d-block">
                            <div class="custom-file mt-3 mb-3">
                                <input id="fileInput" type="file" style="display:none;" />
                                <input type="button" class="btn btn-primary d-block mx-auto" value="Upload ..." onclick="document.getElementById('fileInput').click();" />
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
    <script>
        $(function () {
            $('#expire_date').datepicker();
        });
    </script>
@endsection

@extends('layouts.dashboard')

@section('content')
<div class="container" style="margin-top: -6rem;">
    <div class="row tm-content-row tm-mt-big">
        <div class="bg-white tm-block h-100">
            <div class="row">
                <div class="col-md-8 col-sm-12">
                    <h2 class="tm-block-title d-inline-block">Cylinders</h2>
                </div>
                <div class="col-md-4 col-sm-12 text-right">
                    <a href="add-product.html" class="btn btn-small btn-primary">Add New Cylinder</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped tm-table-striped-even mt-3">
                    <thead>
                        <tr class="tm-bg-gray">
                            <th scope="col">&nbsp;</th>
                            <th scope="col" style="width: 10%;">Cylinder #</th> <!-- Reduced width to one-third -->
                            <th scope="col" class="text-center">Size</th>
                            <th scope="col" class="text-center" style="width: 30%;">Location</th> <!-- Increased width by two-thirds -->
                            <th scope="col">Date Allocated</th>
                            <th scope="col">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" aria-label="Checkbox">
                            </th>
                            <td class="tm-product-name">000000001</td>
                            <td class="text-center">25kg</td>
                            <td class="text-center">Ahmed Ohida</td>
                            <td>2018-10-28</td>
                            <td><i class="fas fa-trash-alt tm-trash-icon"></i></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" aria-label="Checkbox">
                            </th>
                            <td class="tm-product-name">000000002</td>
                            <td class="text-center">12kg</td>
                            <td class="text-center">Chibueze Nwakachukwu</td>
                            <td>2018-10-24</td>
                            <td><i class="fas fa-trash-alt tm-trash-icon"></i></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" aria-label="Checkbox">
                            </th>
                            <td class="tm-product-name">000000003</td>
                            <td class="text-center">12kg</td>
                            <td class="text-center">Olamide Adeola</td>
                            <td>2019-02-14</td>
                            <td><i class="fas fa-trash-alt tm-trash-icon"></i></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" aria-label="Checkbox">
                            </th>
                            <td class="tm-product-name">000000004</td>
                            <td class="text-center">5kg</td>
                            <td class="text-center">Fatima Abubakar</td>
                            <td>2019-03-22</td>
                            <td><i class="fas fa-trash-alt tm-trash-icon"></i></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" aria-label="Checkbox">
                            </th>
                            <td class="tm-product-name">000000005</td>
                            <td class="text-center">25kg</td>
                            <td class="text-center">Chidera Okafor</td>
                            <td>2019-03-22</td>
                            <td><i class="fas fa-trash-alt tm-trash-icon"></i></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" aria-label="Checkbox">
                            </th>
                            <td class="tm-product-name">000000006</td>
                            <td class="text-center">5kg</td>
                            <td class="text-center">Toluwa Adebayo</td>
                            <td>2019-03-22</td>
                            <td><i class="fas fa-trash-alt tm-trash-icon"></i></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" aria-label="Checkbox">
                            </th>
                            <td class="tm-product-name">000000007</td>
                            <td class="text-center">12kg</td>
                            <td class="text-center">Aminu Bello</td>
                            <td>2019-03-22</td>
                            <td><i class="fas fa-trash-alt tm-trash-icon"></i></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <input type="checkbox" aria-label="Checkbox">
                            </th>
                            <td class="tm-product-name">000000008</td>
                            <td class="text-center">25kg</td>
                            <td class="text-center">Izevbizua Omoregie</td>
                            <td>2019-03-22</td>
                            <td><i class="fas fa-trash-alt tm-trash-icon"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="tm-table-mt tm-table-actions-row">
                <div class="tm-table-actions-col-left">
                    <button class="btn btn-danger">Edit Selected Items</button>
                </div>
                <div class="tm-table-actions-col-right">
                    <span class="tm-pagination-label">Page</span>
                    <nav aria-label="Page navigation" class="d-inline-block">
                        <ul class="pagination tm-pagination">
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <span class="tm-dots d-block">...</span>
                            </li>
                            <li class="page-item"><a class="page-link" href="#">13</a></li>
                            <li class="page-item"><a class="page-link" href="#">14</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <footer class="row tm-mt-small">
        <div class="col-12 font-weight-light">
            <p class="d-inline-block tm-bg-black text-white py-2 px-4">Your Company © 2024</p>
            <p class="d-inline-block float-right tm-footer-links">
                <a href="#" class="text-dark">Privacy</a>
                <a href="#" class="text-dark ml-3">Terms</a>
            </p>
        </div>
    </footer>
</div>
@endsection

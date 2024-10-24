@extends('layouts.app')

@section('title', 'Owan Trust Energy Products')

@section('content')

<h1 class="about_text" style="margin-top: 6rem;">Our Products:</h1>
<!-- trainer section start -->
<div class="trainer_section layout_padding" style="margin-top: -4rem;">
    <div class="container">
        <div class="row">
            <div class="col-sm-3">
                <div class="image_1"><img src="{{ asset('images/cylinder-logo.png') }}"></div>
                <h3 class="meditation_text">Gas Cylinders:</h3>
                <h4 class="font_style2">Revolutionizing Energy Consumption</h4>
                <p class="lorem_tetx">
                    At Owan Trust Energy, our flagship product is the uniquely designed gas cylinder. These cylinders aren't just containers for cooking gas; they represent a fusion of functionality and innovation. Each cylinder is branded with a distinct serial number, a feature that goes beyond mere identification. This numbering system is a gateway to our comprehensive tracking and loyalty programs, designed to reward our customers for their continued patronage. By focusing on such details, we ensure not only the quality of the product but also enhance customer engagement and satisfaction.
                </p>
            </div>

            <div class="col-sm-3">
                <div class="image_1"><img src="{{ asset('images/stove-logo.png') }}"></div>
                <h3 class="meditation_text">Gas Stoves & Burners:</h3>
                <h4 class="font_style2">A Blend of Efficiency and Style</h4>
                <p class="lorem_tetx">
                    The gas stoves and burners at Owan Trust Energy are a result of our collaboration with both domestic and international engineering experts. These products are not just tools for cooking; they are a statement of style and efficiency in the kitchen. Our stoves are designed to cater to the diverse culinary needs of our customers, offering them reliability and ease of use. Each stove and burner carries our brand name, a symbol of quality and trust. Our commitment to providing durable and efficient cooking solutions helps us in making a tangible difference in the daily lives of our customers.
                </p>
            </div>

            <div class="col-sm-3">
                <div class="image_1"><img src="{{ asset('images/accessories-logo.png') }}"></div>
                <h3 class="meditation_text">Accessories:</h3>
                <h4 class="font_style2">Enhancing the Cooking Experience</h4>
                <p class="lorem_tetx">
                    Understanding that the essence of a great cooking experience lies in the details, Owan Trust Energy offers a wide range of kitchen accessories. From durable hoses and precise control valves to grease trays and sleek countertops, each accessory is designed to complement our main products. These accessories are not just additions; they are integral components that enhance the functionality and safety of our gas cylinders and stoves. Our focus on providing a complete cooking solution is what makes Owan Trust Energy a one-stop shop for all kitchen gas needs.
                </p>
            </div>
        </div>
    </div>
</div>
<!-- trainer section end -->

@endsection

@extends('layouts.app')

@section('title', 'Contact - Owan Trust Energy')

@section('content')
    <!-- contact section start -->
    <div class="contact_section layout_padding" style="margin-top: 8rem;">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h1 class="contact_text">Contact Us</h1>
                    <div class="mail_section">
                        <input type="text" class="mail_text" placeholder="Name" name="name">
                        <input type="email" class="mail_text" placeholder="Email" name="email">
                        <input type="text" class="mail_text" placeholder="Phone Number" name="phone">
                        <textarea class="massage-bt" placeholder="Message" rows="5" id="comment" name="message"></textarea>
                        <div class="send_bt"><a href="#">SEND</a></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="social_icon">
                        <ul>
                            <li><a href="#"><img src="{{ asset('images/fb-icon.png') }}"></a></li>
                            <li><a href="#"><img src="{{ asset('images/twitter-icon.png') }}"></a></li>
                            <li><a href="#"><img src="{{ asset('images/instagram-icon.png') }}"></a></li>
                            <li><a href="#"><img src="{{ asset('images/linkdin-icon.png') }}"></a></li>
                        </ul>
                    </div>
                    <div class="map">
                        <div class="map-responsive">
                            <iframe
                                src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=Eiffel+Tower+Paris+France"
                                width="600" height="250" frameborder="0" style="border:0; width: 100%;" allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                    <div class="call_text"><img src="{{ asset('images/call-icon.png') }}"><span class="padding_left_0">+01 9876543210</span></div>
                    <div class="call_text"><img src="{{ asset('images/mail-icon.png') }}"><span class="padding_left_0">demo@gmail.com</span></div>
                </div>
            </div>
        </div>
    </div>
    <!-- contact section end -->
@endsection

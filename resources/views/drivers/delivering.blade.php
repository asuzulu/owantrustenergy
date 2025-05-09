@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : 'layouts.drivers-dashboard'))

@section('content')
    <div class="container" style="margin-top: -6rem;">
        <div class="row tm-content-row tm-mt-big">
            <div class="bg-white tm-block h-100">
                <div class="row">
                    <div class="col">
                        <h3 class="tm-block-title text-center" style="font-weight: 1000;">
                            Deliver Cylinder {{ $paddedId }}
                        </h3>

                        {{-- Only show instructions to Driver --}}
                        @if (Auth::user()->position === 'Driver')
                            <h5 class="tm-block-title text-center">
                                Upload a picture with the customer holding the cylinder
                                or the cylinder at the entrance of the customer’s requested delivery location.
                            </h5>
                        @endif
                    </div>
                </div>

                {{-- Success / Error --}}
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @error('delivery_image')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                {{-- Driver flow: Upload button --}}
                @if (Auth::user()->position === 'Driver')
                    <div class="row">
                        <div class="col text-center mt-4">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#deliverModal">
                                Upload
                            </button>
                        </div>
                    </div>

                    <!-- Upload Modal -->
                    <div class="modal fade" id="deliverModal" tabindex="-1" role="dialog"
                        aria-labelledby="deliverModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form method="POST" action="{{ route('drivers.delivering.store', $paddedId) }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deliverModalLabel">Upload Delivery Photo</h5>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <input type="file" name="delivery_image" id="deliveryImageInput"
                                                accept="image/*" required>
                                        </div>
                                        <div class="form-group text-center">
                                            <img id="imagePreview" src="#" alt="Preview"
                                                style="max-width:100%; display:none;">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success">Submit</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
                <!-- End Upload Modal -->

                {{-- Display the uploaded image after success (all roles) --}}
                @php
                    $delivery = \App\Models\Delivery::where('cylinder', ltrim($paddedId, '0'))->first();
                @endphp

                @if ($delivery && $delivery->image_path)
                    <div class="row mt-4">
                        <div class="col text-center">
                            <h4>Delivery Image:</h4>
                            <img src="{{ asset('storage/' . $delivery->image_path) }}" alt="Delivery Image"
                                class="img-fluid mb-3">
                        </div>
                    </div>
                @endif

                {{-- Manager/Employee approval panel --}}
                @if (in_array(Auth::user()->position, ['Manager', 'Employee']) && $delivery && $delivery->image_path)
                    <div class="row mt-4">
                        <div class="col text-center">
                            <div>
                                @if(is_null($delivery->approval))
                                    {{-- Approve --}}
                                    <form action="{{ route('deliveries.approve', $delivery->id) }}" method="POST"
                                        style="display:inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-success mr-2">
                                            Approve Delivery
                                        </button>
                                    </form>
                                    {{-- Disapprove --}}
                                    <form action="{{ route('deliveries.disapprove', $delivery->id) }}" method="POST"
                                        style="display:inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-warning mr-2">
                                            Disapprove Delivery
                                        </button>
                                    </form>
                                    {{-- Cancel --}}
                                    <a href="{{ route('management.home') }}" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                @else
                                    {{-- After approve/disapprove, show only this link --}}
                                    <a href="{{ route('management.cylinders') }}" class="btn btn-primary">
                                        Return to Cylinders List
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // preview before upload
        document.getElementById('deliveryImageInput')?.addEventListener('change', function(evt) {
            const [file] = this.files;
            if (!file) return;
            const img = document.getElementById('imagePreview');
            img.src = URL.createObjectURL(file);
            img.style.display = 'block';
        });
    </script>
@endpush

@extends(Auth::user()->position === 'Manager' ? 'layouts.management-dashboard' : (Auth::user()->position === 'Employee' ? 'layouts.employee-dashboard' : 'layouts.drivers-dashboard'))

@section('content')
    @php
        // BEFORE any checks that reference $delivery
        $delivery = \App\Models\Delivery::where('cylinder', $paddedId)->first();
    @endphp
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

                {{-- Once approved or disapproved, show status + Review link --}}
                @if (!empty($delivery->approval))
                    <div class="row mt-4">
                        <div class="col text-center">
                            <h4>
                                Delivery
                                @if ($delivery->approval === 'approved')
                                    <span class="text-success">Approved</span>
                                @else
                                    <span class="text-danger">Disapproved</span>
                                @endif
                            </h4>

                            <a href="{{ route('deliveries.review', $delivery->id) }}" class="btn btn-info mt-2">
                                Review Delivery
                            </a>
                        </div>
                    </div>
                @endif

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
                                    <div class="modal-header">…</div>
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
                    // Convert back to integer in PHP and query normally
                    $rawCylinder = (int) ltrim($paddedId, '0');
                    $delivery = \App\Models\Delivery::where('cylinder', $paddedId)->first();
                @endphp

                @if ($delivery && $delivery->image_path)
                    <div class="row mt-4">
                        <div class="col text-center">
                            <h4>Delivery Image:</h4>
                            <img src="{{ asset('storage/' . rawurlencode($delivery->image_path)) }}" alt="Delivery Image"
                                class="img-fluid mb-3">
                        </div>
                    </div>
                @endif

                {{-- Manager/Employee approval panel --}}
                @if (in_array(Auth::user()->position, ['Manager', 'Employee']) && $delivery && $delivery->image_path)
                    <div class="row mt-4">
                        <div class="col text-center">
                            <div>
                                @if (is_null($delivery->approval))
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
                                        <button type="button" class="btn btn-warning mr-2" data-toggle="modal"
                                            data-target="#disapproveModal">
                                            Disapprove Delivery
                                        </button>
                                    </form>

                                    {{-- Cancel --}}
                                    <a href="{{ route('management.home') }}" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                @else
                                    <a href="{{ route('management.cylinders') }}" class="btn btn-primary">
                                        Return to Cylinders List
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
                <!-- Disapprove Delivery Modal -->
                <div class="modal fade" id="disapproveModal" tabindex="-1" role="dialog"
                    aria-labelledby="disapproveModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form id="disapproveForm" method="POST"
                            action="{{ route('deliveries.disapprove', $delivery->id) }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="disapproveModalLabel">Disapprove Delivery</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="disapproveReason">Reason for Disapproval</label>
                                        <textarea class="form-control" id="disapproveReason" name="reason" rows="4"
                                            placeholder="Please explain why you are disapproving this delivery..." required></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" id="submitDisapproveBtn"
                                        class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // === preview before upload ===
        document
            .getElementById('deliveryImageInput')
            ?.addEventListener('change', function(evt) {
                const [file] = this.files;
                if (!file) return;
                const img = document.getElementById('imagePreview');
                img.src = URL.createObjectURL(file);
                img.style.display = 'block';
            });

            // === disapprove-modal validation ===
        $(document).ready(function() {
            $('#disapproveForm').on('submit', function(e) {
                let text = $('#disapproveReason').val().trim();
                // Count words: split on whitespace, filter out any empty strings
                let wordCount = text.split(/\s+/).filter(function(w) {
                    return w.length > 0;
                }).length;

                if (wordCount < 10) {
                    e.preventDefault();
                    alert('Response is too short.');
                    return false;
                }
                // Otherwise, allow form to submit normally (will write "disapproved" in approval column)
                return true;
            });
        });
    </script>
@endpush

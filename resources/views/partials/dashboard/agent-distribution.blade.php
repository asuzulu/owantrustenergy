{{-- ============================================
     SUCCESS & ERROR MODALS (Bootstrap 4/5)
     ============================================ --}}
@if (session('success'))
    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-success">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ session('success') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ session('error') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="bg-white tm-block">
    <h3 class="tm-block-title text-center">Cylinders Distributed to Agents</h3>

    @php
        $agentCylinders = \Illuminate\Support\Facades\DB::table('agent_cylinders_distribution')
        ->where('warehouse', $warehouse->name)
        ->whereNull('pick_up_date')                        // ← only those not yet picked up
        ->orderBy('agent_name')
        ->orderByRaw("
                CASE cylinder_size
                    WHEN 'Small' THEN 1
                    WHEN 'Medium' THEN 2
                    WHEN 'Large' THEN 3
                    WHEN 'Extra Large' THEN 4
                    ELSE 5 END
            ")
            ->paginate(10);
    @endphp

    @if ($agentCylinders->isNotEmpty())
        <form action="{{ route('warehouses.confirmAgentPickup', $warehouse->id) }}" method="POST" novalidate>
            @csrf

            <div class="table-responsive mt-3">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Cylinder #</th>
                            <th>Size</th>
                            <th>Weight</th>
                            <th>Agent</th>
                            {{-- remove Passcode column --}}
                        </tr>
                    </thead>
                    <tbody id="agent-distribution-table-body">
                        @foreach ($agentCylinders as $record)
                            <tr>
                                <td>
                                    <input type="checkbox" name="pickup[]" value="{{ $record->id }}">
                                </td>
                                <td>{{ str_pad($record->cylinder_id, 9, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $record->cylinder_size }}</td>
                                <td>{{ $record->cylinder_weight }}</td>
                                <td>{{ $record->agent_name }}</td>
                                {{-- remove per-row passcode display --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3" id="agent-distribution-pagination">
                {{ $agentCylinders->links('pagination::bootstrap-4') }}
            </div>

            {{-- passcode input at bottom for Managers/Employees --}}
            @unless(Auth::user()->position === 'Agent')
                <div class="form-group row passcode-form">
                    <label for="passcode"
                        class="col-sm-3 col-form-label text-sm-right text-center">
                        Enter Passcode:
                    </label>
                    <div class="col-sm-6 col-md-4 d-flex passcode-controls">
                        <input type="text" name="passcode" id="passcode"
                            class="form-control mr-2 passcode-input" required>
                        <button type="submit" class="btn btn-primary">
                            Confirm
                        </button>
                    </div>
                </div>
            @endunless

        </form>
    @else
        <p class="text-center mt-3">No distributed cylinders at this warehouse.</p>
    @endif
</div>


<style>
    .passcode-form { justify-content: flex-start !important; }
    .passcode-form .passcode-controls {
      display: flex; flex-direction: row; align-items: baseline; justify-content: flex-start;
    }
    .passcode-input { width: 250px; }
    @media (max-width:1080px) {
      .passcode-form { justify-content: center !important; }
      .passcode-form .passcode-controls {
        flex-direction: column !important; align-items: center !important; justify-content: center !important;
      }
      .passcode-input { width: 100% !important; margin-bottom: .5rem; margin-right:0 !important; }
      .passcode-form .passcode-controls button { margin-top:0; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1) Trigger Success Modal if session('success') exists
        @if (session('success'))
            $('#successModal').modal('show');
        @endif

        // 2) Trigger Error Modal if session('error') exists
        @if (session('error'))
            $('#errorModal').modal('show');
        @endif

        // 3) Prevent each row’s checkbox click from triggering row‐link (in case you add row‐onclick later)
        const cylinderCheckboxes = document.querySelectorAll('input[name="pickup[]"]');
        cylinderCheckboxes.forEach(cb => {
            cb.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });

        // 4) (Optional) “Select All” logic if you ever add a <th><input id="select_all_agents"></th>
        const selectAllCheckbox = document.getElementById('select_all_agents');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                cylinderCheckboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        }
    });
</script>

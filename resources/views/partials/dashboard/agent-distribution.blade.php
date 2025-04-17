<div class="bg-white tm-block">
    <h3 class="tm-block-title text-center">Cylinders Distributed to Agents</h3>
    @php
        $agentCylinders = \Illuminate\Support\Facades\DB::table('agent_cylinders_distribution')
            ->where('warehouse', $warehouse->name)
            ->orderBy('agent_name') // Group by agent
            ->orderByRaw(
                "CASE cylinder_size
                WHEN 'Small' THEN 1
                WHEN 'Medium' THEN 2
                WHEN 'Large' THEN 3
                WHEN 'Extra Large' THEN 4
                ELSE 5 END",
            )
            ->paginate(10);
    @endphp
    @if ($agentCylinders->isNotEmpty())
        <form action="{{ route('warehouses.confirmAgentPickup', $warehouse->id) }}" method="POST"
            novalidate>
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
                            <th>Passcode</th>
                        </tr>
                    </thead>
                    <tbody id="agent-distribution-table-body">>
                        @foreach ($agentCylinders as $record)
                            <tr>
                                <td><input type="checkbox" name="pickup[]" value="{{ $record->id }}"></td>
                                <td>{{ str_pad($record->cylinder_id, 9, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $record->cylinder_size }}</td>
                                <td>{{ $record->cylinder_weight }}</td>
                                <td>{{ $record->agent_name }}</td>
                                <td>{{ $record->passcode ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3" id="agent-distribution-pagination">
                {{ $agentCylinders->links('pagination::bootstrap-4') }}
            </div>

            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">Confirm Agent Pickup</button>
            </div>
        </form>
    @else
        <p class="text-center mt-3">No distributed cylinders at this warehouse.</p>
    @endif
</div>

@extends('adminlayouts.vertical', ['title' => 'Referral Incentive Configuration'])

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Referral Incentive Configuration</h4>
            <p class="text-muted small mb-0">Configure the 5-Stage Referral Incentive cycle percentages (Variant A: 10% → 12% → 15% → 18% → 20% → RESET)</p>
        </div>
        <a href="{{ route('admin.self_dealers.index') }}" class="btn btn-outline-secondary btn-sm">← Self Dealers</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        {{-- Config Form --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white fw-semibold">⚙️ Stage Configuration</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.referral.config.update') }}">
                        @csrf

                        {{-- Activation Credit % --}}
                        <div class="mb-4 p-3 bg-light rounded">
                            <label class="fw-semibold d-block mb-1">Self Dealer Activation Points %</label>
                            <p class="text-muted small mb-2">One-time benefit when a customer makes their first eligible own purchase.</p>
                            <div class="input-group" style="max-width:200px;">
                                <input type="number" name="activation_credit_percentage"
                                       class="form-control"
                                       value="{{ old('activation_credit_percentage', $stages->first()->activation_credit_percentage ?? 20) }}"
                                       step="0.01" min="1" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        {{-- 5 Stage Rates --}}
                        <label class="fw-semibold mb-3 d-block">5-Stage Referral Incentive Cycle</label>

                        @foreach($stages as $stage)
                        <input type="hidden" name="stages[{{ $loop->index }}][stage_number]" value="{{ $stage->stage_number }}">
                        <div class="row g-2 align-items-center mb-3 p-2 border rounded">
                            <div class="col-md-3">
                                <span class="fw-semibold">Stage {{ $stage->stage_number }}</span>
                                @if($stage->stage_number == 5)
                                    <span class="badge bg-warning text-dark ms-1">→ RESET</span>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <div class="input-group input-group-sm">
                                    <input type="number"
                                           name="stages[{{ $loop->index }}][incentive_percentage]"
                                           class="form-control"
                                           value="{{ old("stages.{$loop->index}.incentive_percentage", $stage->incentive_percentage) }}"
                                           step="0.01" min="0" max="100" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <small class="text-muted">
                                    @php
                                        $desc = ['10%', '12%', '15%', '18%', '20%'];
                                        echo 'Default: ' . ($desc[$stage->stage_number - 1] ?? '—');
                                    @endphp
                                </small>
                            </div>
                        </div>
                        @endforeach

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary fw-semibold">
                                💾 Save Configuration
                            </button>
                            <span class="text-muted small ms-3">Changes apply to NEW referral transactions only. Existing transactions retain their original rates.</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Info Panel --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light fw-semibold">📖 How the Cycle Works</div>
                <div class="card-body">
                    <p class="small text-muted mb-3">The 5-stage cycle runs independently per product category per dealer:</p>
                    <div class="d-flex justify-content-between align-items-center p-2 mb-1 rounded" style="background:#e8f5e9;">
                        <span class="small fw-semibold">Stage 1</span>
                        <span class="badge bg-success">10%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 mb-1 rounded" style="background:#e3f2fd;">
                        <span class="small fw-semibold">Stage 2</span>
                        <span class="badge bg-primary">12%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 mb-1 rounded" style="background:#fff3e0;">
                        <span class="small fw-semibold">Stage 3</span>
                        <span class="badge bg-warning text-dark">15%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 mb-1 rounded" style="background:#fce4ec;">
                        <span class="small fw-semibold">Stage 4</span>
                        <span class="badge bg-danger">18%</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded" style="background:#ede7f6;">
                        <span class="small fw-semibold">Stage 5</span>
                        <span class="badge bg-purple" style="background:#7b1fa2;">20%</span>
                    </div>
                    <div class="text-center py-1 rounded" style="background:#f5f5f5;">
                        <small class="text-muted fw-semibold">↓ RESET → Back to Stage 1</small>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light fw-semibold">⚠️ Important Notes</div>
                <div class="card-body">
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Changes apply to <strong>future referral transactions only</strong></li>
                        <li class="mb-2">Existing pending/available points retain the rate at which they were created</li>
                        <li class="mb-2">Each product category runs its own independent cycle per dealer</li>
                        <li class="mb-2">After Stage 5 completes, the next referral in that category resets to Stage 1</li>
                        <li>Activation % is applied once per dealer on their first eligible own purchase</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

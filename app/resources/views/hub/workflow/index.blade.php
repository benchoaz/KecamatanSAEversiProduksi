@extends('layouts.hub')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h3 mb-1" style="font-weight: 700;">Workflows & Automation</h1>
            <p class="text-muted mb-0">Powered by n8n. Manage cross-district automated tasks.</p>
        </div>
    </div>

    <div class="row">
        <!-- n8n Engine Status -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header border-0 pb-0">
                    <h6 class="text-muted small fw-bold text-uppercase mb-0">Automation Engine</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4 pb-4 border-bottom">
                        <div class="flex-shrink-0 bg-primary-light text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-robot fa-2x"></i>
                        </div>
                        <div class="ms-4">
                            <div class="fw-bold fs-5">n8n Central Instance</div>
                            <div class="mt-1">
                                @if($is_online)
                                    <span class="badge-status-active">online</span>
                                @else
                                    <span class="badge bg-light text-muted border-0 rounded-pill px-3 py-1" style="font-size: 11px;">offline</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <p class="text-muted small mb-4">The n8n editor allows you to create complex automations between 24 district databases and 3rd party services like WhatsApp, Google Drive, or Email.</p>
                        <a href="{{ $n8n_url }}" target="_blank" class="btn btn-primary w-100 p-3">
                            <i class="fas fa-external-link-alt me-2"></i> Open n8n Editor
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Examples/Templates -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    Active Automation Use-Cases
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item p-4 border-0 border-bottom">
                            <div class="d-flex align-items-start">
                                <div class="bg-success-light text-success rounded-circle d-flex align-items-center justify-content-center p-2 me-3" style="width: 32px; height: 32px;">
                                    <i class="fab fa-whatsapp small"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 15px;">WhatsApp Broadcast</div>
                                    <p class="text-muted small mb-0">Notify residents automatically when their public service request status changes.</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item p-4 border-0 border-bottom">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary-light text-primary rounded-circle d-flex align-items-center justify-content-center p-2 me-3" style="width: 32px; height: 32px;">
                                    <i class="fas fa-database small"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 15px;">Data Synchronization</div>
                                    <p class="text-muted small mb-0">Aggregate UMKM and Village Personnel data from 24 districts every midnight.</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item p-4 border-0">
                            <div class="d-flex align-items-start">
                                <div class="bg-info-light text-info rounded-circle d-flex align-items-center justify-content-center p-2 me-3" style="width: 32px; height: 32px;">
                                    <i class="fas fa-cloud-upload-alt small"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="font-size: 15px;">Cloud Backups</div>
                                    <p class="text-muted small mb-0">Schedule automated off-site backups for the entire Hub infrastructure.</p>
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

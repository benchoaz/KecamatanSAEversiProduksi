{{-- Partial: tree-nodes.blade.php (rekursif) --}}
@foreach($nodes as $node)
<div class="tree-node-wrapper ms-{{ $depth > 0 ? 4 : 0 }} mb-2" style="{{ $depth > 0 ? 'margin-left: 28px' : '' }}">
    <div class="node-card {{ $node->is_leaf ? 'leaf' : '' }}"
         data-node-id="{{ $node->id }}"
         data-node-name="{{ $node->name }}"
         data-is-leaf="{{ $node->is_leaf ? '1' : '0' }}">

        {{-- Icon --}}
        <div class="node-icon">
            <i class="fas {{ $node->ikon ?? ($node->is_leaf ? 'fa-file-alt' : 'fa-folder') }}"></i>
        </div>

        {{-- Label --}}
        <div class="flex-grow-1 overflow-hidden">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold small text-slate-700 text-truncate">{{ $node->name }}</span>
                @if($node->is_leaf)
                    <span class="badge bg-emerald-100 text-emerald-700 flex-shrink-0" style="font-size:9px;padding:2px 7px;border-radius:20px;">🍃 Leaf</span>
                @endif
                @if(!$node->is_active)
                    <span class="badge bg-slate-100 text-slate-400 flex-shrink-0" style="font-size:9px;padding:2px 7px;border-radius:20px;">Nonaktif</span>
                @endif
            </div>
            @if($node->is_leaf)
                <div class="text-slate-400 small mt-0" style="font-size:11px;">
                    {{ $node->requirements_count ?? 0 }} syarat berkas
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="node-actions">
            @if(!$node->is_leaf)
            <button class="btn btn-xs btn-outline-primary rounded-2 add-node-btn"
                    data-depth="{{ $depth + 1 }}"
                    data-parent="{{ $node->id }}"
                    data-bs-toggle="modal" data-bs-target="#addNodeModal"
                    title="Tambah sub-node"
                    style="font-size:10px; padding:2px 8px;">
                <i class="fas fa-plus"></i>
            </button>
            @endif
            
            <button class="btn btn-xs btn-outline-info rounded-2 edit-node-btn"
                    data-node-id="{{ $node->id }}"
                    data-node-name="{{ $node->name }}"
                    data-node-desc="{{ $node->description }}"
                    data-node-ikon="{{ $node->ikon }}"
                    data-node-urutan="{{ $node->urutan }}"
                    data-node-req-text="{{ $node->requirement_text }}"
                    data-is-leaf="{{ $node->is_leaf }}"
                    data-is-active="{{ $node->is_active }}"
                    data-show-identity="{{ $node->show_identity_form }}"
                    data-bs-toggle="modal" data-bs-target="#addNodeModal"
                    title="Edit node" style="font-size:10px; padding:2px 8px;">
                <i class="fas fa-pencil-alt"></i>
            </button>

            <form action="{{ route('kecamatan.pelayanan.layanan.nodes.destroy', $node->id) }}" method="POST"
                  onsubmit="return confirm('Hapus node ini beserta semua sub-node di dalamnya?')"
                  style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-xs btn-outline-danger rounded-2"
                        title="Hapus node" style="font-size:10px; padding:2px 8px;">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Rekursi untuk child nodes --}}
    @if($node->children && $node->children->isNotEmpty())
        @include('kecamatan.pelayanan.layanan.partials.tree-nodes', [
            'nodes' => $node->children,
            'depth' => $depth + 1
        ])
    @endif
</div>
@endforeach

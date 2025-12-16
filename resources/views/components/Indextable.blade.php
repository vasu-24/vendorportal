
@props([
    'title' => 'Items',
    'apiUrl' => '',
    'columns' => [],
    'filters' => [],
    'createRoute' => null,
    'createLabel' => 'Create New',
    'editRoute' => null,
    'viewModal' => true,
    'searchPlaceholder' => 'Search...',
    'perPage' => 10,
])

<style>
    /* Soft Badge Colors */
    .badge-active { background-color: #d4edda; color: #155724; font-weight: 500; }
    .badge-draft { background-color: #e9ecef; color: #495057; font-weight: 500; }
    .badge-pending { background-color: #fff3cd; color: #856404; font-weight: 500; }
    .badge-expired { background-color: #f8d7da; color: #721c24; font-weight: 500; }
    .badge-signed { background-color: #cce5ff; color: #004085; font-weight: 500; }
    .badge-approved { background-color: #d4edda; color: #155724; font-weight: 500; }
    .badge-rejected { background-color: #f8d7da; color: #721c24; font-weight: 500; }
    .badge-paid { background-color: #d1ecf1; color: #0c5460; font-weight: 500; }
    .badge-submitted { background-color: #e2e3f1; color: #383d6e; font-weight: 500; }
    .badge-inactive { background-color: #e9ecef; color: #6c757d; font-weight: 500; }
    .badge-under-review { background-color: #fff3cd; color: #856404; font-weight: 500; }
    .badge-terminated { background-color: #f8d7da; color: #721c24; font-weight: 500; }
    
    .index-table th { 
        font-size: 11px; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        border-bottom: 1px solid #e9ecef; 
    }
    .index-table td { 
        vertical-align: middle; 
        font-size: 13px; 
        color: #495057; 
    }
    .index-table tbody tr:hover { 
        background-color: #f8f9fa; 
    }
    .index-table .btn-group-sm .btn { 
        padding: 0.2rem 0.5rem; 
    }
</style>

<div class="container-fluid py-3">

    {{-- Alert --}}
    <div id="alertContainer"></div>

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-semibold text-dark">{{ $title }}</h5>
        @if($createRoute)
        <a href="{{ $createRoute }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>{{ $createLabel }}
        </a>
        @endif
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-3" style="border: none; border-radius: 8px;">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                
                {{-- Search --}}
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0" placeholder="{{ $searchPlaceholder }}">
                    </div>
                </div>

                {{-- Filters --}}
                @foreach($filters as $filter)
                <div class="col-md-2">
                    <select class="form-select form-select-sm filter-select" data-key="{{ $filter['key'] }}" id="filter_{{ $filter['key'] }}">
                        <option value="">{{ $filter['label'] ?? 'All' }}</option>
                        @if(isset($filter['options']))
                            @foreach($filter['options'] as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @endforeach

                {{-- Total --}}
                <div class="col text-end">
                    <span class="text-muted small">Total: <strong id="totalCount">0</strong></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm" style="border: none; border-radius: 8px;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 index-table">
                <thead>
                    <tr class="bg-light">
                        @foreach($columns as $col)
                        <th class="text-muted fw-semibold">{{ $col['label'] }}</th>
                        @endforeach
                        <th class="text-center text-muted fw-semibold" style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="{{ count($columns) + 1 }}" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="ms-2 text-muted">Loading...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-2">
            <small class="text-muted" id="pageInfo">Showing 0 of 0</small>
            <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
        </div>
    </div>
</div>

{{-- View Modal --}}
@if($viewModal)
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border: none; border-radius: 8px;">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold">Details</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalBody"></div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
(function() {
    const API = '{{ $apiUrl }}';
    const COLS = @json($columns);
    const FILTERS_CONFIG = @json($filters);
    const EDIT_ROUTE = '{{ $editRoute }}';
    const VIEW_MODAL = {{ $viewModal ? 'true' : 'false' }};
    const PER_PAGE = {{ $perPage }};

    let page = 1, search = '', filters = {};

    // ========== INIT ==========
    document.addEventListener('DOMContentLoaded', () => {
        loadFilters();
        load();
        bindEvents();
    });

    // ========== LOAD FILTER OPTIONS ==========
    function loadFilters() {
        FILTERS_CONFIG.forEach(f => {
            if (f.api) {
                axios.get(f.api).then(r => {
                    if (r.data.success) {
                        const el = document.getElementById('filter_' + f.key);
                        r.data.data.forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.id || item.value;
                            opt.textContent = item.name || item.label || item.vendor_name || item.company_name;
                            el.appendChild(opt);
                        });
                    }
                });
            }
        });
    }

    // ========== LOAD DATA ==========
    function load() {
        const params = new URLSearchParams({ page, per_page: PER_PAGE });
        if (search) params.append('search', search);
        Object.keys(filters).forEach(k => filters[k] && params.append(k, filters[k]));

        document.getElementById('tableBody').innerHTML = `
            <tr><td colspan="${COLS.length + 1}" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <span class="ms-2 text-muted">Loading...</span>
            </td></tr>`;

        axios.get(`${API}?${params}`)
            .then(r => r.data.success && render(r.data.data))
            .catch(() => {
                document.getElementById('tableBody').innerHTML = `
                    <tr><td colspan="${COLS.length + 1}" class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>Failed to load data
                    </td></tr>`;
            });
    }

    // ========== RENDER ==========
    function render(res) {
        const items = res.data || res;
        const tbody = document.getElementById('tableBody');

        if (!items?.length) {
            tbody.innerHTML = `<tr><td colspan="${COLS.length + 1}" class="text-center py-4 text-muted">No data found</td></tr>`;
            document.getElementById('totalCount').textContent = '0';
            document.getElementById('pageInfo').textContent = 'Showing 0 of 0';
            document.getElementById('pagination').innerHTML = '';
            return;
        }

        tbody.innerHTML = items.map(item => `
            <tr>
                ${COLS.map(c => `<td>${fmt(get(item, c.key), c.format, item)}</td>`).join('')}
                <td class="text-center">${actions(item)}</td>
            </tr>
        `).join('');

        if (res.total !== undefined) {
            document.getElementById('totalCount').textContent = res.total;
            document.getElementById('pageInfo').textContent = `Showing ${res.from || 0}-${res.to || 0} of ${res.total}`;
            paginate(res);
        }
    }

    // ========== GET NESTED VALUE ==========
    function get(obj, key) {
        return key.split('.').reduce((o, k) => (o || {})[k], obj);
    }

    // ========== FORMAT VALUE ==========
    function fmt(v, f, item) {
        if (v == null) return '-';
        switch (f) {
            case 'currency': 
                return '₹' + parseFloat(v).toLocaleString('en-IN', { minimumFractionDigits: 2 });
            case 'date': 
                return new Date(v).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
            case 'datetime': 
                return new Date(v).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            case 'badge': 
                return badge(v);
            case 'boolean': 
                return v ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>';
            case 'link': 
                return `<a href="#" onclick="viewItem(${item.id}); return false;" class="text-decoration-none" style="color: #495057;">${v}</a>`;
            default: 
                return v;
        }
    }

    // ========== BADGE (Soft Colors) ==========
    function badge(s) {
        if (!s) return '-';
        const status = s.toLowerCase().replace(/_/g, '-');
        const label = s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g, ' ');
        return `<span class="badge badge-${status}">${label}</span>`;
    }

    // ========== ACTIONS (View + Edit only) ==========
    function actions(item) {
        let h = '<div class="btn-group btn-group-sm">';
        if (VIEW_MODAL) {
            h += `<button class="btn btn-outline-secondary" onclick="viewItem(${item.id})" title="View"><i class="bi bi-eye"></i></button>`;
        }
        if (EDIT_ROUTE) {
            h += `<a href="${EDIT_ROUTE.replace('{id}', item.id)}" class="btn btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>`;
        }
        return h + '</div>';
    }

    // ========== PAGINATION ==========
    function paginate(d) {
        const el = document.getElementById('pagination');
        if (!d.last_page || d.last_page <= 1) { el.innerHTML = ''; return; }

        let h = `<li class="page-item ${d.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goPage(${d.current_page - 1}); return false;"><i class="bi bi-chevron-left"></i></a>
        </li>`;
        
        for (let i = 1; i <= d.last_page; i++) {
            if (i === 1 || i === d.last_page || (i >= d.current_page - 1 && i <= d.current_page + 1)) {
                h += `<li class="page-item ${i === d.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goPage(${i}); return false;">${i}</a>
                </li>`;
            } else if (i === d.current_page - 2 || i === d.current_page + 2) {
                h += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        h += `<li class="page-item ${d.current_page === d.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goPage(${d.current_page + 1}); return false;"><i class="bi bi-chevron-right"></i></a>
        </li>`;
        el.innerHTML = h;
    }

    // ========== EVENTS ==========
    function bindEvents() {
        let t;
        document.getElementById('searchInput')?.addEventListener('keyup', function() {
            clearTimeout(t);
            t = setTimeout(() => { search = this.value; page = 1; load(); }, 300);
        });

        document.querySelectorAll('.filter-select').forEach(el => {
            el.addEventListener('change', function() {
                filters[this.dataset.key] = this.value;
                page = 1;
                load();
            });
        });
    }

    // ========== VIEW ITEM ==========
    window.viewItem = function(id) {
        const modal = new bootstrap.Modal('#viewModal');
        document.getElementById('viewModalBody').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <span class="ms-2 text-muted">Loading...</span>
            </div>`;
        modal.show();

        axios.get(`${API}/${id}`)
            .then(r => {
                if (r.data.success) {
                    const item = r.data.data;
                    let h = '<table class="table table-sm table-borderless mb-0">';
                    COLS.forEach(c => {
                        h += `<tr>
                            <th class="text-muted" style="width: 140px;">${c.label}</th>
                            <td>${fmt(get(item, c.key), c.format, item)}</td>
                        </tr>`;
                    });
                    h += '</table>';
                    document.getElementById('viewModalBody').innerHTML = h;
                }
            })
            .catch(() => {
                document.getElementById('viewModalBody').innerHTML = `
                    <p class="text-danger text-center mb-0">Failed to load details</p>`;
            });
    };

    // ========== GO TO PAGE ==========
    window.goPage = function(p) { page = p; load(); };

    // ========== REFRESH TABLE ==========
    window.refreshTable = load;

    // ========== SHOW ALERT ==========
    window.showTableAlert = function(type, msg) {
        const icons = { success: 'check-circle', danger: 'exclamation-circle', warning: 'exclamation-triangle' };
        document.getElementById('alertContainer').innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show py-2" role="alert">
                <i class="bi bi-${icons[type]} me-2"></i>${msg}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        setTimeout(() => document.querySelector('#alertContainer .alert')?.remove(), 3000);
    };

})();
</script>
@endpush
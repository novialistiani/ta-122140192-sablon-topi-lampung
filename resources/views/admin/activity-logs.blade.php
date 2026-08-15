<x-admin-layout title="Activity Logs">
    @push('styles')
    @vite('resources/css/admin/user-management.css')
    @endpush

    <div class="user-management-wrapper">
        <!-- Header -->
        <div class="table-card">
            <div class="table-card-header">
                <h2 class="table-card-title">Activity Logs</h2>
                <div style="display: flex; gap: 12px;">
                    <select class="form-select" style="width: auto;" onchange="filterByAction(this.value)">
                        <option value="">All Actions</option>
                        <option value="login">Login</option>
                        <option value="logout">Logout</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                    </select>
                    <a href="{{ route('admin.activity-logs.export') }}" class="btn-add-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>Export Excel</span>
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr class="table-header-row">
                            <th class="table-header-cell">Timestamp</th>
                            <th class="table-header-cell">Admin</th>
                            <th class="table-header-cell">Action</th>
                            <th class="table-header-cell">Description</th>
                            <th class="table-header-cell">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="table-body">
                        @forelse($logs as $log)
                        <tr class="table-row">
                            <td class="table-cell">
                                <span class="user-name">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="user-email">
                                    @if($log->user_type === 'App\Models\Admin')
                                        {{ App\Models\Admin::find($log->user_id)->name ?? 'Unknown Admin' }}
                                    @elseif($log->user_type === 'App\Models\User')
                                        {{ App\Models\User::find($log->user_id)->name ?? 'Unknown User' }}
                                    @else
                                        System
                                    @endif
                                </span>
                            </td>
                            <td class="table-cell">
                                <span class="badge 
                                    {{ $log->action === 'login' ? 'badge-green' : '' }}
                                    {{ $log->action === 'logout' ? 'badge-gray' : '' }}
                                    {{ $log->action === 'create' ? 'badge-blue' : '' }}
                                    {{ $log->action === 'update' ? 'badge-purple' : '' }}
                                    {{ $log->action === 'delete' ? 'badge-red' : '' }}
                                ">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="table-cell">
                                <span class="user-phone">{{ $log->description }}</span>
                            </td>
                            <td class="table-cell">
                                <span class="user-phone">{{ $log->ip_address ?? '-' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="table-cell table-empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-icon">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <p class="empty-text">No activity logs found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <span class="pagination-highlight">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</span> 
                    of <span class="pagination-highlight">{{ $logs->total() }}</span>
                </div>
                <div class="pagination-controls">
                    @if($logs->onFirstPage())
                        <button class="pagination-btn pagination-btn-disabled" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            <span>Previous</span>
                        </button>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="pagination-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            <span>Previous</span>
                        </a>
                    @endif

                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="pagination-btn">
                            <span>Next</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    @else
                        <button class="pagination-btn pagination-btn-disabled" disabled>
                            <span>Next</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
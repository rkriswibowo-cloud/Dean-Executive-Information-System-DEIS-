/**
 * DEIS - Interactive Application Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Toggle Logic (Desktop collapse & Mobile offcanvas drawer)
    const sidebar = document.getElementById('miniSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggleButtons = document.querySelectorAll('.sidebar-toggle, #sidebarToggleBtn, [data-sidebar-toggle]');
    const closeButtons = document.querySelectorAll('.sidebar-close-btn');

    // Initialize sidebar expanded state on desktop
    if (window.innerWidth >= 992) {
        if (localStorage.getItem('sidebarExpanded') === 'false') {
            document.documentElement.classList.add('collapsed');
            document.documentElement.classList.remove('expanded');
        } else {
            document.documentElement.classList.add('expanded');
            document.documentElement.classList.remove('collapsed');
        }
    }

    function openMobileSidebar() {
        if (sidebar) {
            sidebar.classList.add('mobile-show');
            if (backdrop) backdrop.classList.remove('d-none');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }

    function closeMobileSidebar() {
        if (sidebar) {
            sidebar.classList.remove('mobile-show');
            if (backdrop) backdrop.classList.add('d-none');
            document.body.style.overflow = '';
        }
    }

    function toggleDesktopSidebar() {
        if (document.documentElement.classList.contains('collapsed')) {
            document.documentElement.classList.remove('collapsed');
            document.documentElement.classList.add('expanded');
            localStorage.setItem('sidebarExpanded', 'true');
        } else {
            document.documentElement.classList.add('collapsed');
            document.documentElement.classList.remove('expanded');
            localStorage.setItem('sidebarExpanded', 'false');
        }
    }

    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (window.innerWidth < 992) {
                if (sidebar && sidebar.classList.contains('mobile-show')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else {
                toggleDesktopSidebar();
            }
        });
    });

    closeButtons.forEach(btn => {
        btn.addEventListener('click', closeMobileSidebar);
    });

    if (backdrop) {
        backdrop.addEventListener('click', closeMobileSidebar);
    }

    // Auto close mobile sidebar when navigating on mobile
    if (sidebar) {
        sidebar.querySelectorAll('.nav-link, .sidebar-subnav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    closeMobileSidebar();
                }
            });
        });

        // If clicking accordion toggle while sidebar is collapsed, auto-expand sidebar
        sidebar.querySelectorAll('.sidebar-accordion-toggle').forEach(toggle => {
            toggle.addEventListener('click', function () {
                if (window.innerWidth >= 992 && document.documentElement.classList.contains('collapsed')) {
                    document.documentElement.classList.remove('collapsed');
                    document.documentElement.classList.add('expanded');
                    localStorage.setItem('sidebarExpanded', 'true');
                }
            });
        });
    }

    // 2. Initialize Bootstrap Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // 3. Global Keyboard Shortcut for Command Palette (⌘K / Ctrl+K) & Autofocus
    const searchModalEl = document.getElementById('searchModal');
    if (searchModalEl) {
        searchModalEl.addEventListener('shown.bs.modal', function () {
            const input = document.getElementById('globalSearchInput');
            if (input) input.focus();
        });
    }

    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            if (searchModalEl) {
                const searchModal = bootstrap.Modal.getOrCreateInstance(searchModalEl);
                searchModal.show();
            }
        }
    });

    // 4. Live Global Search
    const searchInput = document.getElementById('globalSearchInput');
    const searchResults = document.getElementById('globalSearchResults');

    if (searchInput && searchResults) {
        let debounceTimer;
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.innerHTML = '<div class="text-center py-4 text-muted"><i class="ti ti-search fs-2 mb-2 d-block"></i>Ketik minimal 2 karakter untuk mencari...</div>';
                return;
            }

            debounceTimer = setTimeout(() => {
                searchResults.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm" role="status"></div> Mencari data fakultas...</div>';
                
                const baseUrl = document.body.getAttribute('data-base-url') || '';
                fetch(`${baseUrl}/search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.results || data.results.length === 0) {
                            searchResults.innerHTML = '<div class="text-center py-4 text-muted"><i class="ti ti-folder-x fs-2 mb-2 d-block"></i>Tidak ada hasil yang cocok dengan pencarian Anda.</div>';
                            return;
                        }

                        let html = '<div class="list-group list-group-flush">';
                        data.results.forEach(item => {
                            const fullUrl = item.url.startsWith('http') ? item.url : `${baseUrl}/${item.url}`;
                            html += `
                                <a href="${fullUrl}" class="list-group-item list-group-item-action py-3 px-3 border-0 rounded-2 mb-1">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">${item.title}</h6>
                                            <small class="text-muted">${item.subtitle || ''}</small>
                                        </div>
                                        <span class="badge bg-light text-dark border">${item.category}</span>
                                    </div>
                                </a>
                            `;
                        });
                        html += '</div>';
                        searchResults.innerHTML = html;
                    })
                    .catch(err => {
                        searchResults.innerHTML = '<div class="text-center py-4 text-danger">Terjadi kesalahan saat melakukan pencarian.</div>';
                    });
            }, 250);
        });
    }

    // 5. AI Assistant Chat Interactions
    const aiForm = document.getElementById('aiChatForm');
    const aiInput = document.getElementById('aiChatInput');
    const aiMessages = document.getElementById('aiChatMessages');

    if (aiForm && aiInput && aiMessages) {
        aiForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const message = aiInput.value.trim();
            if (!message) return;

            // Append User Message
            const userMsgEl = document.createElement('div');
            userMsgEl.className = 'ai-message user';
            userMsgEl.textContent = message;
            aiMessages.appendChild(userMsgEl);
            aiInput.value = '';
            aiMessages.scrollTop = aiMessages.scrollHeight;

            // Loading indicator
            const botLoadingEl = document.createElement('div');
            botLoadingEl.className = 'ai-message bot text-muted';
            botLoadingEl.innerHTML = '<span class="spinner-grow spinner-grow-sm me-1"></span> Menganalisis basis data eksekutif...';
            aiMessages.appendChild(botLoadingEl);
            aiMessages.scrollTop = aiMessages.scrollHeight;

            const baseUrl = document.body.getAttribute('data-base-url') || '';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const formData = new FormData();
            formData.append('message', message);
            formData.append('csrf_token', csrfToken);

            fetch(`${baseUrl}/ai/chat`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                botLoadingEl.remove();
                const botMsgEl = document.createElement('div');
                botMsgEl.className = 'ai-message bot';
                
                // Parse simple markdown bold/bullet
                let formatted = (data.response || '')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/\n/g, '<br>');
                
                botMsgEl.innerHTML = formatted;
                aiMessages.appendChild(botMsgEl);
                aiMessages.scrollTop = aiMessages.scrollHeight;
            })
            .catch(() => {
                botLoadingEl.remove();
                const errorEl = document.createElement('div');
                errorEl.className = 'ai-message bot text-danger';
                errorEl.textContent = 'Gagal menghubungi asisten AI. Silakan coba sesaat lagi.';
                aiMessages.appendChild(errorEl);
                aiMessages.scrollTop = aiMessages.scrollHeight;
            });
        });
    }

    // ========================================================
    // 6. Universal Responsive Table Pagination Engine
    // ========================================================
    function initTablePagination() {
        const tables = document.querySelectorAll('table.table:not([data-no-paginate])');
        tables.forEach((table, tableIndex) => {
            // Check if inside print view or specifically excluded
            if (table.closest('.print-area, .d-print-block, .no-pagination')) return;

            const tbody = table.querySelector('tbody');
            if (!tbody) return;

            // Collect all normal data rows (skip colspan empty rows)
            const allRows = Array.from(tbody.querySelectorAll('tr')).filter(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length === 1 && tds[0].hasAttribute('colspan')) return false;
                return true;
            });

            if (allRows.length === 0) return;

            // Prevent duplicate initialization
            if (table.dataset.paginated === 'true') return;
            table.dataset.paginated = 'true';

            let pageSize = parseInt(table.getAttribute('data-page-size') || '10', 10);
            let currentPage = 1;

            // Build Pagination Wrapper Container
            const paginationWrapper = document.createElement('div');
            paginationWrapper.className = 'table-pagination-wrapper';
            paginationWrapper.id = `tablePagination_${tableIndex}`;

            paginationWrapper.innerHTML = `
                <div class="table-pagination-info">
                    <span>Menampilkan <strong class="page-start text-dark">1</strong> - <strong class="page-end text-dark">10</strong> dari <strong class="page-total text-dark">${allRows.length}</strong> data</span>
                    <div class="table-page-size-select">
                        <label for="pageSizeSelect_${tableIndex}" class="mb-0 text-muted">Tampilkan:</label>
                        <select class="form-select form-select-sm page-size-select" id="pageSizeSelect_${tableIndex}">
                            <option value="5" ${pageSize === 5 ? 'selected' : ''}>5</option>
                            <option value="10" ${pageSize === 10 ? 'selected' : ''}>10</option>
                            <option value="25" ${pageSize === 25 ? 'selected' : ''}>25</option>
                            <option value="50" ${pageSize === 50 ? 'selected' : ''}>50</option>
                            <option value="all">Semua</option>
                        </select>
                    </div>
                </div>
                <nav aria-label="Navigasi Halaman Tabel">
                    <ul class="deis-pagination pagination-nav"></ul>
                </nav>
            `;

            // Insert pagination wrapper right after the table's container
            const responsiveParent = table.closest('.table-responsive');
            if (responsiveParent) {
                responsiveParent.parentNode.insertBefore(paginationWrapper, responsiveParent.nextSibling);
            } else {
                table.parentNode.insertBefore(paginationWrapper, table.nextSibling);
            }

            const startEl = paginationWrapper.querySelector('.page-start');
            const endEl = paginationWrapper.querySelector('.page-end');
            const totalEl = paginationWrapper.querySelector('.page-total');
            const selectEl = paginationWrapper.querySelector('.page-size-select');
            const navEl = paginationWrapper.querySelector('.pagination-nav');

            function render() {
                const total = allRows.length;
                totalEl.textContent = total;

                if (total === 0) {
                    startEl.textContent = '0';
                    endEl.textContent = '0';
                    navEl.innerHTML = '';
                    return;
                }

                const actualPageSize = (pageSize === 'all' || pageSize >= total) ? total : pageSize;
                const totalPages = Math.ceil(total / actualPageSize);

                if (currentPage > totalPages) currentPage = totalPages;
                if (currentPage < 1) currentPage = 1;

                const startIdx = (currentPage - 1) * actualPageSize;
                const endIdx = Math.min(startIdx + actualPageSize, total);

                startEl.textContent = startIdx + 1;
                endEl.textContent = endIdx;

                // Slice row visibility
                allRows.forEach((row, idx) => {
                    if (idx >= startIdx && idx < endIdx) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Build pagination buttons
                navEl.innerHTML = '';
                if (totalPages <= 1) return; // Hide navigation buttons if only 1 page

                // 1. First button
                const firstLi = document.createElement('li');
                firstLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                firstLi.innerHTML = `<a class="page-link" href="javascript:void(0);" title="Halaman Pertama"><i class="ti ti-chevrons-left"></i></a>`;
                firstLi.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage !== 1) {
                        currentPage = 1;
                        render();
                    }
                });
                navEl.appendChild(firstLi);

                // 2. Previous button
                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link" href="javascript:void(0);" title="Sebelumnya"><i class="ti ti-chevron-left"></i></a>`;
                prevLi.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        render();
                    }
                });
                navEl.appendChild(prevLi);

                // 3. Numbered pages with sliding window
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, currentPage + 2);

                if (currentPage <= 3) {
                    endPage = Math.min(totalPages, 5);
                }
                if (currentPage >= totalPages - 2) {
                    startPage = Math.max(1, totalPages - 4);
                }

                if (startPage > 1) {
                    const p1 = document.createElement('li');
                    p1.className = 'page-item';
                    p1.innerHTML = `<a class="page-link" href="javascript:void(0);">1</a>`;
                    p1.addEventListener('click', (e) => { e.preventDefault(); currentPage = 1; render(); });
                    navEl.appendChild(p1);

                    if (startPage > 2) {
                        const dots = document.createElement('li');
                        dots.className = 'page-item disabled';
                        dots.innerHTML = `<span class="page-link">...</span>`;
                        navEl.appendChild(dots);
                    }
                }

                for (let p = startPage; p <= endPage; p++) {
                    const pLi = document.createElement('li');
                    pLi.className = `page-item ${p === currentPage ? 'active' : ''}`;
                    pLi.innerHTML = `<a class="page-link" href="javascript:void(0);">${p}</a>`;
                    const targetP = p;
                    pLi.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (currentPage !== targetP) {
                            currentPage = targetP;
                            render();
                        }
                    });
                    navEl.appendChild(pLi);
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const dots = document.createElement('li');
                        dots.className = 'page-item disabled';
                        dots.innerHTML = `<span class="page-link">...</span>`;
                        navEl.appendChild(dots);
                    }

                    const pLast = document.createElement('li');
                    pLast.className = 'page-item';
                    pLast.innerHTML = `<a class="page-link" href="javascript:void(0);">${totalPages}</a>`;
                    pLast.addEventListener('click', (e) => { e.preventDefault(); currentPage = totalPages; render(); });
                    navEl.appendChild(pLast);
                }

                // 4. Next button
                const nextLi = document.createElement('li');
                nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link" href="javascript:void(0);" title="Berikutnya"><i class="ti ti-chevron-right"></i></a>`;
                nextLi.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        render();
                    }
                });
                navEl.appendChild(nextLi);

                // 5. Last button
                const lastLi = document.createElement('li');
                lastLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                lastLi.innerHTML = `<a class="page-link" href="javascript:void(0);" title="Halaman Terakhir"><i class="ti ti-chevrons-right"></i></a>`;
                lastLi.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (currentPage !== totalPages) {
                        currentPage = totalPages;
                        render();
                    }
                });
                navEl.appendChild(lastLi);
            }

            selectEl.addEventListener('change', function () {
                const val = this.value;
                pageSize = val === 'all' ? 'all' : parseInt(val, 10);
                currentPage = 1;
                render();
            });

            // Initial render
            render();
        });
    }

    // Initialize pagination on DOM Ready
    initTablePagination();

    // Expose for dynamic re-renders if necessary
    window.refreshTablePagination = initTablePagination;
});


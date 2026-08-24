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

    // 3. Global Keyboard Shortcut for Command Palette (⌘K / Ctrl+K)
    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            const searchModalEl = document.getElementById('searchModal');
            if (searchModalEl) {
                const searchModal = bootstrap.Modal.getOrCreateInstance(searchModalEl);
                searchModal.show();
                setTimeout(() => {
                    const input = document.getElementById('globalSearchInput');
                    if (input) input.focus();
                }, 300);
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
});

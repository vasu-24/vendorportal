/**
 * Vendor Portal - Sidebar Toggle Script
 * Features:
 * - Collapse/Expand on desktop
 * - Slide-in on mobile
 * - Remember state in localStorage
 * - Keyboard shortcut (Ctrl+B)
 * - Tooltips when collapsed
 */

(function() {
    'use strict';

    // =====================================================
    // ELEMENTS
    // =====================================================
    
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const mobileToggle = document.getElementById('mobileToggle');
    
    // =====================================================
    // STATE
    // =====================================================
    
    const STORAGE_KEY = 'vendor_sidebar_collapsed';
    let isCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';
    let isMobile = window.innerWidth <= 992;
    
    // =====================================================
    // INITIALIZATION
    // =====================================================
    
    function init() {
        // Apply saved state on desktop only
        if (!isMobile && isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
        }
        
        // Initialize Bootstrap tooltips
        initTooltips();
        
        // Setup event listeners
        setupEventListeners();
    }
    
    // =====================================================
    // TOOLTIPS
    // =====================================================
    
    let tooltipInstances = [];
    
    function initTooltips() {
        // Only enable tooltips when sidebar is collapsed
        if (typeof bootstrap !== 'undefined' && sidebar.classList.contains('collapsed')) {
            enableTooltips();
        }
    }
    
    function enableTooltips() {
        const tooltipElements = sidebar.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipElements.forEach(el => {
            const instance = new bootstrap.Tooltip(el, {
                trigger: 'hover',
                container: 'body'
            });
            tooltipInstances.push(instance);
        });
    }
    
    function disableTooltips() {
        tooltipInstances.forEach(instance => {
            instance.dispose();
        });
        tooltipInstances = [];
    }
    
    // =====================================================
    // TOGGLE FUNCTIONS
    // =====================================================
    
    function toggleSidebar() {
        if (isMobile) {
            toggleMobileSidebar();
        } else {
            toggleDesktopSidebar();
        }
    }
    
    function toggleDesktopSidebar() {
        isCollapsed = !isCollapsed;
        
        sidebar.classList.toggle('collapsed', isCollapsed);
        mainContent.classList.toggle('expanded', isCollapsed);
        
        // Save state
        localStorage.setItem(STORAGE_KEY, isCollapsed);
        
        // Toggle tooltips
        if (isCollapsed) {
            enableTooltips();
        } else {
            disableTooltips();
        }
        
        // Update collapse button tooltip
        if (collapseBtn) {
            const tooltip = bootstrap.Tooltip.getInstance(collapseBtn);
            if (tooltip) {
                tooltip.setContent({ '.tooltip-inner': isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar' });
            }
        }
    }
    
    function toggleMobileSidebar() {
        const isOpen = sidebar.classList.contains('show');
        
        if (isOpen) {
            closeMobileSidebar();
        } else {
            openMobileSidebar();
        }
    }
    
    function openMobileSidebar() {
        sidebar.classList.add('show');
        sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMobileSidebar() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // =====================================================
    // EVENT LISTENERS
    // =====================================================
    
    function setupEventListeners() {
        // Desktop collapse button
        if (collapseBtn) {
            collapseBtn.addEventListener('click', toggleDesktopSidebar);
        }
        
        // Mobile toggle button
        if (mobileToggle) {
            mobileToggle.addEventListener('click', toggleMobileSidebar);
        }
        
        // Overlay click (close mobile sidebar)
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }
        
        // Keyboard shortcut (Ctrl+B or Cmd+B)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                toggleSidebar();
            }
        });
        
        // Handle resize
        window.addEventListener('resize', handleResize);
        
        // Close mobile sidebar when clicking a link
        const navLinks = sidebar.querySelectorAll('.nav-menu-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (isMobile) {
                    closeMobileSidebar();
                }
            });
        });
        
        // ESC key closes mobile sidebar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMobile && sidebar.classList.contains('show')) {
                closeMobileSidebar();
            }
        });
    }
    
    function handleResize() {
        const wasMobile = isMobile;
        isMobile = window.innerWidth <= 992;
        
        // Switching from mobile to desktop
        if (wasMobile && !isMobile) {
            closeMobileSidebar();
            
            // Restore saved state
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                enableTooltips();
            }
        }
        
        // Switching from desktop to mobile
        if (!wasMobile && isMobile) {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
            disableTooltips();
        }
    }
    
    // =====================================================
    // INITIALIZE ON DOM READY
    // =====================================================
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
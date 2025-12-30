<!-- Topbar Start -->
<div class="app-header min-h-topbar-height flex items-center sticky top-0 z-30 bg-(--topbar-background) border-b border-default-200">
    <div class="w-full flex items-center justify-between px-6">
        <div class="flex items-center gap-5">
            <!-- Sidenav Menu Toggle Button -->
            <button class="btn btn-icon size-8 hover:bg-default-150 rounded" id="button-toggle-menu">
                <i class="iconify lucide--align-left text-xl"></i>
            </button>
            
            <!-- Topbar Search -->
            <div class="lg:flex hidden items-center relative">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <i class="iconify tabler--search text-base"></i>
                </div>
                <input class="form-input px-12 text-sm rounded border-transparent focus:border-transparent w-60"
                       id="topbar-search" placeholder="Search customers, orders, tickets..." type="search"/>
                <button class="absolute inset-y-0 end-0 flex items-center pe-4" type="button">
                    <span class="ms-auto font-medium">⌘ K</span>
                </button>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Light/Dark Mode Button -->
            <div class="topbar-item">
                <button class="btn btn-icon size-8 hover:bg-default-150 transition-[scale,background] rounded-full"
                        id="light-dark-mode" type="button">
                    <i class="iconify tabler--moon text-xl absolute dark:scale-0 dark:-rotate-90 scale-100 rotate-0 transition-all duration-200"></i>
                    <i class="iconify tabler--sun text-xl absolute dark:scale-100 dark:rotate-0 scale-0 rotate-90 transition-all duration-200"></i>
                </button>
            </div>
            
            <!-- Notification Button -->
            <div class="topbar-item hs-dropdown [--auto-close:inside] relative inline-flex">
                <button aria-expanded="false" aria-haspopup="menu" aria-label="Notifications"
                        class="hs-dropdown-toggle btn btn-icon size-8 hover:bg-default-150 rounded-full relative"
                        type="button">
                    <i class="size-4.5" data-lucide="bell-ring"></i>
                    <span class="notification-badge absolute end-0 top-0 size-4 bg-danger text-white rounded-full text-xs flex items-center justify-center font-semibold" 
                          style="display: none;">0</span>
                </button>
                
                <div class="hs-dropdown-menu max-w-100 p-0" role="menu">
                    <!-- Header -->
                    <div class="p-4 border-b border-default-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <h3 class="text-base text-default-800 font-semibold">Notifications</h3>
                                <span class="notification-count size-5 font-semibold bg-orange-500 rounded text-white flex items-center justify-center text-xs">0</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabs -->
                    <nav aria-label="Tabs" aria-orientation="horizontal"
                         class="flex gap-x-1 bg-default-150 p-2 border-b border-default-200" role="tablist">
                        <button aria-controls="tabsOrders" aria-selected="true"
                                class="hs-tab-active:bg-card hs-tab-active:text-primary py-0.5 px-4 rounded font-semibold inline-flex items-center gap-x-2 border-b-2 border-transparent text-xs whitespace-nowrap text-default-500 hover:text-blue-600 active"
                                data-hs-tab="#tabsOrders" role="tab" type="button">
                            <i class="size-3.5" data-lucide="shopping-cart"></i>
                            Orders
                        </button>
                        <button aria-controls="tabsCustomers" aria-selected="false"
                                class="hs-tab-active:bg-card hs-tab-active:text-primary py-0.5 px-4 rounded font-semibold inline-flex items-center gap-x-2 border-b-2 border-transparent text-xs whitespace-nowrap text-default-500 hover:text-blue-600"
                                data-hs-tab="#tabsCustomers" role="tab" type="button">
                            <i class="size-3.5" data-lucide="user-plus"></i>
                            Customers
                        </button>
                    </nav>
                    
                    <!-- Tabs content -->
                    <div class="h-80 overflow-y-auto" data-simplebar="">
                        <!-- Orders Tab -->
                        <div aria-labelledby="tabsOrders-item" id="tabsOrders" role="tabpanel">
                            <div id="orders-notifications-container">
                                <div class="text-center py-12 text-default-500" id="no-orders">
                                    <i class="size-12 mx-auto mb-3" data-lucide="shopping-bag"></i>
                                    <p>No order notifications</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Customers Tab -->
                        <div aria-labelledby="tabsCustomers-item" class="hidden" id="tabsCustomers" role="tabpanel">
                            <div id="customers-notifications-container">
                                <div class="text-center py-12 text-default-500" id="no-customers">
                                    <i class="size-12 mx-auto mb-3" data-lucide="users"></i>
                                    <p>No customer notifications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="flex items-center justify-between p-4 border-t border-default-200">
                        <button class="text-sm font-medium text-default-900 hover:text-primary" onclick="markAllAsRead()">
                            Mark all as read
                        </button>
                        <a class="btn btn-sm text-white bg-primary" href="{{ route('notifications.index') }}">
                            View All
                            <i class="size-4 ms-1" data-lucide="arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Setting Offcanvas Button -->
            <div class="topbar-item">
                <button aria-controls="theme-customization" aria-expanded="false" aria-haspopup="dialog"
                        class="btn btn-icon size-8 hover:bg-default-150 rounded-full"
                        data-hs-overlay="#theme-customization" type="button">
                    <i class="size-4.5" data-lucide="settings"></i>
                </button>
            </div>
            
            <!-- Profile Dropdown Button -->
            <div class="topbar-item hs-dropdown relative inline-flex">
                <button aria-expanded="false" aria-haspopup="menu" aria-label="Profile Menu"
                        class="cursor-pointer rounded-full p-0.5 bg-primary/10 hover:bg-primary/20 transition-colors">
                    @php
                        $adminUser = auth()->user();
                        $nameParts = explode(' ', $adminUser->name);
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                        $bgColor = 'bg-primary';
                    @endphp
                    <div class="size-9 rounded-full {{ $bgColor }} flex items-center justify-center text-white font-semibold text-sm">
                        {{ $initials }}
                    </div>
                </button>
                
                <div aria-labelledby="hs-dropdown-with-icons" aria-orientation="vertical"
                     class="hs-dropdown-menu min-w-48" role="menu">
                    <div class="p-2">
                        <h6 class="mb-2 text-default-500">Welcome to Kan San</h6>
                        <a class="flex gap-3" href="#!">
                            @php
                                $adminUser = auth()->user();
                                $nameParts = explode(' ', $adminUser->name);
                                $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                            @endphp
                            <div class="relative inline-block">
                                <div class="rounded bg-primary/10 flex items-center justify-center">
                                    <div class="size-12 rounded-full bg-primary flex items-center justify-center text-white font-semibold">
                                        {{ $initials }}
                                    </div>
                                </div>
                                <span class="-top-1 -end-1 absolute w-2.5 h-2.5 bg-green-400 border-2 border-white rounded-full"></span>
                            </div>
                            <div>
                                <h6 class="mb-1 text-sm font-semibold text-default-800">
                                    {{ auth()->user()->name }}
                                </h6>
                                <p class="text-default-500">
                                    {{ auth()->user()->getRoleNames()->first() }}
                                </p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="flex flex-col gap-y-1">
                        <div class="border-t border-default-200 -mx-2 my-1"></div>
                        <a class="flex items-center gap-x-3.5 py-1.5 font-medium px-3 text-default-600 hover:bg-default-150 rounded cursor-pointer"
                           href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="size-4" data-lucide="log-out"></i>
                            Sign Out
                        </a>
                        <form action="{{ route('logout') }}" method="POST" style="display: none;" id="logout-form">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Topbar End -->

<script>
// Notification System
let notificationCheckInterval;

// Load notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    // Check for new notifications every 30 seconds
    notificationCheckInterval = setInterval(loadNotifications, 30000);
});

// Load notifications via AJAX
function loadNotifications() {
    fetch('{{ route("notifications.unread") }}')
        .then(response => response.json())
        .then(data => {
            updateNotificationUI(data.notifications, data.unread_count);
        })
        .catch(error => console.error('Error loading notifications:', error));
}

// Update notification UI with tabs
function updateNotificationUI(notifications, unreadCount) {
    const ordersContainer = document.getElementById('orders-notifications-container');
    const customersContainer = document.getElementById('customers-notifications-container');
    const noOrders = document.getElementById('no-orders');
    const noCustomers = document.getElementById('no-customers');
    const badge = document.querySelector('.notification-badge');
    const countSpan = document.querySelector('.notification-count');
    
    // Update counts
    countSpan.textContent = unreadCount;
    if (unreadCount > 0) {
        badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
    
    // Separate notifications by type
    const orderNotifications = notifications.filter(n => n.type === 'new_order');
    const customerNotifications = notifications.filter(n => n.type === 'customer_registered');
    
    // Update Orders Tab
    if (orderNotifications.length === 0) {
        noOrders.style.display = 'block';
    } else {
        noOrders.style.display = 'none';
        let ordersHtml = '';
        orderNotifications.forEach(notification => {
            ordersHtml += createNotificationHTML(notification);
        });
        ordersContainer.innerHTML = ordersHtml;
    }
    
    // Update Customers Tab
    if (customerNotifications.length === 0) {
        noCustomers.style.display = 'block';
    } else {
        noCustomers.style.display = 'none';
        let customersHtml = '';
        customerNotifications.forEach(notification => {
            customersHtml += createNotificationHTML(notification);
        });
        customersContainer.innerHTML = customersHtml;
    }
    
    // Reinitialize lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Create notification HTML
function createNotificationHTML(notification) {
    const iconColor = getIconColor(notification.color);
    const icon = notification.icon || 'bell';
    
    return `
        <a class="flex gap-3 p-4 items-start hover:bg-default-150 ${notification.is_read ? 'opacity-60' : ''}" 
           href="#" onclick="markAsRead(${notification.id}); return false;">
            <div>
                <div class="size-10 rounded-md ${iconColor} flex justify-center items-center">
                    <i class="size-5" data-lucide="${icon}"></i>
                </div>
            </div>
            <div class="flex justify-between gap-2 w-full">
                <div class="text-sm">
                    <h6 class="mb-1 font-medium text-default-800">${notification.title}</h6>
                    <p class="text-xs text-default-600 mb-2">${notification.message}</p>
                    <p class="flex items-center gap-1 text-default-500 text-xs">
                        <i class="align-middle size-3.5" data-lucide="clock"></i>
                        <span>${notification.time_ago}</span>
                    </p>
                </div>
                <div>
                    ${!notification.is_read ? '<div class="flex items-center gap-2 text-xs text-default-500"><div class="w-1.5 h-1.5 bg-primary rounded-full"></div></div>' : ''}
                </div>
            </div>
        </a>
    `;
}

// Get icon background color based on notification color
function getIconColor(color) {
    const colors = {
        'primary': 'bg-primary/10 text-primary',
        'success': 'bg-success/10 text-success',
        'danger': 'bg-danger/10 text-danger',
        'warning': 'bg-warning/10 text-warning',
        'info': 'bg-info/10 text-info',
    };
    return colors[color] || colors['primary'];
}

// Mark single notification as read
function markAsRead(id) {
    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

// Mark all notifications as read
function markAllAsRead() {
    fetch('{{ route("notifications.read-all") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications();
        }
    })
    .catch(error => console.error('Error marking all as read:', error));
}

// Sidebar Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('topbar-search');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const sidebar = document.querySelector('.sidenav-menu');
            
            if (!sidebar) return;
            
            // Get all menu items
            const menuItems = sidebar.querySelectorAll('.menu-link');
            
            menuItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                const parentLi = item.closest('li');
                
                if (searchTerm === '') {
                    // Show all items when search is empty
                    if (parentLi) {
                        parentLi.style.display = '';
                    }
                } else {
                    // Filter based on search term
                    if (text.includes(searchTerm)) {
                        if (parentLi) {
                            parentLi.style.display = '';
                            // Highlight matching text
                            item.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                        }
                    } else {
                        if (parentLi) {
                            parentLi.style.display = 'none';
                        }
                    }
                }
                
                // Remove highlight when search is cleared
                if (searchTerm === '') {
                    item.style.backgroundColor = '';
                }
            });
        });
        
        // Clear search on Escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.blur();
            }
        });
    }
});
</script>

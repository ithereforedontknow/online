<div class="offcanvas offcanvas-end" tabindex="-1" id="viewNotificationsOffcanvas" aria-labelledby="viewNotificationsOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <div class="d-flex">
            <h5 class="offcanvas-title me-2" id="viewNotificationsOffcanvasLabel">
                Notifications
                <span class="text-muted small ms-2" id="notificationTotalCount"></span>
            </h5>
            <form id="notificationSearchForm" class="me-2">
                <div class="input-group input-group-sm">
                    <input
                        type="search"
                        id="notificationSearchInput"
                        class="form-control"
                        placeholder="Search notifications..."
                        aria-label="Search notifications">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    </div>
    <div class="offcanvas-body p-3">
        <div id="notificationList" class="list-group list-group-flush">
            <!-- Notifications will be dynamically loaded here -->
        </div>
    </div>
</div>
document.addEventListener('DOMContentLoaded', function() {
    // Pathname sekarang
    let currentPath = window.location.pathname;
    if (currentPath.endsWith('/')) currentPath = currentPath.slice(0, -1);

    // Biar gampang, mapping menu: href, folder
    const menuGroups = [
        { selector: 'a[href*="../listStockEdc"]', path: '/listStockEdc' },
        { selector: 'a[href*="../ReadyToUse"]', path: '/ReadyToUse' },
        { selector: 'a[href*="../AlreadyToUse"]', path: '/AlreadyToUse' },
        { selector: 'a[href*="../return_edc"]', path: '/return_edc' },
        { selector: 'a[href*="../technician"]', path: '/technician' },
        { selector: 'a[href*="../ho"]', path: '/ho' },
        { selector: 'a[href*="../laporan"]', path: '/laporan' },
        { selector: 'a[href*="../profile"]', path: '/profile' },
        { selector: 'a[href*="../change_password"]', path: '/change_password' },
        { selector: 'a[href*="../user_management"]', path: '/user_management' },
        { selector: 'a[href*="../user_management"]', path: '/register' },
        { selector: 'a[href*="../dashboard"]', path: '/dashboard' }
    ];

    // Loop semua group
    menuGroups.forEach(group => {
        // Jika path url sekarang mengandung group path
        if(currentPath.includes(group.path)) {
            // Kasih active di nav-link yg sesuai
            document.querySelectorAll('.main-sidebar ' + group.selector).forEach(function(link){
                link.classList.add('active');
                // Kasih menu-open di parent .has-treeview (jika ada)
                let parentTree = link.closest('.has-treeview');
                if(parentTree) {
                    parentTree.classList.add('menu-open');
                    // Kasih active juga di parent nav-link
                    let parentLink = parentTree.querySelector(':scope > .nav-link');
                    if(parentLink) parentLink.classList.add('active');
                }
            });
        }
    });
});

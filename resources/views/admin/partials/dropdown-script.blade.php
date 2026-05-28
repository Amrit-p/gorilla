<script>
    function crmDropdown(btnSel, menuSel) {
        let $openMenu = null;

        function closeMenu() {
            if ($openMenu) {
                $openMenu.addClass('hidden').css({ position: '', top: '', left: '', zIndex: '' });
                $openMenu = null;
            }
        }

        $(document).on('click', btnSel, function (e) {
            e.stopPropagation();
            const $btn  = $(this);
            const $menu = $btn.siblings(menuSel);

            if ($openMenu && $openMenu.is($menu)) { closeMenu(); return; }

            closeMenu();

            $menu.css({ position: 'fixed', top: '-9999px', left: '-9999px', zIndex: 9999 }).removeClass('hidden');

            const menuH = $menu.outerHeight();
            const menuW = $menu.outerWidth();
            const rect  = $btn[0].getBoundingClientRect();
            const vw    = window.innerWidth;
            const vh    = window.innerHeight;

            let top  = rect.bottom + 4;
            let left = rect.right - menuW;

            if (top + menuH > vh - 8) top = rect.top - menuH - 4;
            if (top < 8)              top = 8;
            if (left < 8)             left = 8;
            if (left + menuW > vw - 8) left = vw - menuW - 8;

            $menu.css({ top: top + 'px', left: left + 'px' });
            $openMenu = $menu;
        });

        $(document).on('click', function () { closeMenu(); });
        $(document).on('click', menuSel + ' a, ' + menuSel + ' button', function () { closeMenu(); });
        $(window).on('scroll', closeMenu);
    }
</script>

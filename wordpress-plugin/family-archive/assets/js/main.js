document.addEventListener('DOMContentLoaded', function () {
    var userMenu = document.querySelector('.fa-user-menu');
    if (userMenu) {
        userMenu.setAttribute('tabindex', '0');
        userMenu.addEventListener('click', function (e) {
            e.stopPropagation();
            userMenu.classList.toggle('is-open');
        });
        document.addEventListener('click', function () {
            userMenu.classList.remove('is-open');
        });
    }

    var searchInput = document.getElementById('fa-member-search');
    var tree = document.getElementById('fa-family-tree');
    if (!searchInput || !tree) return;

    var listItems = tree.querySelectorAll('li[data-name]');

    searchInput.addEventListener('input', function () {
        var query = searchInput.value.trim().toLowerCase();

        listItems.forEach(function (li) {
            var card = li.querySelector('.fa-person-card');
            if (!card) return;
            card.classList.remove('is-highlighted');
        });

        if (!query) return;

        listItems.forEach(function (li) {
            var name = (li.getAttribute('data-name') || '').toLowerCase();
            if (name.indexOf(query) !== -1) {
                var card = li.querySelector('.fa-person-card');
                if (card) card.classList.add('is-highlighted');
            }
        });
    });
});

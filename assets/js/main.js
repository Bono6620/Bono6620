document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('member-search');
    var tree = document.getElementById('family-tree');
    if (!searchInput || !tree) return;

    var listItems = tree.querySelectorAll('li[data-name]');

    searchInput.addEventListener('input', function () {
        var query = searchInput.value.trim().toLowerCase();

        listItems.forEach(function (li) {
            var card = li.querySelector('.person-card');
            if (!card) return;
            card.classList.remove('is-highlighted');
        });

        if (!query) return;

        listItems.forEach(function (li) {
            var name = (li.getAttribute('data-name') || '').toLowerCase();
            if (name.indexOf(query) !== -1) {
                var card = li.querySelector('.person-card');
                if (card) card.classList.add('is-highlighted');
            }
        });
    });
});

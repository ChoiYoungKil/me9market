document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-rating-input]').forEach(function (group) {
        var inputs = Array.from(group.querySelectorAll('input[type=radio]'));
        function paint(score) {
            group.querySelector('.me9-rating-fill').style.width = (Number(score) * 20) + '%';
            group.querySelector('output').textContent = score ? score + '점' : '선택 안 함';
        }
        function selected() { return (inputs.find(function (input) { return input.checked; }) || {}).value || ''; }
        group.querySelectorAll('[data-rating-value]').forEach(function (label) {
            label.addEventListener('pointerenter', function (event) { if (event.pointerType !== 'touch') paint(label.dataset.ratingValue); });
        });
        group.querySelector('.me9-rating-stars').addEventListener('pointerleave', function () { paint(selected()); });
        inputs.forEach(function (input) { input.addEventListener('change', function () { paint(selected()); }); });
        group.querySelector('.me9-rating-clear').addEventListener('click', function () { inputs.forEach(function (input) { input.checked = false; }); paint(''); });
        paint(selected());
    });
});

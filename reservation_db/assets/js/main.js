// انیمیشن اسکرول
document.addEventListener('DOMContentLoaded', function() {
    // هدر شفاف هنگام اسکرول
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.style.padding = '0.5rem 0';
            navbar.style.background = '#1a252f';
        } else {
            navbar.style.padding = '1rem 0';
            navbar.style.background = 'linear-gradient(135deg, #2c3e50, #1a252f)';
        }
    });

    // انیمیشن شمارنده برای امتیازات (اختیاری)
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = target / 200;
            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(updateCount, 10);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });

    // Tooltip فعال‌سازی (اگر از bootstrap استفاده شود)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
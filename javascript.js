 const navToggle = document.getElementById('navToggle');
    const mainNav = document.getElementById('mainNav');
 
    navToggle.addEventListener('click', () => {
        const isOpen = mainNav.classList.toggle('is-open');
        navToggle.classList.toggle('is-open');
        navToggle.setAttribute('aria-expanded', isOpen);
    });
 
    // Close the menu when a link is tapped (mobile)
    document.querySelectorAll('#mainNav a').forEach(link => {
        link.addEventListener('click', () => {
            mainNav.classList.remove('is-open');
            navToggle.classList.remove('is-open');
            navToggle.setAttribute('aria-expanded', false);
        });
    });


    function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}
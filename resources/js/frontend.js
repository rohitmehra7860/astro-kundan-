import 'bootstrap';

const form = document.querySelector('#contact-form');
if (form) {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        const status = document.querySelector('#contact-status');
        const payload = Object.fromEntries(new FormData(form).entries());
        button.disabled = true;
        status.classList.remove('error');
        status.textContent = 'Sending your enquiry…';
        try {
            const response = await fetch('/api/contact-leads', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload),
            });
            const result = await response.json();
            if (!response.ok) {
                const firstError = Object.values(result.errors ?? {}).flat()[0];
                throw new Error(firstError ?? result.message ?? 'Please check the form and try again.');
            }
            form.reset();
            status.textContent = result.message ?? 'Thank you. Your enquiry has been received.';
        } catch (error) {
            status.classList.add('error');
            status.textContent = error.message ?? 'We could not send your enquiry. Please try again.';
        } finally {
            button.disabled = false;
        }
    });
}

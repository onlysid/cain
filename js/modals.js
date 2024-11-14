/*
There are a few things which all modals have in common. They must be:
- Opened somehow
- Animated open
- Enable a blurry backdrop
- Disable background scrolling
- Closed in multiple ways
    - Via a cancel button
    - Via a close icon
    - Via clicking on the backdrop overlay

Thus, it makes sense to handle all this in as DRY a way as possible. Some modals have very specific
use cases which means they require their own functions. These will all be listed here.
*/

// Within user settings, we need to display the modal depending on the checked state of the time user out field
document.addEventListener('click', (e) => {
    let target = e.target;

    // Open modal: Traverse up to find an element with `data-modal-open`
    while (target && !target.hasAttribute('data-modal-open') && !target.hasAttribute('data-modal-close')) {
        target = target.parentElement;
    }

    if (target) {
        // Check if it's an open action
        if (target.hasAttribute('data-modal-open')) {
            const modalId = target.getAttribute('data-modal-open');
            const modal = document.getElementById(modalId);
            if (modal) {
                // Firstly, if there is a modal open, close it!
                closeModal();
                modal.classList.add('active');
                const modalWrapper = modal.closest('.modal-wrapper'); // Get parent wrapper
                if (modalWrapper) {
                    modalWrapper.classList.add('active'); // Add active to parent modal-wrapper
                }
                document.body.classList.add('no-scroll'); // Prevent background scrolling
            }
        }

        // Check if it's a close action
        if (target.hasAttribute('data-modal-close')) {
            closeModal();
        }

        // Specific modal actions if hidden input fields require populating dynamically
        if (target.matches('[data-modal-action]')) {
            // We know that something needs populating. Find out what it is.

        }
    }

    // Close all modals if we have selected a backdrop
    if(e.target.classList.contains('modal-wrapper') && !e.target.classList.contains('generic-modal.active')) {
        closeModal();
    }
});

// Close modal function
function closeModal() {
    const modal = document.querySelector('.generic-modal.active');
    if (modal) {
        modal.classList.remove('active');
        const modalWrapper = modal.closest('.modal-wrapper'); // Get parent wrapper
        if (modalWrapper) {
            modalWrapper.classList.remove('active'); // Remove active from parent modal-wrapper
        }
        document.body.classList.remove('no-scroll'); // Re-enable background scrolling
    }
}
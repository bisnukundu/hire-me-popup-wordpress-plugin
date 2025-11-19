(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var popup = document.getElementById('inbio-hire-popup');
        if (!popup) {
            return;
        }
        // Delay showing the popup by 3 seconds to avoid startling visitors immediately.
        setTimeout(function() {
            popup.style.display = 'block';
        }, 3000);

        // Close functionality.
        var closeBtn = popup.querySelector('.inbio-popup-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                popup.style.display = 'none';
            });
        }

        // Draggable functionality.
        var isDragging = false;
        var offsetX = 0;
        var offsetY = 0;

        // Start dragging on mousedown, unless the user clicked the close or hire button.
        popup.addEventListener('mousedown', function(e) {
            if (e.target.closest('.inbio-popup-button') || e.target.closest('.inbio-popup-close')) {
                return;
            }
            isDragging = true;
            var rect = popup.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            // Prevent text selection or other default behaviour while dragging.
            e.preventDefault();
        });

        // Update position as the mouse moves.
        document.addEventListener('mousemove', function(e) {
            if (!isDragging) {
                return;
            }
            var newX = e.clientX - offsetX;
            var newY = e.clientY - offsetY;

            // Constrain the popup within the viewport.
            var maxX = window.innerWidth - popup.offsetWidth;
            var maxY = window.innerHeight - popup.offsetHeight;
            if (newX < 0) newX = 0;
            if (newY < 0) newY = 0;
            if (newX > maxX) newX = maxX;
            if (newY > maxY) newY = maxY;

            popup.style.left = newX + 'px';
            popup.style.top = newY + 'px';
            // Remove bottom positioning so the top value takes effect when dragging.
            popup.style.bottom = 'auto';
        });

        // Stop dragging on mouseup.
        document.addEventListener('mouseup', function() {
            isDragging = false;
        });
    });
})();
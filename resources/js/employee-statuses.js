        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('status');
            const leaveSection = document.getElementById('leaveSection');
            const terminationSection = document.getElementById('terminationSection');

            // Function to toggle sections based on status
            function toggleSections() {
                const status = statusSelect.value;

                // Reset both sections to hidden
                leaveSection.classList.remove('visible-section');
                leaveSection.classList.add('hidden-section');
                terminationSection.classList.remove('visible-section');
                terminationSection.classList.add('hidden-section');

                // Show appropriate section based on status
                if (status === 'on_leave') {
                    leaveSection.classList.remove('hidden-section');
                    leaveSection.classList.add('visible-section');
                } else if (status === 'terminated') {
                    terminationSection.classList.remove('hidden-section');
                    terminationSection.classList.add('visible-section');
                }
            }

            // Initial call to set correct visibility
            toggleSections();

            // Add event listener for status changes
            statusSelect.addEventListener('change', toggleSections);
        });

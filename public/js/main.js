document.addEventListener('DOMContentLoaded', () => {
    const rawOutputFormat = document.getElementById('rawOutputFormat');
    const tableOutputFormat = document.getElementById('tableOutputFormat');
    const outputContainer = document.getElementById('output-container');

    // Add event listeners to the radio buttons
    rawOutputFormat.addEventListener('change', () => {
        if (rawOutputFormat.checked) {
            fetchAndUpdateOutput('raw');
        }
    });

    tableOutputFormat.addEventListener('change', () => {
        if (tableOutputFormat.checked) {
            fetchAndUpdateOutput('table');
        }
    });

    // Function to fetch and update the output based on format
    function fetchAndUpdateOutput(format) {
        const url = new URL(window.location.href);
        url.searchParams.set('format', format);
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                const tempElement = document.createElement('div');
                tempElement.innerHTML = html;
                
                const newOutputContainer = tempElement.querySelector('#output-container');
                if (newOutputContainer) {
                    outputContainer.innerHTML = newOutputContainer.innerHTML;
                }
            })
            .catch(error => {
                console.error('Error fetching output:', error);
            });
    }

    // Auto-dismiss alerts after 10 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert .btn-close').forEach(btn => btn.click());
    }, 10000);
}); 
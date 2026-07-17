const BASE_URL = 'http://localhost:8000/api/';

/**
 * Reusable function to make API calls
 * @param {string} endpoint - The API endpoint (e.g., 'LoginUser.php')
 * @param {string} method - GET, POST, PUT
 * @param {Object|FormData} data - The payload
 * @param {boolean} isFormData - Set to true for POST endpoints like AddFaculty
 */
async function apiCall(endpoint, method = 'GET', data = null, isFormData = false) {
    const options = {
        method: method,
        headers: {}
    };

    // Attach token if exists
    const token = localStorage.getItem('access_token');
    if (token) {
        options.headers['Authorization'] = `Bearer ${token}`;
    }

    if (data) {
        if (isFormData) {
            options.body = data; // FormData handles its own Content-Type
        } else {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }
    }

    try {
        const response = await fetch(`${BASE_URL}${endpoint}`, options);
        return await response.json();
    } catch (error) {
        console.error("API Fetch Error:", error);
        return { status: 'error', message: 'Network or CORS error. Check console.' };
    }
}

// Utility to show alerts
function showAlert(message, type = 'success') {
    const alertBox = document.getElementById('alertBox');
    if(alertBox) {
        alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        setTimeout(() => alertBox.innerHTML = '', 4000);
    } else {
        alert(message);
    }
}

    // Initialize the map with a default location (e.g., Manila)
    var map = L.map('map').setView([14.5995, 120.9842], 12);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add a marker for the initial location
    var marker = L.marker([14.5995, 120.9842], { draggable: true }).addTo(map);

    // Function to update the location input fields
function updateLocationInputs(lat, lng) {
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;

    // Reverse geocode using OpenCage API
    const apiKey = '9ac6f13e83b3424fac80a49e713503fd'; 
    fetch(`https://api.opencagedata.com/geocode/v1/json?key=${apiKey}&q=${lat}+${lng}&no_annotations=1`)
        .then(response => response.json())
        .then(data => {
            const locationName = data.results && data.results.length > 0 ? data.results[0].formatted : 'Unknown location';
            document.getElementById('location').value = locationName;
        })
        .catch(error => console.log('Error reverse geocoding:', error));
}


    // Use Geolocation API to get user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                // Update map and marker to user's location
                map.setView([lat, lng], 15);
                marker.setLatLng([lat, lng]);

                // Update input fields with current location
                updateLocationInputs(lat, lng);
            },
            function (error) {
                console.log('Geolocation error:', error);
                alert('Unable to fetch your location. Please select a location manually.');
            }
        );
    } else {
        alert('Geolocation is not supported by your browser. Please select a location manually.');
    }

    // Update location inputs when the map is clicked
    map.on('click', function (e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        // Move the marker to the clicked location
        marker.setLatLng(e.latlng);

        // Update input fields with the clicked location
        updateLocationInputs(lat, lng);
    });

    // Update location inputs when the marker is dragged
    marker.on('dragend', function (e) {
        var position = marker.getLatLng();
        updateLocationInputs(position.lat, position.lng);
    });

   // Location search suggestions
const locationInput = document.getElementById('location');
locationInput.addEventListener('input', function () {
    const query = locationInput.value.trim();

    if (query.length > 2) { // Only start searching after 3 characters
        const apiKey = '9ac6f13e83b3424fac80a49e713503fd'; 
 //       for whole world
 //      fetch(`https://api.opencagedata.com/geocode/v1/json?key=${apiKey}&q=${query}&no_annotations=1`) 
            fetch(`https://api.opencagedata.com/geocode/v1/json?key=${apiKey}&q=${query}&no_annotations=1&countrycode=PH`)

            .then(response => response.json())
            .then(data => {
                // Clear previous suggestions
                let suggestionsList = document.querySelector('.suggestions');
                if (suggestionsList) {
                    suggestionsList.remove();
                }

                // Create a suggestion list
                suggestionsList = document.createElement('ul');
                suggestionsList.classList.add('suggestions');
                data.results.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item.formatted;
                    li.addEventListener('click', function () {
                        locationInput.value = item.formatted;
                        const lat = item.geometry.lat;
                        const lon = item.geometry.lng;

                        // Update the map and marker with the selected location
                        map.setView([lat, lon], 15);
                        marker.setLatLng([lat, lon]);

                        // Update input fields
                        updateLocationInputs(lat, lon);

                        // Remove suggestions after selection
                        suggestionsList.remove();
                    });
                    suggestionsList.appendChild(li);
                });

                locationInput.parentNode.appendChild(suggestionsList);
            })
            .catch(error => console.log('Error searching location:', error));
    }
});


    // Close suggestions if clicking outside the input
    document.addEventListener('click', function (e) {
        if (!locationInput.contains(e.target)) {
            let suggestionsList = document.querySelector('.suggestions');
            if (suggestionsList) {
                suggestionsList.remove();
            }
        }
    });

    // Initialize camera visibility state
const cameraDropdown = document.getElementById('camera-dropdown');

// Initially hide the camera dropdown
cameraDropdown.style.display = 'none';

// Camera functionality
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const captureButton = document.getElementById('capture');
const retakeButton = document.getElementById('retake');
const preview = document.getElementById('preview');
const capturedImage = document.getElementById('captured-image');

document.getElementById('start-camera').addEventListener('click', startCamera);
captureButton.addEventListener('click', captureImage);
retakeButton.addEventListener('click', retakeImage);

function startCamera() {
    // Show the camera dropdown when the start button is clicked
    cameraDropdown.style.display = 'block';

    // Start the camera feed
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            video.srcObject = stream;
        })
        .catch(function(error) {
            console.log('Error accessing camera: ', error);
        });
}

function captureImage() {
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Draw the current video frame on the canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Get the image data as a data URL (base64)
    const imageData = canvas.toDataURL('image/png');

    // Save the image data to the hidden input field
    capturedImage.value = imageData;

    // Hide the video feed
    video.style.display = 'none';

    // Show the captured image and retake button
    canvas.style.display = 'block';
    retakeButton.style.display = 'block';
}

function retakeImage() {
    // Clear the preview and canvas
    preview.innerHTML = '';
    canvas.style.display = 'none';

    // Reset the hidden input field
    capturedImage.value = '';

    // Show the video feed again
    video.style.display = 'block';

    // Hide the retake button
    retakeButton.style.display = 'none';
}

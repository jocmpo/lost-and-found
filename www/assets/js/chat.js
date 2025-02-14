function toggleNavbar() {
    const userList = document.getElementById('userList');
    const toggleBtn = document.querySelector('.toggle-btn');
    
    userList.classList.toggle('open');
    toggleBtn.classList.toggle('hidden'); // Hide hamburger when sidebar is open
}

function searchUsers() {
        var searchTerm = document.getElementById('search-input').value;
        if (searchTerm.trim() !== "") {
            // Perform the search by redirecting to the same page with the search term
            window.location.href = "chat.php?search=" + encodeURIComponent(searchTerm);
        }
    }

function removeImage() {
    // Remove the image when a user is clicked
    const imageElement = document.getElementById('image');
    if (imageElement) {
        imageElement.style.display = 'none'; // Hides the image
    }
}
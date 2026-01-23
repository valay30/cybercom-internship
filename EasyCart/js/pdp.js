// JavaScript for Image Switching
function switchImage(imageSrc, thumbnail) {
    // Update the main image
    document.getElementById('mainImage').src = imageSrc;

    // Remove active class from all thumbnails
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });

    // Add active class to clicked thumbnail
    thumbnail.classList.add('active');
}

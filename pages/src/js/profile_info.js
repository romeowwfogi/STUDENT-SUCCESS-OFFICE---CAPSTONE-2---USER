if (!isProfileSet) {
    document.querySelectorAll('.body_content_container').forEach(element => {
        element.style.display = 'none';
    });
    openProfileModal();
} else {
    document.querySelectorAll('.body_content_container').forEach(element => {
        element.style.display = 'block';
    });

    // Function to capitalize first letter of each word
    function capitalize(name) {
        return name
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    // Combine into full name, skipping empty parts
    let full_name = [first_name, middle_name, last_name, suffix]
        .filter(name => name && name.trim() !== "")
        .map(capitalize)
        .join(" ");

    // Set value to input field
    document.getElementById('fullname').textContent = full_name;
}
window.addEventListener('scroll', function() {
    const header = document.querySelector("header");
    if (window.scrollY > 50) {
        header.classList.add("scrolled");
    } else {
        header.classList.remove("scrolled");
    }
});
//popup
const popup = document.getElementById("popup");
const popupImg = document.getElementById("popup-img");
const popupTitle = document.getElementById("popup-title");
const popupDescription = document.getElementById("popup-description");
const closeBtn = document.querySelector(".close-btn");

// Get all clickable image wrappers
const triggers = document.querySelectorAll(".popup-trigger");

triggers.forEach(trigger => {
    trigger.addEventListener("click", () => {
        const imgSrc = trigger.dataset.img;
        const title = trigger.dataset.title;
        const description = trigger.dataset.description;

        // Fill in popup data
        popupImg.src = imgSrc;
        popupTitle.textContent = title;
        popupDescription.textContent = description;

        // Show the popup
        popup.style.display = "flex";
    });
});

// Close modal
closeBtn.addEventListener("click", () => {
    popup.style.display = "none";
});

// Close modal when clicking outside content
window.addEventListener("click", e => {
    if (e.target === popup) {
        popup.style.display = "none";
    }
});

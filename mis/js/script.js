 // Function to toggle sidebar
 function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("open");
  }

const images = document.querySelectorAll('.zoom-image');
const overlay2 = document.getElementById('overlay2');
const overlayImage = document.getElementById('overlay-image');
const closeButton = document.getElementById('close2');

images.forEach(image => {
  image.addEventListener('click', () => {
      overlay2.style.display = 'flex';
      overlayImage.src = image.src; // Set the overlay image source
  });
});

closeButton.addEventListener('click', () => {
  overlay2.style.display = 'none';
});

document.getElementById('searchInput').addEventListener('keyup', function() {
  var input = this.value.toLowerCase();
  var rows = document.querySelectorAll('#dataTable tbody tr');

  rows.forEach(function(row) {
      var cells = row.getElementsByTagName('td');
      var found = false;

      for (var i = 0; i < cells.length; i++) {
          if (cells[i].textContent.toLowerCase().indexOf(input) > -1) {
              found = true;
              break;
          }
      }

      row.style.display = found ? '' : 'none';
  });
});


// Get the modal
const modal = document.getElementById("updateModal");
const modalBtn = document.getElementById("modal");
const closeModal = document.getElementById("closeModal");
modalBtn.onclick = function() {
    modal.style.display = "flex";
}
closeModal.onclick = function() {
    modal.style.display = "none"; 
}
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}




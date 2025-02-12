document.addEventListener('DOMContentLoaded', function() {
    // Add an event listener to the "Annuler" button
    const cancelButton = document.getElementById('cancelBtn');
    
    cancelButton.addEventListener('click', function() {
      if (confirm("Voulez-vous vraiment annuler le paiement et revenir à la page de boutique ?")) {
        // Logic to handle the cancellation
        // Close the modal if it's open
        const modal = document.querySelector('.modal.show');
        if (modal) {
          const modalInstance = bootstrap.Modal.getInstance(modal);
          modalInstance.hide(); // Close the modal
        }
  
        // Optionally, redirect to another page or reset the form
        window.location.href = 'shoping-cart.html'; // You can change the URL to your desired page
      }
    });
  });


  function downloadInvoice() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Récupère le contenu de la facture (le body de la card)
    const invoiceContent = document.querySelector('.card-body').innerHTML;

    // Ajouter le contenu HTML dans le PDF
    doc.html(invoiceContent, {
        callback: function (doc) {
            // Sauvegarde le PDF avec un nom
            doc.save('facture.pdf');
        },
        x: 10,
        y: 10
    });
}

  
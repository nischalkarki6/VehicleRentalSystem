<?php
$title = 'Search | CarCodemandu';
$page = 'search';
$css = 'search';
include 'view/layout/header.php';
?>

<main class="page-container container">
      <div class="page-header">
        <h1>Search Results</h1>
        <p>Showing available vehicles for your selected destination.</p>
      </div>
      <div class="fleet-grid">
        <div class="fleet-card">
          <img
            src="https://images.unsplash.com/photo-1614200187524-dc4b892acf16?q=80&w=600&auto=format&fit=crop"
            class="fleet-img"
            alt="Supercar"
          />
          <div class="fleet-details">
            <h3>V8 Sportscar</h3>
            <p>Automatic • 2 Seats • Petrol</p>
            <div class="fleet-price">
              $300 / day
              <button class="btn-primary" style="padding: 0.5rem 1rem">
                Book
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

<?php include 'view/layout/footer.php'; ?>

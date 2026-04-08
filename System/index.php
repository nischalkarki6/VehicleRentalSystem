<?php
$title = 'CarCodemandu';
$page = 'home';
$css = 'index';
$js = 'index';
include 'view/layout/header.php';
?>

<main>
      <section class="hero">
        <div class="hero-bg-right"></div>
        <div class="container hero-container">
          <div class="hero-left">
            <h1 class="hero-title">
              With our exceptional fleet and customer-centric approach, we
              ensure a
              <span class="highlight">seamless travel experience.</span>
            </h1>
            <p class="hero-subtitle">
              Premium vehicle rentals across Nepal. Discover freedom on the road
              with our curated selection of cars and bikes.
            </p>
          </div>
          <!-- Empty right container as background handles the image -->
          <div class="hero-right"></div>
        </div>
      </section>

      <section class="booking-container">
        <div class="container">
          <div class="booking-tabs" id="vehicle-tabs">
            <button class="tab active" data-type="4-wheeler">
              <span class="material-symbols-outlined">directions_car</span>
              4-WHEELER
            </button>
            <button class="tab" data-type="2-wheeler">
              <span class="material-symbols-outlined">two_wheeler</span>
              2-WHEELER
            </button>
          </div>
          <div class="booking-bar">
            <div class="booking-item">
              <label for="pickup-location">Pick up location</label>
              <div class="input-row">
                <span class="material-symbols-outlined">location_on</span>
                <input
                  type="text"
                  id="pickup-location"
                  placeholder="Kathmandu, Pokhara..."
                  required
                />
              </div>
            </div>
            <div class="booking-item">
              <label for="dropoff-location">Drop off location</label>
              <div class="input-row">
                <span class="material-symbols-outlined">near_me</span>
                <input
                  type="text"
                  id="dropoff-location"
                  placeholder="Same as pick up location..."
                />
              </div>
            </div>
            <div class="booking-item">
              <label for="pickup-date">Pick up date</label>
              <div class="input-row">
                <span class="material-symbols-outlined">calendar_month</span>
                <input type="date" id="pickup-date" required />
              </div>
            </div>
            <div class="booking-item">
              <label for="travel-location">Travel location</label>
              <div class="input-row">
                <span class="material-symbols-outlined">map</span>
                <input
                  type="text"
                  id="travel-location"
                  placeholder="Destination..."
                />
              </div>
            </div>
            <button class="btn-search" id="main-search-btn">SEARCH</button>
          </div>
        </div>
      </section>

      <section class="stats-section">
        <div class="container stats-grid">
          <div class="stat-box">
            <h2>2000+</h2>
            <p>NO. OF VEHICLES</p>
          </div>
          <div class="stat-box">
            <h2>3000+</h2>
            <p>NO. OF DRIVERS</p>
          </div>
          <div class="stat-box">
            <h2>5000+</h2>
            <p>CUSTOMERS SERVED</p>
          </div>
          <div class="stat-box">
            <h2>15</h2>
            <p>YEARS OF EXPERIENCE</p>
          </div>
        </div>
      </section>

      <section class="legacy-section container">
        <div class="legacy-grid">
          <div class="legacy-text">
            <span class="section-label">OUR LEGACY</span>
            <h2>THE TOP CAR RENTAL COMPANY IN NEPAL.</h2>
            <p>
              Founded in 2003, CarCodemandu is to be the top car rental company
              in Nepal, providing an unmatched travel experience. In the hands
              of skilled drivers and first-rate service, satisfy the various
              needs of passengers in a safe and comfortable manner.
            </p>

            <div class="trusted-block">
              <div class="avatars">
                <div class="avatar" style="background-color: #aebfd0"></div>
                <div class="avatar" style="background-color: #4a5d7c"></div>
                <div class="avatar" style="background-color: #1b263b"></div>
              </div>
              <span class="trusted-text"
                >Trusted by thousands of local and international
                travelers.</span
              >
            </div>
          </div>
          <div class="legacy-images">
            <img src="" class="legacy-img-1" alt="Green SUV" />
            <img src="" class="legacy-img-2" alt="Vintage Motorcycle Detail" />
          </div>
        </div>
      </section>

      <section class="how-it-works container">
        <div class="how-it-works-header">
          <h2>HOW IT WORKS.</h2>
          <p>Four simple steps to start your journey with CarCodemandu.</p>
        </div>
        <div class="steps-row">
          <div class="step">
            <div class="icon-box">
              <span class="material-symbols-outlined">directions_car</span>
            </div>
            <h3>CHOOSE YOUR VEHICLE</h3>
            <p>Select from our extensive fleet of 4-wheelers and 2-wheelers.</p>
          </div>
          <div class="arrow">
            <span class="material-symbols-outlined">arrow_right_alt</span>
          </div>
          <div class="step">
            <div class="icon-box">
              <span class="material-symbols-outlined">smartphone</span>
            </div>
            <h3>BOOK AND RENT</h3>
            <p>
              Secure your ride with our fast and easy digital booking process.
            </p>
          </div>
          <div class="arrow">
            <span class="material-symbols-outlined">arrow_right_alt</span>
          </div>
          <div class="step">
            <div class="icon-box">
              <span class="material-symbols-outlined">map</span>
            </div>
            <h3>TRAVEL AND DRIVE</h3>
            <p>Experience the freedom of exploring Nepal with total comfort.</p>
          </div>
          <div class="arrow">
            <span class="material-symbols-outlined">arrow_right_alt</span>
          </div>
          <div class="step">
            <div class="icon-box">
              <span class="material-symbols-outlined">sync</span>
            </div>
            <h3>RETURN THE VEHICLE</h3>
            <p>Easy drop-off at your chosen location when your trip is done.</p>
          </div>
        </div>
      </section>

      <section class="urban-section container">
        <div class="urban-grid">
          <div class="urban-img-wrap">
            <img src="" alt="Green Super SUV" class="urban-img" />
          </div>
          <div class="urban-text">
            <h2>URBAN FREEDOM.</h2>
            <p>
              Navigate Nepal with precision. Our fleet features the latest SUVs
              for mountain roads and agile 2-wheelers for city traffic.
            </p>
            <div class="btn-group">
              <button
                class="btn-dark"
                onclick="window.location.href = 'bookings.php'"
              >
                BOOK NOW
              </button>
              <button
                class="btn-light"
                onclick="window.location.href = 'fleet.php'"
              >
                EXPLORE FLEET
              </button>
            </div>
          </div>
        </div>
      </section>
    </main>

<?php include 'view/layout/footer.php'; ?>

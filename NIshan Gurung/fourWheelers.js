// Sample vehicle data
const vehicles = [
    {
        name: "Toyota Corolla",
        seats: 5,
        pricePerDay: "$50",
        image: "toyota_corolla.jpg",
        available: true
    },
    {
        name: "Honda Civic",
        seats: 5,
        pricePerDay: "$55",
        image: "honda_civic.jpg",
        available: true
    },
    {
        name: "Ford Explorer",
        seats: 7,
        pricePerDay: "$80",
        image: "ford_explorer.jpg",
        available: false
    },
    {
        name: "Suzuki Vitara",
        seats: 5,
        pricePerDay: "$45",
        image: "suzuki_vitara.jpg",
        available: true
    }
];

// Reference to vehicle container
const vehicleList = document.getElementById("vehicleList");

// Function to display vehicles
function displayVehicles() {
    vehicles.forEach(vehicle => {
        const card = document.createElement("div");
        card.className = "vehicle-card";

        card.innerHTML = `
            <img src="${vehicle.image}" alt="${vehicle.name}">
            <h3>${vehicle.name}</h3>
            <p>Seats: ${vehicle.seats}</p>
            <p>Price: ${vehicle.pricePerDay}</p>
            <p>Status: ${vehicle.available ? "Available" : "Booked"}</p>
            <button ${vehicle.available ? "" : "disabled"}>Book Now</button>
        `;

        vehicleList.appendChild(card);
    });
}

// Initialize page
displayVehicles();
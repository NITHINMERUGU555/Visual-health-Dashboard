<?php include('includes/header.php'); ?>

<section class="hero-banner"></section>

<main>
  <h2>Immunization Data</h2>
  <p>This page provides insights into immunization coverage and vaccination efforts to improve public health.</p>

  <!-- Dropdown to select state -->
  <div style="text-align: center; margin: 20px 0;">
    <label for="stateSelect" style="font-weight: bold;">Select State:</label>
    <select id="stateSelect">
      <option value="AL">Alabama</option>
      <option value="AK">Alaska</option>
      <option value="AZ">Arizona</option>
      <option value="AR">Arkansas</option>
      <option value="CA">California</option>
      <option value="CO">Colorado</option>
      <option value="CT">Connecticut</option>
      <option value="DE">Delaware</option>
      <option value="FL" selected>Florida</option>
      <option value="GA">Georgia</option>
      <option value="HI">Hawaii</option>
      <option value="ID">Idaho</option>
      <option value="IL">Illinois</option>
      <option value="IN">Indiana</option>
      <option value="IA">Iowa</option>
      <option value="KS">Kansas</option>
      <option value="KY">Kentucky</option>
      <option value="LA">Louisiana</option>
      <option value="ME">Maine</option>
      <option value="MD">Maryland</option>
      <option value="MA">Massachusetts</option>
      <option value="MI">Michigan</option>
      <option value="MN">Minnesota</option>
      <option value="MS">Mississippi</option>
      <option value="MO">Missouri</option>
      <option value="MT">Montana</option>
      <option value="NE">Nebraska</option>
      <option value="NV">Nevada</option>
      <option value="NH">New Hampshire</option>
      <option value="NJ">New Jersey</option>
      <option value="NM">New Mexico</option>
      <option value="NY">New York</option>
      <option value="NC">North Carolina</option>
      <option value="ND">North Dakota</option>
      <option value="OH">Ohio</option>
      <option value="OK">Oklahoma</option>
      <option value="OR">Oregon</option>
      <option value="PA">Pennsylvania</option>
      <option value="RI">Rhode Island</option>
      <option value="SC">South Carolina</option>
      <option value="SD">South Dakota</option>
      <option value="TN">Tennessee</option>
      <option value="TX">Texas</option>
      <option value="UT">Utah</option>
      <option value="VT">Vermont</option>
      <option value="VA">Virginia</option>
      <option value="WA">Washington</option>
      <option value="WV">West Virginia</option>
      <option value="WI">Wisconsin</option>
      <option value="WY">Wyoming</option>
    </select>
  </div>

  <div class="infographic">
    <h3>Vaccination Coverage</h3>
    <canvas id="vaccineChart" width="400" height="200"></canvas>
    <p style="text-align: center; margin-top: 10px; font-size: 14px; color: #666;">
      <em>Note: Booster data may not be available for all states.</em>
    </p>
    <p id="lastUpdated" style="text-align:center; font-size: 13px; color: #555; margin-top: 6px;">
      Loading last updated date...
    </p>
  </div>
</main>

<?php include('includes/footer.php'); ?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chart;

function loadChart(stateCode) {
  fetch("https://data.cdc.gov/resource/8xkx-amqh.json?$limit=5000")
    .then(response => response.json())
    .then(data => {
      const stateData = data.filter(entry => entry.recip_state === stateCode);

      let dose1Sum = 0, fullSum = 0, boosterSum = 0;
      let dose1Count = 0, fullCount = 0, boosterCount = 0;

      stateData.forEach(entry => {
        const dose1 = parseFloat(entry.administered_dose1_pop_pct);
        const full = parseFloat(entry.series_complete_pop_pct);
        const booster = parseFloat(entry.bivalent_booster_pop_pct);

        if (!isNaN(dose1)) { dose1Sum += dose1; dose1Count++; }
        if (!isNaN(full)) { fullSum += full; fullCount++; }
        if (!isNaN(booster)) { boosterSum += booster; boosterCount++; }
      });

      const coverage = [
        dose1Count ? (dose1Sum / dose1Count).toFixed(1) : 0,
        fullCount ? (fullSum / fullCount).toFixed(1) : 0,
        boosterCount ? (boosterSum / boosterCount).toFixed(1) : 0
      ];

      const ctx = document.getElementById('vaccineChart').getContext('2d');
      if (chart) chart.destroy();

      chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ["1st Dose", "Fully Vax", "Booster"],
          datasets: [{
            label: `${stateCode} Vaccination Coverage (%)`,
            data: coverage,
            backgroundColor: [
              '#007acc',
              '#2ca02c',
              boosterCount > 0 ? '#ff7f0e' : '#ccc'
            ],
            borderRadius: 6
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: ctx => ctx.raw + '%'
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              ticks: {
                callback: v => v + '%'
              }
            }
          }
        }
      });

      const latestEntry = stateData[0];
      if (latestEntry?.date) {
        const formatted = new Date(latestEntry.date).toLocaleDateString('en-US', {
          year: 'numeric', month: 'long', day: 'numeric'
        });
        document.getElementById("lastUpdated").textContent = `Last updated: ${formatted}`;
      } else {
        document.getElementById("lastUpdated").textContent = "Last updated date not available";
      }
    })
    .catch(err => {
      console.error("Error loading chart data:", err);
      document.getElementById("lastUpdated").textContent = "Error loading update date";
    });
}

// Initial chart load for FL
loadChart("FL");

document.getElementById("stateSelect").addEventListener("change", function () {
  loadChart(this.value);
});
</script>

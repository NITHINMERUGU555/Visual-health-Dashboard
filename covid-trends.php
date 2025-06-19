<?php include('includes/header.php'); ?>
<h2>COVID-19 Case Trends</h2>
<p>This page displays monthly COVID-19 cases using live data. Select a state to view specific data.</p>

<label for="stateSelect">Select State: </label>
<select id="stateSelect">
  <option value="FL">Florida</option>
  <option value="TX">Texas</option>
  <option value="CA">California</option>
  <!-- Add more states here -->
</select>

<section class="infographic">
  <h3>Monthly Cases</h3>
  <canvas id="caseChart" width="600" height="300"></canvas>
  <p style="font-size: 12px; color: gray;">📊 Live data from CDC. May vary based on reporting frequency and availability.</p>
</section>

<?php include('includes/footer.php'); ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('caseChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [],
    datasets: [{
      label: 'Monthly New Cases',
      data: [],
      backgroundColor: '#007acc'
    }]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

// 📦 CDC Fetch Logic
function loadCasesForState(stateCode) {
  fetch(`https://data.cdc.gov/resource/9mfq-cb36.json?state=${stateCode}&$limit=100`)
    .then(res => res.json())
    .then(data => {
      const months = {};
      data.forEach(entry => {
        const month = entry.submission_date?.split("/")[0];
        const cases = parseInt(entry.new_case || "0", 10);
        if (month) months[month] = (months[month] || 0) + cases;
      });

      const ordered = Object.entries(months).sort((a, b) => +a[0] - +b[0]);
      const monthLabels = ordered.map(([m]) => `Month ${m}`);
      const monthData = ordered.map(([, v]) => v);

      chart.data.labels = monthLabels;
      chart.data.datasets[0].data = monthData;
      chart.update();
    })
    .catch(err => {
      console.error("CDC data load error:", err);
    });
}

// Load default
loadCasesForState('FL');

// Handle dropdown
document.getElementById('stateSelect').addEventListener('change', e => {
  loadCasesForState(e.target.value);
});
</script>

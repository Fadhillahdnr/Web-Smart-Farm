import './bootstrap';
import Chart from 'chart.js/auto';

let chart;
let lastDataId = null;

// ===============================
// INIT CHART
// ===============================
function initChart() {
    const canvas = document.getElementById('chart');

    if (!canvas) {
        console.log("❌ Canvas tidak ditemukan!");
        return;
    }

    const ctx = canvas.getContext('2d');

    // fallback kalau data kosong
    const labels = window.initialData?.labels || [];
    const soilData = window.initialData?.soil || [];
    const phData = window.initialData?.ph || [];

    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Kelembapan (%)',
                    data: soilData,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#22c55e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                },
                {
                    label: 'pH',
                    data: phData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 500,
                easing: 'easeInOutQuart'
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#1e293b',
                        font: {
                            size: 12,
                            weight: 'bold'
                        },
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#22c55e',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 10
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: { 
                        color: '#64748b',
                        font: { size: 10 }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    },
                    ticks: { 
                        color: '#64748b',
                        font: { size: 10 }
                    },
                    beginAtZero: true
                }
            }
        }
    });

    console.log("✅ Chart initialized");
}

// ===============================
// FETCH REALTIME DATA
// ===============================
function fetchData() {
    fetch('/api/sensor/latest')
        .then(res => res.json())
        .then(data => {
            if (!data || !data.id) return;
            
            // CEK APAKAH DATA BARU (berdasarkan ID)
            if (lastDataId !== null && lastDataId === data.id) {
                console.log("⏳ Data belum berubah...");
                return;
            }
            
            lastDataId = data.id;
            const time = new Date().toLocaleTimeString();

            // ===================
            // UPDATE CARD + FLASH EFFECT
            // ===================
            updateCard('soil', data.moisture + '%');
            updateCard('ph', data.ph);
            updateCard('color', data.color);
            updateCard('status', data.status);

            // ===================
            // UPDATE PROGRESS
            // ===================
            document.getElementById('soil-bar').style.width = data.moisture + '%';
            document.getElementById('ph-bar').style.width = (data.ph * 10) + '%';

            // ===================
            // UPDATE CHART
            // ===================
            if (chart) {
                chart.data.labels.push(time);
                chart.data.datasets[0].data.push(data.moisture);
                chart.data.datasets[1].data.push(data.ph);

                // batasi max 10 data
                if (chart.data.labels.length > 10) {
                    chart.data.labels.shift();
                    chart.data.datasets[0].data.shift();
                    chart.data.datasets[1].data.shift();
                }

                chart.update('none'); // smooth update tanpa lag
            }

            // ===================
            // UPDATE TABEL (TERBARU DI ATAS)
            // ===================
            const row = `
                <tr class="new-row">
                    <td>${time}</td>
                    <td>${data.moisture}%</td>
                    <td>${data.ph}</td>
                    <td>${data.status}</td>
                </tr>
            `;

            const history = document.getElementById('history');

            // masuk ke paling atas
            history.insertAdjacentHTML('afterbegin', row);

            // batasi max 10 baris (biar tidak berat)
            if (history.children.length > 10) {
                history.removeChild(history.lastElementChild);
            }

            // HAPUS CLASS NEW-ROW SETELAH ANIMASI
            setTimeout(() => {
                document.querySelectorAll('.new-row').forEach(row => {
                    row.classList.remove('new-row');
                });
            }, 1000);

            console.log("✅ Data baru diterima:", data);

        })
        .catch(err => {
            console.log("❌ Sensor offline:", err);
        });
}

// ===============================
// UPDATE CARD DENGAN FLASH EFFECT
// ===============================
function updateCard(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.innerText = value;
        el.parentElement.classList.add('flash');
        setTimeout(() => el.parentElement.classList.remove('flash'), 500);
    }
}

// ===============================
// RUN APP
// ===============================
document.addEventListener("DOMContentLoaded", () => {
    initChart();

    // ambil data tiap 3 detik
    setInterval(fetchData, 3000);

    console.log("🚀 Dashboard running...");
});
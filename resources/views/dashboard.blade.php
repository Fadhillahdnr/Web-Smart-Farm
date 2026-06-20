<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Farming Dashboard</title>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.pulse {
    animation: pulse 0.6s ease;
}

@keyframes pulse {

    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.08);
    }

    100% {
        transform: scale(1);
    }
}

</style>

</head>

<body class="bg-gradient-to-br from-gray-100 to-gray-300 min-h-screen">

<div class="max-w-7xl mx-auto p-6">

    <!-- ================= HEADER ================= -->

    <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow mb-6">

        <div class="text-2xl font-semibold text-green-700">
            🌱 Smart Farming Dashboard
        </div>

        <div class="flex items-center gap-4">

            <span class="text-sm text-gray-500">
                Realtime Monitoring
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full text-sm shadow transition">
                    🗝️ Logout
                </button>
            </form>

        </div>

    </div>

    <!-- ================= CARD ================= -->

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">

        <!-- SOIL -->

        <div class="card bg-gradient-to-r from-blue-500 to-blue-400 text-white p-4 rounded-xl shadow">

            <p>Kelembapan</p>

            <h2 id="soil" class="text-3xl font-bold">
                -
            </h2>

        </div>

        <!-- PH -->

        <div class="card bg-gradient-to-r from-purple-500 to-purple-400 text-white p-4 rounded-xl shadow">

            <p>pH Tanah</p>

            <h2 id="ph" class="text-3xl font-bold">
                -
            </h2>

        </div>

        <!-- COLOR -->

        <div class="card bg-gradient-to-r from-yellow-500 to-orange-400 text-white p-4 rounded-xl shadow">

            <p>Warna Tanah</p>

            <h2 id="color" class="text-xl font-bold">
                -
            </h2>

        </div>

        <!-- BATTERY -->

        <div id="batteryCard" class="card bg-gradient-to-r from-green-500 to-emerald-400 text-white p-4 rounded-xl shadow">

            <p>Baterai</p>

            <h2 id="battery" class="text-3xl font-bold">
                -
            </h2>

        </div>

        <!-- STATUS -->

        <div id="statusCard" class="card bg-gray-700 text-white p-4 rounded-xl shadow">

            <p>Status</p>

            <h2 id="status" class="text-2xl font-bold">
                -
            </h2>

        </div>

    </div>

    <!-- ================= CHART + TABLE ================= -->

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <!-- CHART -->

        <div class="md:col-span-2 bg-white p-4 rounded-xl shadow">

            <h2 class="font-semibold mb-2">
                📈 Grafik Realtime
            </h2>

            <canvas id="chart"></canvas>

        </div>

        <!-- TABLE -->

        <div class="bg-white p-5 rounded-xl shadow">

            <div class="flex items-center justify-between mb-4">

                <h2 class="font-semibold text-lg">
                    📋 Histori Data
                </h2>

                <a href="/export-excel"
                class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow transition text-sm">

                    📥 <span>Export</span>

                </a>

            </div>

            <div class="overflow-y-auto max-h-80 border rounded-lg">

                <table class="w-full text-sm text-center">

                    <thead class="sticky top-0 bg-gray-100 text-gray-700">

                        <tr>

                            <th class="py-2">Soil</th>
                            <th class="py-2">pH</th>
                            <th class="py-2">Warna</th>
                            <th class="py-2">Battery</th>
                            <th class="py-2">Status</th>

                        </tr>

                    </thead>

                    <tbody id="historyTable">

                        @foreach($all->sortByDesc('created_at') as $d)

                        <tr class="border-b hover:bg-gray-50 transition">

                            <!-- SOIL -->

                            <td class="py-2">
                                {{ $d->moisture }}%
                            </td>

                            <!-- PH -->

                            <td class="py-2">
                                {{ $d->ph }}
                            </td>

                            <!-- COLOR -->

                            <td class="py-2">

                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $d->color == 'HITAM' ? 'bg-gray-800 text-white' :
                                    ($d->color == 'COKLAT' ? 'bg-yellow-700 text-white' :
                                    ($d->color == 'MERAH' ? 'bg-red-500 text-white' :
                                    'bg-gray-300 text-black')) }}">

                                    {{ $d->color }}

                                </span>

                            </td>

                            <!-- BATTERY -->

                            <td class="py-2">

                                <span class="font-semibold
                                {{ $d->battery >= 70 ? 'text-green-600' :
                                ($d->battery >= 30 ? 'text-yellow-500' :
                                'text-red-600') }}">

                                    🔋 {{ $d->battery }}%

                                </span>

                            </td>

                            <!-- STATUS -->

                            <td class="py-2 font-semibold
                                {{ $d->status == 'SUBUR' ? 'text-green-600' :
                                ($d->status == 'CUKUP SUBUR' ? 'text-yellow-500' : 'text-red-600') }}">

                                {{ $d->status }}

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- ================= PROGRESS ================= -->

    <div class="bg-white p-4 rounded-xl shadow">

        <h2 class="font-semibold mb-3">
            Indikator Sensor
        </h2>

        <!-- SOIL -->

        <div class="mb-4">

            <div class="flex justify-between mb-1">

                <p>Kelembapan</p>

                <span id="soilText">0%</span>

            </div>

            <div class="w-full bg-gray-300 h-3 rounded">

                <div id="bar-soil"
                    class="bg-blue-500 h-3 rounded transition-all duration-500"
                    style="width:0%">

                </div>

            </div>

        </div>

        <!-- PH -->

        <div class="mb-4">

            <div class="flex justify-between mb-1">

                <p>pH</p>

                <span id="phText">0</span>

            </div>

            <div class="w-full bg-gray-300 h-3 rounded">

                <div id="bar-ph"
                    class="bg-purple-500 h-3 rounded transition-all duration-500"
                    style="width:0%">

                </div>

            </div>

        </div>

        <!-- BATTERY -->

        <div class="mb-2">

            <div class="flex justify-between mb-1">

                <p>Baterai</p>

                <span id="batteryText">0%</span>

            </div>

            <div class="w-full bg-gray-300 h-3 rounded">

                <div id="bar-battery"
                    class="bg-green-500 h-3 rounded transition-all duration-500"
                    style="width:0%">

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ================= SCRIPT ================= -->

<script>

let chart;

function initChart() {

    const ctx =
        document.getElementById('chart');

    chart = new Chart(ctx, {

        type: 'line',

        data: {

            labels: [],

            datasets: [

                {
                    label: 'Kelembapan',
                    data: [],
                    borderColor: 'blue',
                    backgroundColor: 'rgba(59,130,246,0.2)',
                    fill: true,
                    tension: 0.4
                },

                {
                    label: 'pH',
                    data: [],
                    borderColor: 'purple',
                    backgroundColor: 'rgba(168,85,247,0.2)',
                    fill: true,
                    borderDash: [5,5],
                    tension: 0.4
                }

            ]

        },

        options: {

            responsive: true

        }

    });
}

function updateDashboard() {

    fetch('/api/sensor/latest')

        .then(res => res.json())

        .then(data => {

            const time =
                new Date().toLocaleTimeString();

            // ================= CARD =================

            document.getElementById('soil').innerText =
                data.moisture + '%';

            document.getElementById('ph').innerText =
                data.ph;

            document.getElementById('color').innerText =
                data.color;

            document.getElementById('battery').innerText =
                data.battery + '%';

            // ================= STATUS =================

            let statusCard =
                document.getElementById('statusCard');

            let statusEl =
                document.getElementById('status');

            statusEl.innerText =
                data.status;

            statusCard.className =
                "card text-white p-4 rounded-xl shadow";

            if (data.status === "SUBUR")
                statusCard.classList.add("bg-green-500");

            else if (data.status === "CUKUP SUBUR")
                statusCard.classList.add("bg-yellow-500");

            else
                statusCard.classList.add("bg-red-500");

            // ================= BATTERY COLOR =================

            let batteryBar =
                document.getElementById('bar-battery');

            batteryBar.className =
                "h-3 rounded transition-all duration-500";

            if (data.battery >= 70)
                batteryBar.classList.add("bg-green-500");

            else if (data.battery >= 30)
                batteryBar.classList.add("bg-yellow-500");

            else
                batteryBar.classList.add("bg-red-500");

            // ================= PROGRESS =================

            document.getElementById('bar-soil').style.width =
                data.moisture + '%';

            document.getElementById('bar-ph').style.width =
                (data.ph / 14 * 100) + '%';

            document.getElementById('bar-battery').style.width =
                data.battery + '%';

            // ================= TEXT =================

            document.getElementById('soilText').innerText =
                data.moisture + '%';

            document.getElementById('phText').innerText =
                data.ph;

            document.getElementById('batteryText').innerText =
                data.battery + '%';

            // ================= CHART =================

            chart.data.labels.push(time);

            chart.data.datasets[0].data.push(data.moisture);

            chart.data.datasets[1].data.push(data.ph);

            if (chart.data.labels.length > 10) {

                chart.data.labels.shift();

                chart.data.datasets[0].data.shift();

                chart.data.datasets[1].data.shift();

            }

            chart.update('none');

        });
}

// ================= HISTORY =================

function updateHistory() {

    fetch('/api/sensor/history')

        .then(res => res.json())

        .then(data => {

            let table =
                document.getElementById('historyTable');

            table.innerHTML = "";

            data.forEach(d => {

                let colorClass = '';

                if (d.color === 'HITAM')
                    colorClass = 'bg-gray-800 text-white';

                else if (d.color === 'COKLAT')
                    colorClass = 'bg-yellow-700 text-white';

                else if (d.color === 'MERAH')
                    colorClass = 'bg-red-500 text-white';

                else
                    colorClass = 'bg-gray-300 text-black';

                let statusClass = '';

                if (d.status === 'SUBUR')
                    statusClass = 'text-green-600';

                else if (d.status === 'CUKUP SUBUR')
                    statusClass = 'text-yellow-500';

                else
                    statusClass = 'text-red-600';

                let batteryClass = '';

                if (d.battery >= 70)
                    batteryClass = 'text-green-600';

                else if (d.battery >= 30)
                    batteryClass = 'text-yellow-500';

                else
                    batteryClass = 'text-red-600';

                table.innerHTML += `

                    <tr class="border-b hover:bg-gray-50 transition">

                        <td class="py-2">
                            ${d.moisture}%
                        </td>

                        <td class="py-2">
                            ${d.ph}
                        </td>

                        <td class="py-2">

                            <span class="px-2 py-1 rounded-full text-xs font-semibold ${colorClass}">

                                ${d.color}

                            </span>

                        </td>

                        <td class="py-2 font-semibold ${batteryClass}">

                            🔋 ${d.battery}%

                        </td>

                        <td class="py-2 font-semibold ${statusClass}">

                            ${d.status}

                        </td>

                    </tr>

                `;
            });

        });
}

initChart();

// ================= REALTIME =================

setInterval(() => {

    updateDashboard();

    updateHistory();

}, 3000);

updateDashboard();

updateHistory();

</script>

</body>
</html>
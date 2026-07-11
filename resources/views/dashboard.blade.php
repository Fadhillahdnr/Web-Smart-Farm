<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Soil Classifier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card { transition: transform .25s ease, box-shadow .25s ease; }
        .card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px rgb(15 23 42 / .12); }
        .pulse { animation: pulse .45s ease; }
        @keyframes pulse { 50% { transform: scale(1.04); } }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 to-emerald-50 text-slate-800">
<main class="mx-auto max-w-7xl p-4 md:p-6">
    <header class="mb-6 flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-emerald-700">🌱 Soil Classifier</h1>
            <p class="text-sm text-slate-500">Monitoring kesuburan tanah secara realtime</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="hidden text-sm text-slate-500 sm:inline">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-full bg-red-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-600">Logout</button>
            </form>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="mb-6 rounded-2xl bg-white p-5 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <label for="soilSelector" class="mb-2 block text-sm font-semibold text-slate-700">Tanah yang dipantau</label>
                <select id="soilSelector" class="w-full rounded-xl border-slate-300 px-4 py-3 focus:border-emerald-500 focus:ring-emerald-500" {{ $soilPlots->isEmpty() ? 'disabled' : '' }}>
                    @forelse($soilPlots as $soil)
                        <option value="{{ route('dashboard', ['soil' => $soil->id]) }}" @selected($selectedSoil?->is($soil))>{{ $soil->name }}</option>
                    @empty
                        <option>Belum ada tanah</option>
                    @endforelse
                </select>
            </div>
            <form method="POST" action="{{ route('soil-plots.store') }}" class="flex flex-col gap-2 sm:flex-row">
                @csrf
                <input name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Tuliskan nama tanah, mis. Tanah A" class="min-w-72 flex-1 rounded-xl border-slate-300 px-4 py-3 focus:border-emerald-500 focus:ring-emerald-500">
                <button class="rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white transition hover:bg-emerald-700">+ Tambah Tanah</button>
            </form>
        </div>

        @if($selectedSoil)
            <div class="mt-5 grid gap-4 border-t pt-5 lg:grid-cols-[1fr_auto] lg:items-end">
                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Token perangkat untuk {{ $selectedSoil->name }}</p>
                    <div class="flex gap-2">
                        <input id="sensorToken" readonly value="{{ $selectedSoil->getRawOriginal('sensor_token') }}" class="min-w-0 flex-1 rounded-xl border-slate-200 bg-slate-50 px-3 py-2 font-mono text-xs">
                        <button type="button" id="copyToken" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">Salin</button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Kirim token ini melalui header <code>X-Soil-Token</code> pada request perangkat. Jangan bagikan ke pihak lain.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="toggleRename" class="rounded-xl border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Ubah nama</button>
                    <form method="POST" action="{{ route('soil-plots.token', $selectedSoil) }}" onsubmit="return confirm('Token lama akan langsung berhenti bekerja. Lanjutkan?')">
                        @csrf @method('PATCH')
                        <button class="rounded-xl border border-amber-300 px-4 py-2 text-sm text-amber-700 hover:bg-amber-50">Buat ulang token</button>
                    </form>
                    <form method="POST" action="{{ route('soil-plots.destroy', $selectedSoil) }}" onsubmit="return confirm('Hapus {{ addslashes($selectedSoil->name) }} beserta SELURUH histori sensor? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf @method('DELETE')
                        <button class="rounded-xl border border-red-300 px-4 py-2 text-sm text-red-600 hover:bg-red-50">Hapus tanah</button>
                    </form>
                </div>
            </div>
            <form id="renameForm" method="POST" action="{{ route('soil-plots.update', $selectedSoil) }}" class="mt-4 hidden gap-2 sm:flex-row">
                @csrf @method('PATCH')
                <input name="name" required maxlength="100" value="{{ $selectedSoil->name }}" class="flex-1 rounded-xl border-slate-300 px-4 py-2">
                <button class="rounded-xl bg-slate-800 px-4 py-2 text-white">Simpan nama</button>
            </form>
        @endif
    </section>

    @if(!$selectedSoil)
        <section class="rounded-2xl border-2 border-dashed border-emerald-200 bg-white p-12 text-center shadow-sm">
            <div class="mb-3 text-5xl">🌾</div>
            <h2 class="text-xl font-bold">Tambahkan tanah pertama Anda</h2>
            <p class="mt-2 text-slate-500">Setelah tanah dibuat, token perangkat dan histori sensornya akan tampil di sini.</p>
        </section>
    @else
        <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <article class="card rounded-2xl bg-gradient-to-r from-blue-600 to-blue-400 p-5 text-white shadow-sm"><p class="text-sm opacity-90">Kelembapan</p><h2 id="soil" class="mt-1 text-3xl font-bold">-</h2></article>
            <article class="card rounded-2xl bg-gradient-to-r from-purple-600 to-purple-400 p-5 text-white shadow-sm"><p class="text-sm opacity-90">pH Tanah</p><h2 id="ph" class="mt-1 text-3xl font-bold">-</h2></article>
            <article class="card rounded-2xl bg-gradient-to-r from-amber-500 to-orange-400 p-5 text-white shadow-sm"><p class="text-sm opacity-90">Warna Tanah</p><h2 id="color" class="mt-1 text-2xl font-bold">-</h2></article>
            <article class="card rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-400 p-5 text-white shadow-sm"><p class="text-sm opacity-90">Baterai</p><h2 id="battery" class="mt-1 text-3xl font-bold">-</h2></article>
            <article id="statusCard" class="card rounded-2xl bg-slate-700 p-5 text-white shadow-sm"><p class="text-sm opacity-90">Status</p><h2 id="status" class="mt-1 text-2xl font-bold">-</h2></article>
        </section>

        <section class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <article class="rounded-2xl bg-white p-5 shadow-sm lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <div><h2 class="font-bold">📈 Grafik {{ $selectedSoil->name }}</h2><p id="lastUpdated" class="text-xs text-slate-500">Menunggu data sensor...</p></div>
                    <span id="connectionBadge" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Menghubungkan</span>
                </div>
                <div class="h-80"><canvas id="chart"></canvas></div>
            </article>

            <article class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div><h2 class="font-bold">📋 Histori Data</h2><p class="text-xs text-slate-500">{{ $selectedSoil->name }}</p></div>
                    <a href="{{ route('soil-plots.export', $selectedSoil) }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Export</a>
                </div>
                <div class="max-h-80 overflow-auto rounded-xl border">
                    <table class="w-full min-w-[520px] text-center text-sm">
                        <thead class="sticky top-0 bg-slate-100 text-xs uppercase text-slate-600"><tr><th class="p-3">Waktu</th><th>Soil</th><th>pH</th><th>Warna</th><th>Baterai</th><th>Status</th></tr></thead>
                        <tbody id="historyTable" class="divide-y"></tbody>
                    </table>
                    <p id="emptyHistory" class="hidden p-8 text-center text-sm text-slate-500">Belum ada data untuk tanah ini.</p>
                </div>
            </article>
        </section>

        <section class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="mb-4 font-bold">Indikator Sensor</h2>
            <div class="space-y-4">
                @foreach([['Kelembapan','soilText','bar-soil','bg-blue-500'], ['pH','phText','bar-ph','bg-purple-500'], ['Baterai','batteryText','bar-battery','bg-emerald-500']] as [$label,$textId,$barId,$color])
                    <div><div class="mb-1 flex justify-between text-sm"><span>{{ $label }}</span><span id="{{ $textId }}">0</span></div><div class="h-3 overflow-hidden rounded-full bg-slate-200"><div id="{{ $barId }}" class="h-full rounded-full {{ $color }} transition-all duration-500" style="width:0%"></div></div></div>
                @endforeach
            </div>
        </section>
    @endif
</main>

@if($selectedSoil)
<script>
const latestUrl = @json(route('dashboard.latest', $selectedSoil));
const historyUrl = @json(route('dashboard.history', $selectedSoil));
const initialHistory = @json($history);
let lastDataId = null;

const chronological = [...initialHistory].reverse();
const chart = new Chart(document.getElementById('chart'), {
    type: 'line',
    data: {
        labels: chronological.map(item => formatTime(item.created_at)),
        datasets: [
            { label: 'Kelembapan (%)', data: chronological.map(item => item.moisture), borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.10)', fill: true, tension: .35 },
            { label: 'pH', data: chronological.map(item => item.ph), borderColor: '#9333ea', backgroundColor: 'rgba(147,51,234,.08)', fill: true, tension: .35, borderDash: [5, 5] }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false }, scales: { y: { beginAtZero: true } } }
});

function formatTime(value) {
    if (!value) return '-';
    return new Intl.DateTimeFormat('id-ID', { dateStyle: 'short', timeStyle: 'medium' }).format(new Date(value));
}

function setText(id, value) { document.getElementById(id).textContent = value; }

function updateCards(data) {
    setText('soil', `${data.moisture}%`); setText('ph', data.ph); setText('color', data.color);
    setText('battery', `${data.battery}%`); setText('status', data.status);
    setText('soilText', `${data.moisture}%`); setText('phText', data.ph); setText('batteryText', `${data.battery}%`);
    document.getElementById('bar-soil').style.width = `${Math.min(100, data.moisture)}%`;
    document.getElementById('bar-ph').style.width = `${Math.min(100, data.ph / 14 * 100)}%`;
    document.getElementById('bar-battery').style.width = `${Math.min(100, data.battery)}%`;
    const statusCard = document.getElementById('statusCard');
    statusCard.className = 'card rounded-2xl p-5 text-white shadow-sm ' + (data.status === 'SUBUR' ? 'bg-emerald-500' : data.status === 'CUKUP SUBUR' ? 'bg-amber-500' : 'bg-red-500');
    statusCard.classList.add('pulse'); setTimeout(() => statusCard.classList.remove('pulse'), 450);
    setText('lastUpdated', `Data terakhir: ${formatTime(data.created_at)}`);
}

function addCell(row, text, className = '') { const cell = row.insertCell(); cell.textContent = text; cell.className = `p-3 ${className}`; return cell; }

function renderHistory(items) {
    const table = document.getElementById('historyTable'); table.replaceChildren();
    document.getElementById('emptyHistory').classList.toggle('hidden', items.length > 0);
    items.forEach(item => {
        const row = table.insertRow(); row.className = 'hover:bg-slate-50';
        addCell(row, formatTime(item.created_at), 'whitespace-nowrap text-xs text-slate-500');
        addCell(row, `${item.moisture}%`); addCell(row, item.ph); addCell(row, item.color);
        addCell(row, `🔋 ${item.battery}%`, item.battery >= 70 ? 'text-emerald-600' : item.battery >= 30 ? 'text-amber-600' : 'text-red-600');
        addCell(row, item.status, `font-semibold ${item.status === 'SUBUR' ? 'text-emerald-600' : item.status === 'CUKUP SUBUR' ? 'text-amber-600' : 'text-red-600'}`);
    });
}

async function refresh() {
    const badge = document.getElementById('connectionBadge');
    try {
        const [latestResponse, historyResponse] = await Promise.all([fetch(latestUrl), fetch(historyUrl)]);
        if (!latestResponse.ok || !historyResponse.ok) throw new Error('Request gagal');
        const [latest, history] = await Promise.all([latestResponse.json(), historyResponse.json()]);
        renderHistory(history);
        if (latest.id) {
            updateCards(latest);
            if (lastDataId !== latest.id) {
                lastDataId = latest.id;
                const ordered = [...history].reverse();
                chart.data.labels = ordered.map(item => formatTime(item.created_at));
                chart.data.datasets[0].data = ordered.map(item => item.moisture);
                chart.data.datasets[1].data = ordered.map(item => item.ph);
                chart.update('none');
            }
        }
        badge.textContent = 'Online'; badge.className = 'rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700';
    } catch (error) {
        badge.textContent = 'Terputus'; badge.className = 'rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700';
    }
}

document.getElementById('soilSelector').addEventListener('change', event => window.location.href = event.target.value);
document.getElementById('toggleRename').addEventListener('click', () => document.getElementById('renameForm').classList.toggle('hidden'));
document.getElementById('copyToken').addEventListener('click', async event => {
    await navigator.clipboard.writeText(document.getElementById('sensorToken').value);
    event.target.textContent = 'Tersalin'; setTimeout(() => event.target.textContent = 'Salin', 1200);
});
renderHistory(initialHistory);
if (initialHistory[0]) { lastDataId = initialHistory[0].id; updateCards(initialHistory[0]); }
setInterval(refresh, 3000);
</script>
@else
<script>document.getElementById('soilSelector')?.addEventListener('change', event => window.location.href = event.target.value);</script>
@endif
</body>
</html>

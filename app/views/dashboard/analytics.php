<div class="row g-4">
    <div class="col-12">
        <div class="card card-lg shadow-sm border-0 rounded-4">
            <div class="card-header bg-body border-bottom py-3">
                <h5 class="card-title fw-bold mb-0">Dashboard Analitik & Tren Kinerja Fakultas</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 border">
                            <h6 class="fw-bold mb-3">Distribusi Mahasiswa per Program Studi</h6>
                            <div id="studentPieChart" style="min-height: 280px;"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-4 border">
                            <h6 class="fw-bold mb-3">Distribusi Jabatan Fungsional Dosen</h6>
                            <div id="lecturerRankChart" style="min-height: 280px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseUrl = document.body.getAttribute('data-base-url') || '';
    fetch(`${baseUrl}/dashboard/chart-data`)
        .then(res => res.json())
        .then(data => {
            // Student Pie
            new ApexCharts(document.querySelector("#studentPieChart"), {
                series: data.studentDist.map(s => parseInt(s.count)),
                labels: data.studentDist.map(s => s.name),
                chart: { type: 'donut', height: 280 },
                colors: ['#3b82f6', '#10b981', '#f59e0b'],
                legend: { position: 'bottom' }
            }).render();

            // Lecturer Rank Bar
            new ApexCharts(document.querySelector("#lecturerRankChart"), {
                series: [{ name: 'Jumlah Dosen', data: data.rankDist.map(r => parseInt(r.count)) }],
                chart: { type: 'bar', height: 280 },
                xaxis: { categories: data.rankDist.map(r => r.academic_rank) },
                colors: ['#8b5cf6'],
                plotOptions: { bar: { borderRadius: 4, horizontal: true } }
            }).render();
        });
});
</script>

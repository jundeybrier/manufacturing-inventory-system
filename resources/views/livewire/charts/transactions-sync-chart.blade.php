<div class="p-4 rounded-xl shadow">
    <h3 class="text-lg font-bold mb-3">Last 30 Days — Transactions vs Synced</h3>
    <div id="transactionsChart"></div>

    <script>
        document.addEventListener('livewire:navigated', () => {
            const options = {
                chart: { type: 'bar', height: 350 },
                series: [
                    {
                        name: 'Total Transactions',
                        data: @json($totals)
                    },
                    {
                        name: 'Synced Transactions',
                        data: @json($synced)
                    }
                ],
                xaxis: {
                    categories: @json($labels),
                },
                plotOptions: {
                    bar: { columnWidth: '45%' }
                },
                colors: ['#2563eb', '#ff6800'],
                dataLabels: { enabled: false },
            };
            const chart = new ApexCharts(document.querySelector("#transactionsChart"), options);
            chart.render();
        });
    </script>
</div>

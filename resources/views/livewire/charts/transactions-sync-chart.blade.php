<div class="p-4 rounded-xl" wire:ignore>
    <h3 class="text-lg font-bold mb-3 text-neutral-800 dark:text-neutral-200">
        Last 30 Days — Transactions vs Synced
    </h3>

    <div id="transactionsChart"></div>

    <script>
        let transactionsChart = null;

        function renderTransactionsChart() {
            // Destroy previous chart instance
            if (transactionsChart) {
                transactionsChart.destroy();
            }

            const isDark = document.documentElement.classList.contains('dark');

            const options = {
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: false },
                },
                series: [
                    {
                        name: 'Total Transactions',
                        data: @json($totals),
                    },
                    {
                        name: 'Synced Transactions',
                        data: @json($synced),
                    }
                ],
                xaxis: {
                    categories: @json($labels),
                    labels: {
                        style: { colors: isDark ? '#fff' : '#000' }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: isDark ? '#fff' : '#000' }
                    }
                },
                legend: {
                    labels: {
                        colors: isDark ? '#fff' : '#000'
                    },
                    position: 'top'
                },
                plotOptions: {
                    bar: {
                        columnWidth: '45%',
                        borderRadius: 4
                    }
                },
                dataLabels: { enabled: false },
                colors: isDark ? ['#60a5fa', '#f59e0b'] : ['#2563eb', '#ff6800'],
                tooltip: { theme: isDark ? 'dark' : 'light' },
            };

            const chartContainer = document.querySelector("#transactionsChart");
            if (chartContainer) {
                transactionsChart = new ApexCharts(chartContainer, options);
                transactionsChart.render();
            }
        }

        // Render on first load
        document.addEventListener('DOMContentLoaded', renderTransactionsChart);

        // Re-render when Livewire navigates (page visit without full refresh)
        document.addEventListener('livewire:navigated', renderTransactionsChart);

        // Optional: re-render when theme toggles
        window.addEventListener('theme-changed', renderTransactionsChart);
    </script>
</div>

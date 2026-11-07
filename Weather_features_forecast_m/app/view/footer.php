                <footer class="dashboard-footer">
                    <span>Weather Forecast</span>
                    <span>Vị trí là khóa tra cứu dữ liệu dự báo. Các chỉ số hiện là dữ liệu mẫu.</span>
                </footer>
            </div>
        </main>
    </div>
    <script>
        const locations = {
            hanoi: {
                name: 'Hà Nội, Việt Nam',
                temperature: 25,
                humidity: 42,
                temperatures: [25, 26, 27, 26, 25, 24, 21, 22, 24, 26, 27, 27, 29, 25, 24, 25],
                humidities: [42, 40, 39, 42, 45, 47, 53, 47, 43, 48, 52, 52, 52, 47, 44, 41]
            },
            danang: {
                name: 'Đà Nẵng, Việt Nam',
                temperature: 29,
                humidity: 68,
                temperatures: [25, 25, 26, 27, 28, 29, 30, 30, 29, 28, 29, 30, 31, 30, 29, 29],
                humidities: [70, 72, 73, 71, 69, 68, 66, 65, 67, 69, 70, 68, 66, 67, 68, 68]
            },
            hcm: {
                name: 'TP. Hồ Chí Minh, Việt Nam',
                temperature: 31,
                humidity: 76,
                temperatures: [29, 29, 28, 29, 30, 31, 32, 32, 31, 30, 31, 32, 33, 32, 31, 31],
                humidities: [78, 79, 80, 78, 77, 76, 74, 73, 75, 77, 78, 76, 74, 75, 76, 76]
            }
        };

        const locationSelect = document.getElementById('location-select');
        const locationButtons = document.querySelectorAll('.location-link');
        const temperatureLine = document.getElementById('temperature-line');
        const humidityLine = document.getElementById('humidity-line');
        const temperatureLabels = document.querySelectorAll('.chart-y-labels text');
        const humidityLabels = document.querySelectorAll('.chart-right-labels text');
        const chart = { left: 62, right: 770, top: 30, bottom: 270 };

        function getChartRange(values) {
            const minimum = Math.floor(Math.min(...values) / 5) * 5 - 5;
            const maximum = Math.ceil(Math.max(...values) / 5) * 5 + 5;
            return { minimum, maximum };
        }

        function createSmoothPath(values, minimum, maximum) {
            const step = (chart.right - chart.left) / (values.length - 1);
            const points = values.map((value, index) => ({
                x: chart.left + index * step,
                y: chart.bottom - ((value - minimum) / (maximum - minimum)) * (chart.bottom - chart.top)
            }));
            let path = `M ${points[0].x} ${points[0].y}`;

            for (let index = 0; index < points.length - 1; index += 1) {
                const current = points[index];
                const next = points[index + 1];
                const midpoint = (current.x + next.x) / 2;
                path += ` C ${midpoint} ${current.y}, ${midpoint} ${next.y}, ${next.x} ${next.y}`;
            }

            return path;
        }

        function showLocation(locationKey) {
            const location = locations[locationKey];
            if (!location) {
                return;
            }

            locationSelect.value = locationKey;
            document.querySelector('.temperature-value').innerHTML = `${location.temperature} <span>°C</span>`;
            document.querySelector('.humidity-value').innerHTML = `${location.humidity} <span>%</span>`;
            document.querySelector('.chart-location').textContent = `${location.name} · 24 giờ qua`;
            document.querySelector('.latest-temperature').textContent = `${location.temperature}°C`;
            document.querySelector('.latest-humidity').textContent = `${location.humidity}%`;
            const temperatureRange = getChartRange(location.temperatures);
            const humidityRange = getChartRange(location.humidities);
            temperatureLine.setAttribute('d', createSmoothPath(location.temperatures, temperatureRange.minimum, temperatureRange.maximum));
            humidityLine.setAttribute('d', createSmoothPath(location.humidities, humidityRange.minimum, humidityRange.maximum));

            temperatureLabels.forEach((label, index) => {
                const value = temperatureRange.maximum - ((temperatureRange.maximum - temperatureRange.minimum) * index / (temperatureLabels.length - 1));
                label.textContent = `${Math.round(value)}°C`;
            });
            humidityLabels.forEach((label, index) => {
                const value = humidityRange.maximum - ((humidityRange.maximum - humidityRange.minimum) * index / (humidityLabels.length - 1));
                label.textContent = `${Math.round(value)}%`;
            });

            locationButtons.forEach((button) => {
                const isSelected = button.dataset.location === locationKey;
                button.classList.toggle('selected', isSelected);
                button.setAttribute('aria-pressed', String(isSelected));
            });
        }

        locationSelect.addEventListener('change', () => showLocation(locationSelect.value));
        locationButtons.forEach((button) => {
            button.addEventListener('click', () => showLocation(button.dataset.location));
        });

        const menuButton = document.getElementById('menu-button');
        const sidebar = document.getElementById('sidebar');
        menuButton.addEventListener('click', () => {
            const isOpen = sidebar.classList.toggle('sidebar-open');
            menuButton.setAttribute('aria-expanded', String(isOpen));
        });

        showLocation('hanoi');
    </script>
</body>
</html>